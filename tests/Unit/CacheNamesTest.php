<?php

namespace Tests\Unit;

use App\CacheNames;
use PHPUnit\Framework\TestCase;

class CacheNamesTest extends TestCase
{
    public function test_enum_values_are_correct()
    {
        $expectedValues = [
            'products_list',
            'product_detail'
        ];

        $this->assertEquals($expectedValues, CacheNames::values());
    }

    public function test_key_generation_without_parameters()
    {
        $cacheKey = CacheNames::PRODUCTS_LIST->key();
        $this->assertEquals('products_list', $cacheKey);
    }

    public function test_key_generation_with_parameters()
    {
        $params = ['category' => 'electronics', 'price' => 100];
        $cacheKey = CacheNames::PRODUCTS_LIST->key($params);

        // Should contain the base value and a hash of parameters
        $this->assertStringStartsWith('products_list_', $cacheKey);
        $this->assertEquals(46, strlen($cacheKey)); // products_list_ (14) + md5 hash (32)
    }



    public function test_paginated_key_generation()
    {
        $params = ['category' => 'electronics'];
        $paginatedKey = CacheNames::PRODUCTS_LIST->paginatedKey($params);

        $this->assertStringStartsWith('products_list_', $paginatedKey);

        // Should include pagination parameters with default values
        $expectedParams = [
            'category' => 'electronics',
            'page' => 1,
            'per_page' => 15, // Use default value instead of config()
            'sort_column' => 'id',
            'sort_direction' => 'DESC'
        ];
        ksort($expectedParams);

        $expectedHash = md5(http_build_query($expectedParams));
        $this->assertStringEndsWith($expectedHash, $paginatedKey);
    }

    public function test_cache_keys_are_consistent()
    {
        $params = ['category' => 'electronics', 'price' => 100];

        $key1 = CacheNames::PRODUCTS_LIST->key($params);
        $key2 = CacheNames::PRODUCTS_LIST->key($params);

        $this->assertEquals($key1, $key2);
    }

    public function test_parameter_order_does_not_affect_cache_key()
    {
        $params1 = ['category' => 'electronics', 'price' => 100];
        $params2 = ['price' => 100, 'category' => 'electronics'];

        $key1 = CacheNames::PRODUCTS_LIST->key($params1);
        $key2 = CacheNames::PRODUCTS_LIST->key($params2);

        $this->assertEquals($key1, $key2);
    }

    public function test_different_parameters_generate_different_keys()
    {
        $params1 = ['category' => 'electronics'];
        $params2 = ['category' => 'clothing'];

        $key1 = CacheNames::PRODUCTS_LIST->key($params1);
        $key2 = CacheNames::PRODUCTS_LIST->key($params2);

        $this->assertNotEquals($key1, $key2);
    }
}
