<?php

namespace App\Console\Commands;

use App\Mail\OrderPlacedNotification;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestOrderNotificationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-order-notification {admin_email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test order notification email to admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $adminEmail = $this->argument('admin_email');

        if (!$adminEmail) {
            // Get first admin email
            $admin = Admin::first();
            if (!$admin) {
                $this->error('No admin found in database. Please create an admin first.');
                return 1;
            }
            $adminEmail = $admin->email;
        }

        // Create sample data for testing
        $user = User::first() ?? User::factory()->create();
        $category = Category::first() ?? Category::factory()->create();
        $product = Product::first() ?? Product::factory()->create([
            'name' => 'Test Product for Email',
            'price' => 99.99,
            'category_id' => $category->id
        ]);

        // Create a sample order
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 199.98,
            'notes' => 'This is a test order for email notification demonstration'
        ]);

        // Create order product
        OrderProduct::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price,
            'total_price' => $product->price * 2,
        ]);

        // Send the email
        try {
            Mail::to($adminEmail)->send(new OrderPlacedNotification($order, $user));
            $this->info("Test order notification email sent successfully to: {$adminEmail}");
            $this->info("Order Number: {$order->order_number}");
            $this->info("Customer: {$user->name} ({$user->email})");
            $this->info("Total Amount: \${$order->total_amount}");

            // Clean up the test order
            $order->orderProducts()->delete();
            $order->delete();

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            return 1;
        }
    }
}
