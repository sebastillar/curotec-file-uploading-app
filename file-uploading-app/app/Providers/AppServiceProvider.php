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
use App\Repositories\FileCollaboratorRepositoryEloquent;
use \App\Repositories\Interfaces\FileCollaboratorRepository;
use App\Services\FileCollaborationService;
use App\Services\Interfaces\FileCollaborationServiceInterface;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\AuthService;
use App\Repositories\Interfaces\UserRepository;
use App\Repositories\UserRepositoryEloquent;

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

        $this->app->bind(
            FileCollaborationServiceInterface::class,
            FileCollaborationService::class
        );

        $this->app->bind(
            AuthServiceInterface::class,
            AuthService::class
        );

        $this->app->bind(FileRepository::class, FileRepositoryEloquent::class);
        $this->app->bind(FileVersionRepository::class, FileVersionRepositoryEloquent::class);
        $this->app->bind(FileVersionCommentRepository::class, FileVersionCommentRepositoryEloquent::class);
        $this->app->bind(FileCollaboratorRepository::class,FileCollaboratorRepositoryEloquent::class);
        $this->app->bind(UserRepository::class,UserRepositoryEloquent::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
