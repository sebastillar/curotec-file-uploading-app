<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FileService;
use App\Repositories\Interfaces\FileRepository;
use App\Repositories\Interfaces\FileVersionRepository;
use App\Repositories\Interfaces\FileVersionCommentRepository;
use App\Services\Interfaces\FileServiceInterface;
use App\Repositories\FileRepositoryEloquent;
use App\Repositories\FileVersionRepositoryEloquent;
use App\Repositories\FileVersionCommentRepositoryEloquent;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FileServiceInterface::class, function ($app) {
            return new FileService(
                $app->make(FileRepository::class),
                $app->make(FileVersionRepository::class),
                $app->make(FileVersionCommentRepository::class)
            );
        });

        $this->app->bind(FileRepository::class, FileRepositoryEloquent::class);
        $this->app->bind(FileVersionRepository::class, FileVersionRepositoryEloquent::class);
        $this->app->bind(FileVersionCommentRepository::class, FileVersionCommentRepositoryEloquent::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
