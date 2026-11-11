<?php
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
class HealthController {
    /**
     * GET /api/health
     *
     * Проверка приложения и БД. Доступ: гость. Успех 200. При недоступной БД возвращается 503 с database=unavailable.
     * @group Состояние сервера
     * @unauthenticated
     * @response 200 {"app":"ICC","time":"2026-11-11T00:00:00+00:00","database":"ok"}
     * @response 503 {"app":"ICC","database":"unavailable","driver":"mysql","server_version":null}
     */
    public function __invoke() {
        try { DB::select('select 1'); $ok=true; } catch(\Throwable $e) { $ok=false; }
        return response()->json(['app'=>config('app.name'),'time'=>now()->toIso8601String(),'database'=>$ok?'ok':'unavailable','driver'=>config('database.default'),'server_version'=>$ok?DB::selectOne('select version() as version')->version:null],$ok?200:503);
    }
}
