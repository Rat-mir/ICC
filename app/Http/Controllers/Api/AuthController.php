<?php
namespace App\Http\Controllers\Api;
use App\Http\Requests\{RegisterRequest,LoginRequest};
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
class AuthController {
    public function __construct(private AuthService $service) {}
    /**
     * POST /api/register
     *
     * Регистрация. Доступ: гость. Успех 201. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Аутентификация
     * @unauthenticated
     * @response 201 {"user":{"id":2,"name":"Креатор ICC","email":"creator@icc.test","role":"creator","is_blocked":false},"token":"<токен>","token_type":"Bearer","expires_in":86400}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function register(RegisterRequest $r) { return response()->json($this->service->token($this->service->register($r->validated())),201); }
    /**
     * POST /api/login
     *
     * Вход и токен. Доступ: гость. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Аутентификация
     * @unauthenticated
     * @response 200 {"user":{"id":2,"name":"Креатор ICC","email":"creator@icc.test","role":"creator","is_blocked":false},"token":"<токен>","token_type":"Bearer","expires_in":86400}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function login(LoginRequest $r) { return response()->json($this->service->token($this->service->login($r->validated()))); }
    /**
     * GET /api/user
     *
     * Текущий пользователь. Доступ: авторизованный; свой объект/admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Аутентификация
     * @authenticated
     * @response 200 {"data":{"id":2,"name":"Креатор ICC","email":"creator@icc.test","role":"creator","is_blocked":false,"created_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function user(Request $r) { return new UserResource($r->user()); }
    /**
     * POST /api/logout
     *
     * Отзыв токена. Доступ: авторизованный; свой объект/admin. Успех 204. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Аутентификация
     * @authenticated
     * @response 204
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function logout(Request $r) { $r->user()->currentAccessToken()?->delete(); return response()->noContent(); }
}
