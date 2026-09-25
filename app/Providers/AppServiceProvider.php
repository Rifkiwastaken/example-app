<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        try {
            \App\Models\SeedUnit::ensureFixed();
        } catch (\Throwable $e) {
            // tabel seed_units mungkin belum ada saat migrate awal
        }

        \Illuminate\Support\Facades\View::composer(['layouts.public', 'landing.*', 'site.*'], function ($view) {
            try {
                $view->with('situs', \App\Models\WebsiteSetting::current());
                $view->with('navJenis', \App\Models\WebsiteContent::navJenis());
            } catch (\Throwable $e) {
                $view->with('situs', new \App\Models\WebsiteSetting());
                $view->with('navJenis', \App\Models\WebsiteContent::JENIS);
            }
        });
    }
}
