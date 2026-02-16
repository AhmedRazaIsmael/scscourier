<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Use Bootstrap 5 for pagination
        Paginator::useBootstrapFive();

        // Share user permissions with sidebar view
        View::composer('layouts.sidebar', function ($view) {
            $user = Auth::user();

            if ($user && $user->is_admin) {
                // give admin all permissions from config
                $permissions = config('permissions.all', []);
            } else {
                // user may be null (guest)
                $permissions = $user?->permissions ?? [];
            }

            // ensure it's an array
            if (!is_array($permissions)) {
                $permissions = [];
            }

            $view->with('permissions', $permissions);
        });
    }
}
