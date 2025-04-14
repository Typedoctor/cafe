<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        Gate::define('manager', function ($user) {
            return $user->isManager();
        });

        Gate::define('cashier', function ($user) {
            return $user->isCashier();
        });
    }
}