<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\OrderProduct;

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

    public function test_order_cannot_be_created_with_empty_products_array()
    {
        $user = User::factory()->create();

        // Test with empty products array
        $orderData = [
            'products' => [], // Empty array should fail validation
            'notes' => 'This should fail'
        ];

        $response = $this->withAuth($user)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products']);

        // Verify no order was created
        $this->assertDatabaseMissing('orders', [
            'user_id' => $user->id
        ]);
    }

    public function test_order_cannot_be_created_with_null_products()
    {
        $user = User::factory()->create();

        // Test with null products
        $orderData = [
            'products' => null, // Null should fail validation
            'notes' => 'This should also fail'
        ];

        $response = $this->withAuth($user)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products']);

        // Verify no order was created
        $this->assertDatabaseMissing('orders', [
            'user_id' => $user->id
        ]);
    }

    public function test_order_cannot_be_created_with_string_instead_of_array()
    {
        $user = User::factory()->create();

        // Test with string instead of array
        $orderData = [
            'products' => 'invalid string', // String should fail validation
            'notes' => 'This should fail too'
        ];

        $response = $this->withAuth($user)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products']);

        // Verify no order was created
        $this->assertDatabaseMissing('orders', [
            'user_id' => $user->id
        ]);
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

    public function test_admin_can_filter_orders_by_category_names()
    {
        // Create categories with unique names
        $timestamp = time();
        $electronicsCategory = Category::factory()->create(['name' => "Electronics{$timestamp}"]);
        $clothingCategory = Category::factory()->create(['name' => "Clothing{$timestamp}"]);
        $booksCategory = Category::factory()->create(['name' => "Books{$timestamp}"]);

        // Create products in different categories
        $laptop = Product::factory()->create([
            'name' => "Gaming Laptop {$timestamp}",
            'category_id' => $electronicsCategory->id,
            'stock' => 10
        ]);
        $shirt = Product::factory()->create([
            'name' => "Cotton Shirt {$timestamp}",
            'category_id' => $clothingCategory->id,
            'stock' => 20
        ]);
        $book = Product::factory()->create([
            'name' => "Programming Book {$timestamp}",
            'category_id' => $booksCategory->id,
            'stock' => 15
        ]);

        // Create users and orders
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Create orders with different products
        $order1 = Order::factory()->create(['user_id' => $user1->id]);
        OrderProduct::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $laptop->id,
            'quantity' => 1
        ]);

        $order2 = Order::factory()->create(['user_id' => $user2->id]);
        OrderProduct::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $shirt->id,
            'quantity' => 2
        ]);

        $order3 = Order::factory()->create(['user_id' => $user3->id]);
        OrderProduct::factory()->create([
            'order_id' => $order3->id,
            'product_id' => $book->id,
            'quantity' => 1
        ]);

        // Test filtering by single category
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['category_names' => ["Electronics{$timestamp}"]]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertNotContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds);

        // Test filtering by multiple categories
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['category_names' => ["Electronics{$timestamp}", "Clothing{$timestamp}"]]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds);
    }

    public function test_admin_can_filter_orders_by_product_names()
    {
        // Create unique names with timestamp
        $timestamp = time();
        $category = Category::factory()->create(['name' => "Electronics{$timestamp}"]);
        $laptop = Product::factory()->create([
            'name' => "Gaming Laptop {$timestamp}",
            'category_id' => $category->id,
            'stock' => 10
        ]);
        $phone = Product::factory()->create([
            'name' => "Smartphone {$timestamp}",
            'category_id' => $category->id,
            'stock' => 15
        ]);
        $tablet = Product::factory()->create([
            'name' => "Tablet Pro {$timestamp}",
            'category_id' => $category->id,
            'stock' => 8
        ]);

        // Create users and orders
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Create orders with different products
        $order1 = Order::factory()->create(['user_id' => $user1->id]);
        OrderProduct::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $laptop->id,
            'quantity' => 1
        ]);

        $order2 = Order::factory()->create(['user_id' => $user2->id]);
        OrderProduct::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $phone->id,
            'quantity' => 1
        ]);

        $order3 = Order::factory()->create(['user_id' => $user3->id]);
        OrderProduct::factory()->create([
            'order_id' => $order3->id,
            'product_id' => $tablet->id,
            'quantity' => 1
        ]);

        // Test filtering by single product name
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['product_names' => ["Gaming Laptop {$timestamp}"]]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertNotContains($order2->id, $orderIds);

        // Test filtering by multiple product names
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['product_names' => ["Gaming Laptop {$timestamp}", "Smartphone {$timestamp}"]]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds);
    }

    public function test_admin_can_filter_orders_by_product_name_like_search()
    {
        // Use unique suffix for this test
        $uniqueId = uniqid('test_');
        $category = Category::factory()->create(['name' => "Electronics{$uniqueId}"]);

        $iphone = Product::factory()->create([
            'name' => "iPhone{$uniqueId} Pro",
            'category_id' => $category->id,
            'stock' => 10
        ]);
        $ipad = Product::factory()->create([
            'name' => "iPad{$uniqueId} Air",
            'category_id' => $category->id,
            'stock' => 8
        ]);
        $macbook = Product::factory()->create([
            'name' => "MacBook{$uniqueId} Pro",
            'category_id' => $category->id,
            'stock' => 5
        ]);
        $android = Product::factory()->create([
            'name' => "Samsung{$uniqueId} Galaxy",
            'category_id' => $category->id,
            'stock' => 12
        ]);

        // Create users and orders
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $user4 = User::factory()->create();

        // Create orders with different products
        $order1 = Order::factory()->create(['user_id' => $user1->id]);
        OrderProduct::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $iphone->id,
            'quantity' => 1
        ]);

        $order2 = Order::factory()->create(['user_id' => $user2->id]);
        OrderProduct::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $ipad->id,
            'quantity' => 1
        ]);

        $order3 = Order::factory()->create(['user_id' => $user3->id]);
        OrderProduct::factory()->create([
            'order_id' => $order3->id,
            'product_id' => $macbook->id,
            'quantity' => 1
        ]);

        $order4 = Order::factory()->create(['user_id' => $user4->id]);
        OrderProduct::factory()->create([
            'order_id' => $order4->id,
            'product_id' => $android->id,
            'quantity' => 1
        ]);

        // Test filtering by partial product name (should find products containing 'Pro')
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['product_name' => "{$uniqueId} Pro"]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds); // iPhone Pro
        $this->assertContains($order3->id, $orderIds); // MacBook Pro
        $this->assertNotContains($order2->id, $orderIds);
        $this->assertNotContains($order4->id, $orderIds);

        // Test filtering by specific unique term
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['product_name' => "Samsung{$uniqueId}"]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order4->id, $orderIds); // Samsung Galaxy
        $this->assertNotContains($order1->id, $orderIds);
        $this->assertNotContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds);
    }

    public function test_user_can_filter_their_own_orders_by_product_filters()
    {
        // Create unique identifiers
        $uniqueId = uniqid('user_filter_');
        $category = Category::factory()->create(['name' => "Electronics{$uniqueId}"]);
        $laptop = Product::factory()->create([
            'name' => "Gaming{$uniqueId} Laptop",
            'category_id' => $category->id,
            'stock' => 10
        ]);
        $phone = Product::factory()->create([
            'name' => "Smartphone{$uniqueId}",
            'category_id' => $category->id,
            'stock' => 15
        ]);

        // Create users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create orders for user1
        $order1 = Order::factory()->create(['user_id' => $user1->id]);
        OrderProduct::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $laptop->id,
            'quantity' => 1
        ]);

        $order2 = Order::factory()->create(['user_id' => $user1->id]);
        OrderProduct::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $phone->id,
            'quantity' => 1
        ]);

        // Create order for user2 (should not appear in user1's results)
        $order3 = Order::factory()->create(['user_id' => $user2->id]);
        OrderProduct::factory()->create([
            'order_id' => $order3->id,
            'product_id' => $laptop->id,
            'quantity' => 1
        ]);

        // Test user can filter their own orders by product name
        $response = $this->withAuth($user1)
            ->getJson(route('user.orders.index', ['product_name' => "Gaming{$uniqueId}"]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertNotContains($order2->id, $orderIds);

        // Test user can filter their own orders by category
        $response = $this->withAuth($user1)
            ->getJson(route('user.orders.index', ['category_names' => ["Electronics{$uniqueId}"]]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds); // Both user1's orders
        $this->assertContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds); // user2's order should not appear
    }

    public function test_combined_filters_work_together()
    {
        // Create unique identifiers
        $uniqueId = uniqid('combined_');
        $electronicsCategory = Category::factory()->create(['name' => "Electronics{$uniqueId}"]);
        $clothingCategory = Category::factory()->create(['name' => "Clothing{$uniqueId}"]);

        // Create products
        $gamingLaptop = Product::factory()->create([
            'name' => "Gaming{$uniqueId} Laptop Pro",
            'category_id' => $electronicsCategory->id,
            'stock' => 5
        ]);
        $regularLaptop = Product::factory()->create([
            'name' => "Business{$uniqueId} Laptop",
            'category_id' => $electronicsCategory->id,
            'stock' => 8
        ]);
        $shirt = Product::factory()->create([
            'name' => "Gaming{$uniqueId} Shirt",
            'category_id' => $clothingCategory->id,
            'stock' => 20
        ]);

        // Create users and orders
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Order with Gaming Laptop Pro (Electronics)
        $order1 = Order::factory()->create(['user_id' => $user1->id]);
        OrderProduct::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $gamingLaptop->id,
            'quantity' => 1
        ]);

        // Order with Business Laptop (Electronics)
        $order2 = Order::factory()->create(['user_id' => $user2->id]);
        OrderProduct::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $regularLaptop->id,
            'quantity' => 1
        ]);

        // Order with Gaming Shirt (Clothing)
        $order3 = Order::factory()->create(['user_id' => $user3->id]);
        OrderProduct::factory()->create([
            'order_id' => $order3->id,
            'product_id' => $shirt->id,
            'quantity' => 1
        ]);

        // Test combining category filter with product name like filter
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', [
                'category_names' => ["Electronics{$uniqueId}"],
                'product_name' => "Gaming{$uniqueId}"
            ]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds); // Only Gaming Laptop Pro from Electronics
        $this->assertNotContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds);

        // Test combining multiple filters
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', [
                'category_names' => ["Electronics{$uniqueId}", "Clothing{$uniqueId}"],
                'product_name' => "Gaming{$uniqueId}"
            ]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds); // Gaming Laptop Pro
        $this->assertContains($order3->id, $orderIds); // Gaming Shirt
        $this->assertNotContains($order2->id, $orderIds); // Business Laptop should not appear
    }

    public function test_admin_can_filter_orders_by_user_name()
    {
        // Create unique identifiers
        $uniqueId = uniqid('user_filter_');
        $category = Category::factory()->create(['name' => "Electronics{$uniqueId}"]);
        $product = Product::factory()->create([
            'name' => "Test Product {$uniqueId}",
            'category_id' => $category->id,
            'stock' => 10
        ]);

        // Create users with unique names
        $user1 = User::factory()->create(['name' => "John{$uniqueId} Doe"]);
        $user2 = User::factory()->create(['name' => "Jane{$uniqueId} Smith"]);
        $user3 = User::factory()->create(['name' => "Bob Wilson{$uniqueId}"]);

        // Create orders for different users
        $order1 = Order::factory()->create(['user_id' => $user1->id]);
        OrderProduct::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $order2 = Order::factory()->create(['user_id' => $user2->id]);
        OrderProduct::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $order3 = Order::factory()->create(['user_id' => $user3->id]);
        OrderProduct::factory()->create([
            'order_id' => $order3->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        // Test filtering by user name like search
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['user_name' => "John{$uniqueId}"]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertNotContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds);

        // Test filtering by partial user name
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['user_name' => $uniqueId]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds); // All users contain the unique ID
        $this->assertContains($order2->id, $orderIds);
        $this->assertContains($order3->id, $orderIds);
    }

    public function test_admin_can_filter_orders_by_user_names()
    {
        // Create unique identifiers
        $uniqueId = uniqid('user_names_');
        $category = Category::factory()->create(['name' => "Electronics{$uniqueId}"]);
        $product = Product::factory()->create([
            'name' => "Test Product {$uniqueId}",
            'category_id' => $category->id,
            'stock' => 10
        ]);

        // Create users with unique names
        $user1 = User::factory()->create(['name' => "Alice{$uniqueId}"]);
        $user2 = User::factory()->create(['name' => "Bob{$uniqueId}"]);
        $user3 = User::factory()->create(['name' => "Charlie{$uniqueId}"]);

        // Create orders for different users
        $order1 = Order::factory()->create(['user_id' => $user1->id]);
        OrderProduct::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $order2 = Order::factory()->create(['user_id' => $user2->id]);
        OrderProduct::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $order3 = Order::factory()->create(['user_id' => $user3->id]);
        OrderProduct::factory()->create([
            'order_id' => $order3->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        // Test filtering by single user name
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['user_names' => ["Alice{$uniqueId}"]]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertNotContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds);

        // Test filtering by multiple user names
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['user_names' => ["Alice{$uniqueId}", "Bob{$uniqueId}"]]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds);
    }

    public function test_admin_can_filter_orders_by_user_ids()
    {
        // Create unique identifiers
        $uniqueId = uniqid('user_ids_');
        $category = Category::factory()->create(['name' => "Electronics{$uniqueId}"]);
        $product = Product::factory()->create([
            'name' => "Test Product {$uniqueId}",
            'category_id' => $category->id,
            'stock' => 10
        ]);

        // Create users
        $user1 = User::factory()->create(['name' => "User1{$uniqueId}"]);
        $user2 = User::factory()->create(['name' => "User2{$uniqueId}"]);
        $user3 = User::factory()->create(['name' => "User3{$uniqueId}"]);

        // Create orders for different users
        $order1 = Order::factory()->create(['user_id' => $user1->id]);
        OrderProduct::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $order2 = Order::factory()->create(['user_id' => $user2->id]);
        OrderProduct::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $order3 = Order::factory()->create(['user_id' => $user3->id]);
        OrderProduct::factory()->create([
            'order_id' => $order3->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        // Test filtering by single user ID
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['user_ids' => [$user1->id]]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertNotContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds);

        // Test filtering by multiple user IDs
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['user_ids' => [$user1->id, $user2->id]]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds);
    }

    public function test_admin_can_filter_orders_by_category_ids()
    {
        // Create unique identifiers
        $uniqueId = uniqid('cat_ids_');

        // Create categories
        $electronicsCategory = Category::factory()->create(['name' => "Electronics{$uniqueId}"]);
        $clothingCategory = Category::factory()->create(['name' => "Clothing{$uniqueId}"]);
        $booksCategory = Category::factory()->create(['name' => "Books{$uniqueId}"]);

        // Create products in different categories
        $laptop = Product::factory()->create([
            'name' => "Laptop {$uniqueId}",
            'category_id' => $electronicsCategory->id,
            'stock' => 10
        ]);
        $shirt = Product::factory()->create([
            'name' => "Shirt {$uniqueId}",
            'category_id' => $clothingCategory->id,
            'stock' => 20
        ]);
        $book = Product::factory()->create([
            'name' => "Book {$uniqueId}",
            'category_id' => $booksCategory->id,
            'stock' => 15
        ]);

        // Create users and orders
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Create orders with different products
        $order1 = Order::factory()->create(['user_id' => $user1->id]);
        OrderProduct::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $laptop->id,
            'quantity' => 1
        ]);

        $order2 = Order::factory()->create(['user_id' => $user2->id]);
        OrderProduct::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $shirt->id,
            'quantity' => 1
        ]);

        $order3 = Order::factory()->create(['user_id' => $user3->id]);
        OrderProduct::factory()->create([
            'order_id' => $order3->id,
            'product_id' => $book->id,
            'quantity' => 1
        ]);

        // Test filtering by single category ID
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['category_ids' => [$electronicsCategory->id]]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertNotContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds);

        // Test filtering by multiple category IDs
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', ['category_ids' => [$electronicsCategory->id, $clothingCategory->id]]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertContains($order2->id, $orderIds);
        $this->assertNotContains($order3->id, $orderIds);
    }

    public function test_all_filters_work_together_comprehensively()
    {
        // Create unique identifiers
        $uniqueId = uniqid('all_filters_');

        // Create categories
        $electronicsCategory = Category::factory()->create(['name' => "Electronics{$uniqueId}"]);
        $clothingCategory = Category::factory()->create(['name' => "Clothing{$uniqueId}"]);

        // Create products
        $laptop = Product::factory()->create([
            'name' => "Gaming{$uniqueId} Laptop",
            'category_id' => $electronicsCategory->id,
            'stock' => 10
        ]);
        $shirt = Product::factory()->create([
            'name' => "Gaming{$uniqueId} Shirt",
            'category_id' => $clothingCategory->id,
            'stock' => 20
        ]);

        // Create users with specific names
        $user1 = User::factory()->create(['name' => "John{$uniqueId}"]);
        $user2 = User::factory()->create(['name' => "Jane{$uniqueId}"]);

        // Create orders
        $order1 = Order::factory()->create(['user_id' => $user1->id]);
        OrderProduct::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $laptop->id,
            'quantity' => 1
        ]);

        $order2 = Order::factory()->create(['user_id' => $user2->id]);
        OrderProduct::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $shirt->id,
            'quantity' => 1
        ]);

        // Test combining user filter with product and category filters
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', [
                'user_names' => ["John{$uniqueId}"],
                'category_ids' => [$electronicsCategory->id],
                'product_name' => "Gaming{$uniqueId}"
            ]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds); // John's order with Gaming Laptop in Electronics
        $this->assertNotContains($order2->id, $orderIds); // Jane's order should not appear

        // Test combining user IDs with category names
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.orders.index', [
                'user_ids' => [$user1->id, $user2->id],
                'category_names' => ["Electronics{$uniqueId}"]
            ]));

        $response->assertStatus(200);
        $responseData = $response->json();
        $orderIds = collect($responseData['data']['items']['data'])->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds); // John's Electronics order
        $this->assertNotContains($order2->id, $orderIds); // Jane's Clothing order should not appear
    }
}
