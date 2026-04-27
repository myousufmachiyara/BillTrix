<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Fix "Specified key was too long" on MySQL < 5.7.7 / MariaDB < 10.2.2
        Schema::defaultStringLength(191);
    }
}