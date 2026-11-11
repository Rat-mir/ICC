<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureUserHasRole {
    public function handle(Request $r,Closure $next,string ...$roles) { abort_unless($r->user() && in_array($r->user()->role->value,$roles,true),403,'Недостаточно прав для этой роли.'); return $next($r); }
}
