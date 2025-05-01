<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        Gate::define('only-admins', function (User $user) {
            return $user->role === UserRole::ADMIN;
        });

        Gate::define('article-manage', function (User $user, Article $article) {
            return $user->id === $article->user_id || $user->role === UserRole::ADMIN;
        });
    }
}
