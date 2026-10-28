<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Support/helpers.php');
        $this->app->singleton(CartService::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::defaultView('components.pagination');

        View::composer('layouts.app', function ($view) {
            $view->with('cartCount', app(CartService::class)->count())
                ->with('compareCount', count(session('compare', [])))
                ->with('navCategories', Category::orderBy('sort_order')->get());
        });
    }
}
