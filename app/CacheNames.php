<?php

namespace App;

enum CacheNames: string
{
    case PRODUCTS_LIST = 'products_list';
    case PRODUCT_DETAIL = 'product_detail';

    /**
     * Get all enum values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Generate a cache key with parameters
     */
    public function key(array $params = []): string
    {
        if (empty($params)) {
            return $this->value;
        }

        // Sort parameters for consistent cache keys
        ksort($params);
        $paramString = http_build_query($params);

        return $this->value . '_' . md5($paramString);
    }



    /**
     * Generate a cache key with pagination parameters
     */
    public function paginatedKey(array $params = []): string
    {
        // Include pagination-specific parameters
        $paginationParams = [
            'page' => $params['page'] ?? 1,
            'per_page' => $params['per_page'] ?? 15,
            'sort_column' => $params['sortColumn'] ?? 'id',
            'sort_direction' => $params['sortDirection'] ?? 'DESC'
        ];

        // Merge with other parameters
        $allParams = array_merge($params, $paginationParams);

        return $this->key($allParams);
    }
}
