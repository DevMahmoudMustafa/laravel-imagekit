<?php

namespace DevMahmoudMustafa\ImageKit\Providers;

use DevMahmoudMustafa\ImageKit\ImageKitService;
use Illuminate\Support\ServiceProvider;

class ImageKitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/imagekit.php', 'imagekit');
        
        // Bind as singleton but with reset capability
        $this->app->singleton('imagekit', function ($app) {
            return $app->make(ImageKitService::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/imagekit.php' => config_path('imagekit.php'),
        ], 'imagekit-config');
    }
}
