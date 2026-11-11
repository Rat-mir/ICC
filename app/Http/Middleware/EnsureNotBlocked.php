<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureNotBlocked {
    public function handle(Request $r,Closure $next) { abort_if($r->user()?->is_blocked,403,'Аккаунт заблокирован.'); return $next($r); }
}
