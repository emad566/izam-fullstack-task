<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class OrderTest extends TestCase
{
    protected $order_assertion_array = [
        'id',
        'order_number',
        'user',
        'total_amount',
        'status',
        'notes',
        'order_date',
        'products',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function test_user_can_list_their_orders()
    {
        $user = User::factory()->create();

        // Create orders for this user
        Order::factory()->count(2)->create(['user_id' => $user->id]);

        // Create orders for other users (should not appear in results)
        Order::factory()->count(3)->create();

        $response = $this->withAuth($user)
            ->getJson(route('user.orders.index'));

        $assertion_array = $this->list_format;
        $assertion_array['data']['items']['data']['*'] = $this->order_assertion_array;

        $response->assertStatus(200)
            ->assertJsonStructure($assertion_array);

        // Verify that only the user's orders are returned
        $responseData = $response->json();
        $this->assertCount(2, $responseData['data']['items']['data']);
    }

    public function test_admin_can_list_all_orders()
    {
        Order::factory()->count(5)->create();

        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index'));

        $assertion_array = $this->list_format;
        $assertion_array['data']['items']['data']['*'] = $this->order_assertion_array;

        $response->assertStatus(200)
            ->assertJsonStructure($assertion_array);
    }

    public function test_user_can_create_order_with_single_product()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 50.00,
            'stock' => 10
        ]);

        $orderData = [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ]
            ],
            'notes' => 'Test order'
        ];

        $response = $this->withAuth($user)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'item' => $this->order_assertion_array
                ]
            ]);

        // Verify order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => 100.00, // 2 * 50.00
            'notes' => 'Test order'
        ]);

        // Verify order product was created
        $this->assertDatabaseHas('order_products', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00
        ]);

        // Verify stock was reduced
        $product->refresh();
        $this->assertEquals(8, $product->stock);
    }

    public function test_user_can_create_order_with_multiple_products()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 25.00,
            'stock' => 15
        ]);

        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 75.00,
            'stock' => 8
        ]);

        $orderData = [
            'products' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 3
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 2
                ]
            ],
            'notes' => 'Multi-product order'
        ];

        $response = $this->withAuth($user)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(201);

        // Verify order total: (3 * 25.00) + (2 * 75.00) = 225.00
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => 225.00
        ]);

        // Verify both order products were created
        $this->assertDatabaseHas('order_products', [
            'product_id' => $product1->id,
            'quantity' => 3,
            'unit_price' => 25.00
        ]);

        $this->assertDatabaseHas('order_products', [
            'product_id' => $product2->id,
            'quantity' => 2,
            'unit_price' => 75.00
        ]);

        // Verify stock was reduced for both products
        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(12, $product1->stock); // 15 - 3
        $this->assertEquals(6, $product2->stock);  // 8 - 2
    }

    public function test_user_cannot_create_order_with_insufficient_stock()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 50.00,
            'stock' => 5
        ]);

        $orderData = [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10 // More than available stock
                ]
            ]
        ];

        $response = $this->withAuth($user)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.quantity']);

        // Verify no order was created
        $this->assertDatabaseMissing('orders', [
            'user_id' => $user->id
        ]);

        // Verify stock wasn't changed
        $product->refresh();
        $this->assertEquals(5, $product->stock);
    }

    public function test_user_can_show_their_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->withAuth($user)
            ->getJson(route('user.orders.show', $order->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'item' => $this->order_assertion_array
                ]
            ]);
    }

    public function test_user_cannot_show_other_users_order()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user2->id]);

        $response = $this->withAuth($user1)
            ->getJson(route('user.orders.show', $order->id));

        $response->assertStatus(403); // Forbidden
    }

    public function test_admin_can_show_any_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.show', $order->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'item' => $this->order_assertion_array
                ]
            ]);
    }

    public function test_admin_can_update_order_status_and_notes()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'status' => 'cancelled',
            'notes' => 'Customer requested cancellation'
        ];

        $response = $this->withAuth($this->admin)
            ->putJson(route('admin.orders.update', $order->id), $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
            'notes' => 'Customer requested cancellation'
        ]);
    }

    public function test_admin_can_access_order_edit_route()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.edit', $order->id));

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_order_update_routes()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'status' => 'cancelled'
        ];

        // Test that the user.orders.update route doesn't exist
        try {
            $url = route('user.orders.update', $order->id);
            $this->fail('user.orders.update route should not exist');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Route [user.orders.update] not defined', $e->getMessage());
        }

        // Test that the user.orders.edit route doesn't exist
        try {
            $url = route('user.orders.edit', $order->id);
            $this->fail('user.orders.edit route should not exist');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Route [user.orders.edit] not defined', $e->getMessage());
        }
    }

    public function test_user_can_delete_their_order()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 10
        ]);

        $order = Order::factory()->create(['user_id' => $user->id]);

        // Create order product to test stock restoration
        DB::table('order_products')->insert([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => $product->price,
            'total_price' => 3 * $product->price,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Reduce stock to simulate the order
        $product->decrement('stock', 3);
        $this->assertEquals(7, $product->refresh()->stock);

        $response = $this->withAuth($user)
            ->deleteJson(route('user.orders.destroy', $order->id));

        $response->assertStatus(200);

        // Verify stock was restored
        $this->assertEquals(10, $product->refresh()->stock);
    }

    public function test_order_validation_requires_products()
    {
        $user = User::factory()->create();

        $response = $this->withAuth($user)
            ->postJson(route('user.orders.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products']);
    }

    public function test_order_validation_requires_valid_product_data()
    {
        $user = User::factory()->create();

        $orderData = [
            'products' => [
                [
                    'product_id' => 99999, // Non-existent product
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->withAuth($user)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.product_id']);
    }

    public function test_order_validation_requires_positive_quantity()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $orderData = [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0 // Invalid quantity
                ]
            ]
        ];

        $response = $this->withAuth($user)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.quantity']);
    }

    public function test_admin_can_filter_orders_by_status()
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'delivered']);
        Order::factory()->create(['status' => 'cancelled']);

        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['status' => 'pending']));

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertNotEmpty($responseData['data']);
    }

    public function test_admin_can_filter_orders_by_order_number()
    {
        $timestamp = time();
        $order = Order::factory()->create(['order_number' => "ORD-TESTFILTER{$timestamp}"]);
        Order::factory()->create(['order_number' => "ORD-OTHERFILTER{$timestamp}"]);

        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['order_number' => "TESTFILTER{$timestamp}"]));

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertNotEmpty($responseData['data']);
    }

    public function test_order_status_defaults_to_pending_on_creation()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 25.00,
            'stock' => 10
        ]);

        $orderData = [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ]
            ],
            'notes' => 'Test order for status verification'
        ];

        $response = $this->withAuth($user)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(201);

        // Verify the order was created with pending status
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending', // Should default to pending
            'total_amount' => 50.00
        ]);

        // Get the created order and verify status enum
        $order = Order::where('user_id', $user->id)->latest()->first();
        $this->assertEquals(\App\OrderStatus::PENDING, $order->status);
    }

    public function test_guest_cannot_access_orders()
    {
        $response = $this->getJson(route('user.orders.index'));
        $response->assertStatus(400) // The middleware returns 400 with specific message
                 ->assertJson([
                     'status' => false,
                     'message' => 'You are not user role'
                 ]);
    }
}
