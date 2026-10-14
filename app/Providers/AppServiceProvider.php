<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::define('manage-cms', fn (User $user): bool => (bool) $user->is_admin);
        RateLimiter::for('cms-login', fn (Request $request): array => [
            Limit::perMinute(5)->by(Str::lower(is_string($request->input('email')) ? $request->input('email') : 'invalid').'|'.$request->ip()),
            Limit::perMinute(30)->by('ip|'.$request->ip()),
        ]);
    }
}
