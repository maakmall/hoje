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
        if (app()->environment('production')) {
            app()->useStoragePath('/tmp/storage');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       // if (app()->environment('production')) {
       //      config([
       //          'filesystems.disks.local.root' => '/tmp',
       //          'view.compiled' => '/tmp/views',
       //          'cache.stores.file.path' => '/tmp/cache',
       //      ]);
       //  }
    }
}
