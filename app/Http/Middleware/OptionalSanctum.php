<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class OptionalSanctum {
    public function handle(Request $r,Closure $next) {
        $user=Auth::guard('sanctum')->user();
        $r->setUserResolver(fn()=>$user && !$user->is_blocked ? $user : null);
        return $next($r);
    }
}
