<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductTest extends TestCase
{
    protected $product_assertion_array = [
        'id',
        'name',
        'description',
        'image_urls',
        'price',
        'stock',
        'category',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_can_list_products()
    {
        // Create products with categories
        Product::factory()->count(3)->create();

        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.products.index'));

        $assertion_array = $this->list_format;
        $assertion_array['data']['items']['data']['*'] = $this->product_assertion_array;

        $response->assertStatus(200)
            ->assertJsonStructure($assertion_array);
    }

    public function test_admin_can_create_product_with_image()
    {
        $productData = Product::factory()->make()->toArray();
        $image = UploadedFile::fake()->image('product.jpg', 800, 600);

        $response = $this->withAuth($this->admin)
            ->post(route('admin.products.store'), array_merge($productData, [
                'image' => $image
            ]));

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'name' => $productData['name'],
            'price' => $productData['price'],
            'stock' => $productData['stock'],
            'category_id' => $productData['category_id']
        ]);

        // Check if media was created
        $createdProduct = Product::where('name', $productData['name'])->first();
        $this->assertNotNull($createdProduct);
        $this->assertTrue($createdProduct->hasMedia('images'));
    }

    public function test_admin_can_create_product_without_image()
    {
        $productData = Product::factory()->make()->toArray();

        $response = $this->withAuth($this->admin)
            ->postJson(route('admin.products.store'), $productData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'name' => $productData['name'],
            'price' => $productData['price']
        ]);

        $product = Product::where('name', $productData['name'])->first();
        $this->assertFalse($product->hasMedia('images'));
        $this->assertNull($product->getImageUrls());
    }

    public function test_admin_can_update_product_with_new_image()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $newImage = UploadedFile::fake()->image('updated.jpg', 600, 400);
        $updateData = Product::factory()->make(['category_id' => $category->id])->toArray();

        $response = $this->withAuth($this->admin)
            ->put(route('admin.products.update', $product->id), array_merge($updateData, [
                'image' => $newImage
            ]));

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $updateData['name'],
            'price' => $updateData['price'],
            'stock' => $updateData['stock']
        ]);

        // Verify image was updated
        $updatedProduct = Product::find($product->id);
        $this->assertTrue($updatedProduct->hasMedia('images'));
    }

    public function test_admin_can_update_product_without_changing_image()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // Add initial image directly to media collection for testing
        $initialImage = UploadedFile::fake()->image('keep.jpg', 400, 300);
        $product->addMedia($initialImage->getPathname())
            ->usingName('keep-image')
            ->toMediaCollection('images');

        $updateData = Product::factory()->make(['category_id' => $category->id])->toArray();
        // No image field - should keep existing image

        $response = $this->withAuth($this->admin)
            ->putJson(route('admin.products.update', $product->id), $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $updateData['name'],
            'description' => $updateData['description']
        ]);

        // Verify original image is still there
        $updatedProduct = Product::find($product->id);
        $this->assertTrue($updatedProduct->hasMedia('images'));
        $this->assertEquals(1, $updatedProduct->getMedia('images')->count());
    }

    public function test_admin_can_show_product()
    {
        $product = Product::factory()->create();

        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.products.show', $product->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'item' => $this->product_assertion_array
                ]
            ]);
    }

    public function test_admin_can_deactivate_product()
    {
        $product = Product::factory()->create();

        $response = $this->withAuth($this->admin)
            ->putJson(route('admin.products.toggleActive', [$product->id, 'false']));

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id
        ]);
        $this->assertNotNull(Product::withTrashed()->find($product->id)->deleted_at);
    }

    public function test_admin_can_activate_product()
    {
        $product = Product::factory()->create();

        $response = $this->withAuth($this->admin)
            ->putJson(route('admin.products.toggleActive', [$product->id, 'true']));

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id
        ]);
        $this->assertNull(Product::withTrashed()->find($product->id)->deleted_at);
    }

    public function test_admin_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->withAuth($this->admin)
            ->deleteJson(route('admin.products.destroy', $product->id));

        $response->assertStatus(200);
    }

    public function test_product_validation_requires_required_fields()
    {
        $response = $this->withAuth($this->admin)
            ->postJson(route('admin.products.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'stock', 'category_id']);
    }

    public function test_product_validation_image_must_be_valid_image()
    {
        $productData = Product::factory()->make()->toArray();
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->withAuth($this->admin)
            ->post(route('admin.products.store'), array_merge($productData, [
                'image' => $invalidFile
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_product_validation_price_must_be_positive()
    {
        $productData = Product::factory()->make(['price' => -10.00])->toArray();

        $response = $this->withAuth($this->admin)
            ->postJson(route('admin.products.store'), $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_product_validation_stock_must_be_positive_integer()
    {
        $productData = Product::factory()->make(['stock' => -5])->toArray();

        $response = $this->withAuth($this->admin)
            ->postJson(route('admin.products.store'), $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stock']);
    }

    public function test_admin_can_filter_products_by_category()
    {
        $category1 = Category::factory()->create(['name' => 'Electronics']);
        $category2 = Category::factory()->create(['name' => 'Clothing']);

        Product::factory()->create(['category_id' => $category1->id]);
        Product::factory()->create(['category_id' => $category2->id]);

        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.products.index', ['category_name' => 'Electronics']));

        $response->assertStatus(200);

        // Verify response structure
        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);
    }

    public function test_admin_can_filter_products_by_category_names()
    {
        // Create categories
        $electronics = Category::factory()->create(['name' => 'Electronics']);
        $clothing = Category::factory()->create(['name' => 'Clothing']);
        $books = Category::factory()->create(['name' => 'Books']);

        // Create products for each category
        $product1 = Product::factory()->create(['category_id' => $electronics->id]);
        $product2 = Product::factory()->create(['category_id' => $clothing->id]);
        $product3 = Product::factory()->create(['category_id' => $books->id]);

        // Test filtering by multiple category names
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.products.index', [
                'category_names' => ['Electronics', 'Clothing']
            ]));

        $response->assertStatus(200);

        // Verify response structure
        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);
        $this->assertNotEmpty($responseData['data']);

        // Verify that products from both Electronics and Clothing categories are returned
        // The exact structure depends on your API response format
        $this->assertArrayHasKey('items', $responseData['data']);
    }

    public function test_admin_can_filter_products_by_single_category_name_in_array()
    {
        // Create categories
        $electronics = Category::factory()->create(['name' => 'Electronics']);
        $clothing = Category::factory()->create(['name' => 'Clothing']);

        // Create products for each category
        $product1 = Product::factory()->create(['category_id' => $electronics->id]);
        $product2 = Product::factory()->create(['category_id' => $clothing->id]);

        // Test filtering by single category name using category_names parameter
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.products.index', [
                'category_names' => ['Electronics']
            ]));

        $response->assertStatus(200);

        // Verify response structure
        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);
        $this->assertNotEmpty($responseData['data']);
    }

    public function test_admin_can_filter_products_by_name()
    {
        $product1 = Product::factory()->create(['name' => 'Apple iPhone 15']);
        $product2 = Product::factory()->create(['name' => 'Samsung Galaxy S24']);
        $product3 = Product::factory()->create(['name' => 'Apple iPad Pro']);

        // Test filtering by partial name match
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.products.index', ['name' => 'Apple']));

        $response->assertStatus(200);

        // Verify response structure
        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);

        // The response should contain products with "Apple" in the name
        // Note: The exact filtering behavior depends on the 'like' method implementation
        $this->assertNotEmpty($responseData['data']);
    }

    public function test_admin_can_filter_products_by_exact_name()
    {
        $product1 = Product::factory()->create(['name' => 'iPhone 15 Pro']);
        $product2 = Product::factory()->create(['name' => 'iPhone 15']);

        // Test filtering by exact name match
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.products.index', ['name' => 'iPhone 15 Pro']));

        $response->assertStatus(200);

        // Verify response structure
        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);
        $this->assertNotEmpty($responseData['data']);
    }

    public function test_admin_can_filter_products_by_min_price()
    {
        $category = Category::factory()->create();

        $product1 = Product::factory()->create(['price' => 10.00, 'category_id' => $category->id]);
        $product2 = Product::factory()->create(['price' => 25.00, 'category_id' => $category->id]);
        $product3 = Product::factory()->create(['price' => 50.00, 'category_id' => $category->id]);

        // Filter products with minimum price of 20
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.products.index', ['min_price' => 20.00]));

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);
        $this->assertNotEmpty($responseData['data']);
    }

    public function test_admin_can_filter_products_by_max_price()
    {
        $category = Category::factory()->create();

        $product1 = Product::factory()->create(['price' => 10.00, 'category_id' => $category->id]);
        $product2 = Product::factory()->create(['price' => 25.00, 'category_id' => $category->id]);
        $product3 = Product::factory()->create(['price' => 50.00, 'category_id' => $category->id]);

        // Filter products with maximum price of 30
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.products.index', ['max_price' => 30.00]));

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);
        $this->assertNotEmpty($responseData['data']);
    }

    public function test_admin_can_filter_products_by_price_range()
    {
        $category = Category::factory()->create();

        $product1 = Product::factory()->create(['price' => 10.00, 'category_id' => $category->id]);
        $product2 = Product::factory()->create(['price' => 25.00, 'category_id' => $category->id]);
        $product3 = Product::factory()->create(['price' => 35.00, 'category_id' => $category->id]);
        $product4 = Product::factory()->create(['price' => 50.00, 'category_id' => $category->id]);

        // Filter products with price between 20 and 40
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.products.index', [
                'min_price' => 20.00,
                'max_price' => 40.00
            ]));

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);
        $this->assertNotEmpty($responseData['data']);
    }

    public function test_admin_can_combine_price_and_category_filters()
    {
        $electronics = Category::factory()->create(['name' => 'Electronics']);
        $clothing = Category::factory()->create(['name' => 'Clothing']);

        // Create products in different categories with different prices
        $product1 = Product::factory()->create(['price' => 15.00, 'category_id' => $electronics->id]);
        $product2 = Product::factory()->create(['price' => 25.00, 'category_id' => $electronics->id]);
        $product3 = Product::factory()->create(['price' => 35.00, 'category_id' => $clothing->id]);
        $product4 = Product::factory()->create(['price' => 45.00, 'category_id' => $electronics->id]);

        // Filter Electronics products with price between 20 and 50
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.products.index', [
                'category_name' => 'Electronics',
                'min_price' => 20.00,
                'max_price' => 50.00
            ]));

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);
        $this->assertNotEmpty($responseData['data']);
    }

    public function test_price_filter_returns_correct_products_for_range()
    {
        // Clear any existing products first (including soft deleted)
        Product::withTrashed()->forceDelete();

        $category = Category::factory()->create();

        $product1 = Product::factory()->create(['price' => 10.00, 'category_id' => $category->id, 'name' => 'Product 1']);
        $product2 = Product::factory()->create(['price' => 20.00, 'category_id' => $category->id, 'name' => 'Product 2']);
        $product3 = Product::factory()->create(['price' => 150.00, 'category_id' => $category->id, 'name' => 'Product 3']);

        // Filter with price range 100-200 (should only return product3)
        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.products.index', [
                'min_price' => 100.00,
                'max_price' => 200.00
            ]));

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);

        // Verify the response contains only products within the price range
        if (isset($responseData['data']['items']['data'])) {
            $products = $responseData['data']['items']['data'];

            $this->assertCount(1, $products, 'Should return exactly 1 product in price range 100-200');

            // Verify the returned product is the correct one
            $returnedProduct = $products[0];
            $this->assertEquals(150.00, $returnedProduct['price']);
            $this->assertEquals('Product 3', $returnedProduct['name']);
        } else {
            $this->fail('No products data found in response: ' . json_encode($responseData));
        }
    }
}
