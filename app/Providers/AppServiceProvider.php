<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request as Req;

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
        if (class_exists('Maatwebsite\Excel\Facades\Excel')) {
            \Maatwebsite\Excel\Facades\Excel::macro('sheetName', function ($name) {
                Req::merge(['sheetName' => $name]);
            });
        }
    }
}
