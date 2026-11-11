<?php
use App\Exceptions\BusinessException;
use App\Http\Middleware\{ApiLogger,EnsureNotBlocked,EnsureUserHasRole,OptionalSanctum};
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions,Middleware};
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php',api: __DIR__.'/../routes/api.php',commands: __DIR__.'/../routes/console.php',health: '/up')
    ->withMiddleware(function(Middleware $m): void {
        $m->api(prepend: [ApiLogger::class]);
        $m->alias(['role'=>EnsureUserHasRole::class,'active'=>EnsureNotBlocked::class,'optional.auth'=>OptionalSanctum::class]);
    })
    ->withExceptions(function(Exceptions $e): void {
        $e->shouldRenderJsonWhen(fn(Request $r)=>$r->is('api/*') || $r->expectsJson());
        $e->render(function(ValidationException $x,Request $r) {
            if($r->is('api/*')) return response()->json(['message'=>'Ошибка валидации','errors'=>$x->errors()],422);
        });
        $e->render(function(BusinessException $x,Request $r) {
            if($r->is('api/*')) return response()->json(['message'=>$x->getMessage()],409);
        });
        $e->render(function(QueryException $x,Request $r) {
            if($r->is('api/*') && in_array((string)$x->getCode(),['23000','23503','23505'],true)) return response()->json(['message'=>'Операция нарушает целостность связанных данных.'],409);
        });
        $e->render(function(\Throwable $x,Request $r) {
            if(!$r->is('api/*')) return null;
            if($x instanceof \Illuminate\Auth\AuthenticationException) return response()->json(['message'=>'Необходим токен доступа.'],401);
            if($x instanceof \Illuminate\Auth\Access\AuthorizationException) return response()->json(['message'=>'Недостаточно прав.'],403);
            if($x instanceof HttpExceptionInterface) {
                $code=$x->getStatusCode();
                $message=match($code) {401=>$x->getMessage() ?: 'Необходим токен доступа.',403=>$x->getMessage() ?: 'Недостаточно прав.',404=>'Запись не найдена.',405=>'Метод не поддерживается.',429=>'Слишком много запросов. Повторите позже.',default=>$code>=500?'Внутренняя ошибка сервера.':$x->getMessage()};
                return response()->json(['message'=>$message],$code,$x->getHeaders());
            }
            if(!$x instanceof ValidationException && !$x instanceof BusinessException && !config('app.debug')) return response()->json(['message'=>'Внутренняя ошибка сервера.'],500);
        });
    })->create();
