<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Category;

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
     * Bootstrap application services.
     */
    public function boot(): void
    {
        /**
         * cartCount globale:
         * - usa lo stesso formato del CartController
         * - cart = array piatto in sessione
         * - badge = somma delle qty
         */
        View::composer('*', function ($view) {
            $cart = session()->get('cart', []);
            if (!is_array($cart)) {
                $cart = [];
            }

            $count = array_sum(
                array_map(fn ($item) => (int) ($item['qty'] ?? 1), $cart)
            );

            $view->with('cartCount', $count);
        });

        /**
         * Categorie attive per navigation + footer
         */
        View::composer(['layouts.navigation', 'layouts.footer'], function ($view) {
            $categoriesNav = Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

            $view->with('categoriesNav', $categoriesNav);
        });
    }
}
