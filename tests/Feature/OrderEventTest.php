<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Listeners\SendOrderNotificationToAdmin;
use App\Mail\OrderPlacedNotification;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderEventTest extends TestCase
{
    use DatabaseTransactions;

    protected $testUser;
    protected $testAdmin;
    protected $category;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUser = User::factory()->create();
        $this->testAdmin = Admin::factory()->create();
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 10,
            'price' => 99.99
        ]);
    }

    public function test_order_placed_event_is_fired_when_order_is_created()
    {
        Event::fake([OrderPlaced::class]);

        $orderData = [
            'products' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2
                ]
            ],
            'notes' => 'Test order for event testing'
        ];

        $response = $this->withAuth($this->testUser)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(201);

        // Assert that the OrderPlaced event was fired
        Event::assertDispatched(OrderPlaced::class, function ($event) {
            return $event->order->user_id === $this->testUser->id
                && $event->user->id === $this->testUser->id;
        });
    }

    public function test_order_placed_event_contains_correct_data()
    {
        Event::fake([OrderPlaced::class]);

        $orderData = [
            'products' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1
                ]
            ],
            'notes' => 'Event data test'
        ];

        $response = $this->withAuth($this->testUser)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(201);

        Event::assertDispatched(OrderPlaced::class, function ($event) {
            // Verify event contains the correct order and user
            $this->assertNotNull($event->order);
            $this->assertNotNull($event->user);
            $this->assertEquals($this->testUser->id, $event->user->id);
            $this->assertEquals($this->testUser->id, $event->order->user_id);
            $this->assertEquals('Event data test', $event->order->notes);
            $this->assertEquals(99.99, $event->order->total_amount);

            return true;
        });
    }

    public function test_send_order_notification_listener_sends_email_to_admin()
    {
        Mail::fake();

        // Create specific admins to test that they receive notifications
        $admin1 = Admin::factory()->create(['email' => 'testemail1@example.com']);
        $admin2 = Admin::factory()->create(['email' => 'testemail2@example.com']);

        $orderData = [
            'products' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3
                ]
            ],
            'notes' => 'Email notification test'
        ];

        $response = $this->withAuth($this->testUser)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(201);

        // Assert that emails were sent to our specific admins
        Mail::assertSent(OrderPlacedNotification::class, function ($mail) use ($admin1) {
            return $mail->hasTo($admin1->email);
        });

        Mail::assertSent(OrderPlacedNotification::class, function ($mail) use ($admin2) {
            return $mail->hasTo($admin2->email);
        });

        // Assert that the OrderPlacedNotification was sent (at least once)
        Mail::assertSent(OrderPlacedNotification::class);
    }

    public function test_order_placed_notification_email_contains_correct_data()
    {
        Mail::fake();

        $orderData = [
            'products' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2
                ]
            ],
            'notes' => 'Email content test'
        ];

        $response = $this->withAuth($this->testUser)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(201);

        Mail::assertSent(OrderPlacedNotification::class, function ($mail) {
            // Verify the mail contains the correct order and user data
            $this->assertEquals($this->testUser->id, $mail->user->id);
            $this->assertNotNull($mail->order);
            $this->assertEquals($this->testUser->id, $mail->order->user_id);
            $this->assertEquals('Email content test', $mail->order->notes);
            $this->assertEquals(199.98, $mail->order->total_amount); // 2 * 99.99

            return true;
        });
    }

    public function test_order_placed_notification_has_correct_subject()
    {
        Mail::fake();

        $orderData = [
            'products' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->withAuth($this->testUser)
            ->postJson(route('user.orders.store'), $orderData);

        $response->assertStatus(201);

        Mail::assertSent(OrderPlacedNotification::class, function ($mail) {
            $envelope = $mail->envelope();
            $this->assertStringContainsString('New Order Placed - Order #', $envelope->subject);
            $this->assertStringContainsString($mail->order->order_number, $envelope->subject);

            return true;
        });
    }

        public function test_listener_handles_failed_email_gracefully()
    {
        // Create a listener instance
        $listener = new SendOrderNotificationToAdmin();

        // Create an order directly using the factory
        $order = \App\Models\Order::factory()->create([
            'user_id' => $this->testUser->id,
            'total_amount' => 99.99
        ]);

        $event = new OrderPlaced($order, $this->testUser);

        // This should not throw an exception, but log the error
        try {
            $listener->failed($event, new \Exception('Test exception'));
            $this->assertTrue(true); // If we reach here, the method handled the failure gracefully
        } catch (\Exception $e) {
            $this->fail('Listener failed method should handle exceptions gracefully');
        }
    }

    public function test_multiple_order_events_fire_correctly()
    {
        Event::fake([OrderPlaced::class]);

        // Create first order
        $orderData1 = [
            'products' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1
                ]
            ],
            'notes' => 'First order'
        ];

        $response1 = $this->withAuth($this->testUser)
            ->postJson(route('user.orders.store'), $orderData1);

        $response1->assertStatus(201);

        // Create second order
        $orderData2 = [
            'products' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2
                ]
            ],
            'notes' => 'Second order'
        ];

        $response2 = $this->withAuth($this->testUser)
            ->postJson(route('user.orders.store'), $orderData2);

        $response2->assertStatus(201);

        // Assert that OrderPlaced event was fired twice
        Event::assertDispatched(OrderPlaced::class, 2);
    }
}
