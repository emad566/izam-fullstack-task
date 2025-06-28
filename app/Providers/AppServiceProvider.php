<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(true);
        Schema::defaultStringLength(191);

        // Only force HTTPS in production
        // if (app()->environment('production')) {
            URL::forceScheme('https');
        // }

        Builder::macro('betweenEqual', function ($field, $array) {
            return $this->where($field, '>=', $array[0])
                ->where($field, '<=', $array[1]);
        });

        Builder::macro('like', function ($field, $string) {
            return $string ? $this->where($field, 'like', '%' . $string . '%') : $this;
        });

        Builder::macro('likeEnd', function ($field, $string) {
            return $string ? $this->where($field, 'like', '%' . $string) : $this;
        });

        Builder::macro('likeStart', function ($field, $string) {
            return $string ? $this->where($field,  'like', $string . '%') : $this;
        });
        Builder::macro('orLikeEnd', function ($field, $string) {
            return $string ? $this->orWhere($field, 'like', '%' . $string) : $this;
        });

        Builder::macro('orLikeStart', function ($field, $string) {
            return $string ? $this->orWhere($field,  'like', $string . '%') : $this;
        });

        Builder::macro('orLike', function ($field, $string) {
            return $string ? $this->orWhere($field, 'like', '%' . $string . '%') : $this;
        });

        Builder::macro('active', function () {
            return $this->whereNull('deleted_at');
        });

        Builder::macro('inActive', function () {
            return $this->whereNotNull('deleted_at');
        });
    }
}
