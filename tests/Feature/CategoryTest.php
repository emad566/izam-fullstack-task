<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Tests\TestCase;

class CategoryTest  extends TestCase
{
    protected $category_assertion_array = [
        'id',
        'name',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    public function test_admin_can_list_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->withAuth($this->admin)
            ->getJson(route('admin.categories.index'));

        $assertion_array = $this->list_format;
        $assertion_array['data']['items']['data']['*'] = $this->category_assertion_array;

        $response->assertStatus(200)
            ->assertJsonStructure($assertion_array);
    }

    public function test_admin_can_create_category()
    {
        $category = Category::factory()->make()->toArray();

        $response = $this->withAuth($this->admin)
            ->postJson(route('admin.categories.store'), $category);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'item' => $this->category_assertion_array
                ]
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => $category['name'],
        ]);
    }

    public function test_admin_can_update_category()
    {
        $category = Category::factory()->create();


        $response = $this->withAuth($this->admin)
            ->putJson(route('admin.categories.update', $category->id), $category->toArray());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'item' => $this->category_assertion_array
                ]
            ]);

        $this->assertDatabaseHas('categories', [
            'name' =>  $category->name,
        ]);
    }



    public function test_admin_can_deactivate_category()
    {
        $category = Category::factory()->create();

        $response = $this->withAuth($this->admin)
            ->putJson(route('admin.categories.toggleActive', [$category->id, 'false']));

        $response->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id
        ]);
        $this->assertNotNull(Category::withTrashed()->find($category->id)->deleted_at);
    }

    public function test_admin_can_activate_category()
    {
        $category = Category::factory()->create();

        $response = $this->withAuth($this->admin)
            ->putJson(route('admin.categories.toggleActive', [$category->id, 'true']));

        $response->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id
        ]);
        $this->assertNull(Category::withTrashed()->find($category->id)->deleted_at);
    }

    public function test_admin_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->withAuth($this->admin)
            ->deleteJson(route('admin.categories.destroy', $category->id));

        $response->assertStatus(200);

    }

}
