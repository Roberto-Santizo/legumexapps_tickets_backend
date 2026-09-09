<?php

namespace App\Providers;

use App\Interfaces\Storage\ImageStorageServiceInterface;
use App\Services\Storage\ImageStorageService;
use Illuminate\Support\ServiceProvider;

class ImageStorageProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ImageStorageServiceInterface::class, ImageStorageService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
