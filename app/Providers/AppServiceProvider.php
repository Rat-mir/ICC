<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
class AppServiceProvider extends ServiceProvider {
    public function register(): void {}
    public function boot(): void {
        RateLimiter::for('login',fn(Request $r)=>Limit::perMinute(10)->by($r->ip()));
        RateLimiter::for('api',fn(Request $r)=>Limit::perMinute(120)->by($r->user()?->id ?: $r->ip()));
        RateLimiter::for('media',fn(Request $r)=>Limit::perMinute(1200)->by($r->user()?->id ?: $r->ip()));
    }
}
