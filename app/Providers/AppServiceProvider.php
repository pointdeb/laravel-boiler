<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Pointdeb\LaravelCommon\Validators\HttpValidator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        HttpValidator::boot();
    }
}
