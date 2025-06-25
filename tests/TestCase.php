<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{

    protected ?User $user;

    protected $list_format = [
        'status',
        'message',
        'data' => [
            'items' => [
                'data' => [
                    '*' => [],
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next'
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [
                        '*' => [
                            'url',
                            'label',
                            'active'
                        ]
                    ],
                    'path',
                    'per_page',
                    'to',
                    'total'
                ]
            ]
        ],
        'errors',
        'response_code',
        'request_data'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Set default locale
        app()->setLocale('en');


        try {
            $this->user = User::first();
        } catch (\Exception $e) {
            Artisan::call('migrate:fresh');
            Artisan::call('db:seed');
            
            $this->user = User::first();
        }

    }

    protected function getAuthToken(User $user): string
    {
        return $user->createToken('test-token')->plainTextToken;
    }

    protected function withAuth(User $user): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->getAuthToken($user));
    }
}
