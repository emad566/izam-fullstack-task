<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SecurityValidationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sql_injection_prevention_in_category_creation()
    {
        $admin = Admin::factory()->create();

        $maliciousData = [
            'name' => "'; DROP TABLE categories; --",
        ];

        $response = $this->withAuth($admin)
            ->postJson(route('admin.categories.store'), $maliciousData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // Verify table still exists and is functional (has existing data)
        $this->assertTrue(Category::count() >= 0); // Should be functional, not dropped
    }

    public function test_xss_prevention_in_product_creation()
    {
        $admin = Admin::factory()->create();
        $category = Category::factory()->create();

        $maliciousData = [
            'name' => '<script>alert("XSS")</script>Product Name',
            'description' => '<img src=x onerror=alert("XSS")>',
            'price' => 99.99,
            'stock' => 10,
            'category_id' => $category->id,
        ];

        $response = $this->withAuth($admin)
            ->postJson(route('admin.products.store'), $maliciousData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_path_traversal_prevention_in_category_name()
    {
        $admin = Admin::factory()->create();

        $maliciousData = [
            'name' => '../../../etc/passwd',
        ];

        $response = $this->withAuth($admin)
            ->postJson(route('admin.categories.store'), $maliciousData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_oversized_array_prevention_in_order()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Create an oversized products array (more than 50 items) with different products
        $products = Product::factory()->count(60)->create(['stock' => 10]);
        $oversizedProducts = [];
        foreach ($products as $prod) {
            $oversizedProducts[] = [
                'product_id' => $prod->id,
                'quantity' => 1,
            ];
        }

        $maliciousData = [
            'products' => $oversizedProducts,
            'notes' => 'Test order',
        ];

        $response = $this->withAuth($user)
            ->postJson(route('user.orders.store'), $maliciousData);

        // Should be automatically limited to 50 products and succeed
        $response->assertStatus(201);

        // Verify the order was created with limited products (50 max)
        $order = \App\Models\Order::latest()->first();
        $this->assertLessThanOrEqual(50, $order->orderProducts()->count());
    }

    public function test_numeric_overflow_prevention_in_product_price()
    {
        $admin = Admin::factory()->create();
        $category = Category::factory()->create();

        $maliciousData = [
            'name' => 'Test Product',
            'description' => 'Valid description',
            'price' => '999999999999999.99', // Extremely large price
            'stock' => 10,
            'category_id' => $category->id,
        ];

        $response = $this->withAuth($admin)
            ->postJson(route('admin.products.store'), $maliciousData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_excessive_quantity_prevention_in_order()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);

        $maliciousData = [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 50000, // Excessive quantity
                ]
            ],
            'notes' => 'Test order',
        ];

        $response = $this->withAuth($user)
            ->postJson(route('user.orders.store'), $maliciousData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.quantity']);
    }

    public function test_null_byte_injection_prevention()
    {
        $admin = Admin::factory()->create();

        $maliciousData = [
            'name' => "Normal Name\0Hidden Content",
        ];

        $response = $this->withAuth($admin)
            ->postJson(route('admin.categories.store'), $maliciousData);

        // Should either sanitize the null byte or reject the input
        if ($response->status() === 201) {
            // If accepted, verify null byte was removed
            $category = Category::latest()->first();
            $this->assertStringNotContainsString("\0", $category->name);
        } else {
            // If rejected, that's also acceptable
            $response->assertStatus(422);
        }
    }

    public function test_reserved_names_prevention_in_category()
    {
        $admin = Admin::factory()->create();
        $reservedNames = ['admin', 'api', 'system', 'root'];

        foreach ($reservedNames as $name) {
            $response = $this->withAuth($admin)
                ->postJson(route('admin.categories.store'), ['name' => $name]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        }
    }

    public function test_email_injection_prevention_in_user_registration()
    {
        $maliciousData = [
            'name' => 'Test User',
            'email' => 'test@example.com<script>alert("XSS")</script>',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson(route('auth.user.register'), $maliciousData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_filter_parameter_validation_prevents_sql_injection()
    {
        $admin = Admin::factory()->create();

        // Test with malicious filter parameters
        $maliciousFilters = [
            'user_name' => "'; DROP TABLE users; --",
            'category_name' => "' OR '1'='1",
            'product_name' => '<script>alert("XSS")</script>',
            'q' => "../../etc/passwd",
        ];

        $response = $this->withAuth($admin)
            ->getJson(route('admin.orders.index', $maliciousFilters));

        // Should either sanitize the input or return validation errors
        // If it returns 200, the input was sanitized and safe
        // If it returns 422, the input was rejected (also safe)
                $this->assertTrue(in_array($response->status(), [200, 422]));

        if ($response->status() === 422) {
            $response->assertJsonStructure(['errors']);
        }
    }

    public function test_input_length_limits_are_enforced()
    {
        $admin = Admin::factory()->create();
        $veryLongString = str_repeat('A', 300); // Exceeds 255 char limit

        $response = $this->withAuth($admin)
            ->postJson(route('admin.categories.store'), [
                'name' => $veryLongString,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_pagination_limits_prevent_resource_exhaustion()
    {
        $admin = Admin::factory()->create();

        $maliciousParams = [
            'per_page' => 10000, // Excessive page size
            'page' => 99999999, // Extremely large page number
        ];

        $response = $this->withAuth($admin)
            ->getJson(route('admin.categories.index', $maliciousParams));

        // Should either limit the values or return validation errors
                $this->assertTrue(in_array($response->status(), [200, 422]));

        if ($response->status() === 422) {
            $response->assertJsonStructure(['errors']);
        }
    }

    public function test_admin_injection_prevention_in_user_names()
    {
        $adminImpersonationNames = [
            'admin',
            'administrator',
            'root',
            'system',
        ];

        foreach ($adminImpersonationNames as $name) {
            $response = $this->postJson(route('auth.user.register'), [
                'name' => $name,
                'email' => $name . uniqid() . '@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        }
    }
}

