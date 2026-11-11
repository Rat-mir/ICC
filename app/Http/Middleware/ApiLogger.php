<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class ApiLogger {
    public function handle(Request $r,Closure $next) {
        $start=microtime(true); $code=500;
        try { $response=$next($r); $code=$response->getStatusCode(); return $response; }
        catch(\Throwable $e) { $code=app(\Illuminate\Contracts\Debug\ExceptionHandler::class)->render($r,$e)->getStatusCode(); throw $e; }
        finally { Log::info('api.request',['method'=>$r->method(),'path'=>$r->path(),'user_id'=>$r->user()?->id,'status'=>$code,'duration_ms'=>round((microtime(true)-$start)*1000,2)]); }
    }
}
