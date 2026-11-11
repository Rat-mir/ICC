<?php
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
class HealthController {
    /**
     * GET /api/health
     *
     * Проверка приложения и БД. Доступ: гость. Успех 200. Ошибки 401,403,404,409,422,429,500,503. message для ошибок, errors для 422.
     * @group Публикации и реакции
     * @unauthenticated
     * @response 200 {"app":"ICC","time":"2026-11-11T00:00:00+00:00","database":"ok"}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function __invoke() {
        try { DB::select('select 1'); $ok=true; } catch(\Throwable $e) { $ok=false; }
        return response()->json(['app'=>config('app.name'),'time'=>now()->toIso8601String(),'database'=>$ok?'ok':'unavailable','driver'=>config('database.default'),'server_version'=>$ok?DB::selectOne('select version() as version')->version:null],$ok?200:503);
    }
}
