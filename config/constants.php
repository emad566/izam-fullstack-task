<?php

return [
    /**
     * Products cache duration
     */
    'products_cache_duration' => env('PRODUCTS_CACHE_DURATION', 60), // 60 minutes = 1 hour

    /**
     * Pagination validation and settings
     */
    'per_page' => env('PER_PAGE', 50),  // 50 items per page
    'list_validations' => [
        'per_page' => 'nullable|numeric|min:1|max:100',
        'page' => 'nullable|numeric|min:1|max:1000',
        'sort_direction' => 'nullable|in:ASC,DESC',
        'date_from' => 'nullable|date_format:Y-m-d\TH:i:s.v\Z',
        'date_to' => 'nullable|date_format:Y-m-d\TH:i:s.v\Z',
    ],
];
