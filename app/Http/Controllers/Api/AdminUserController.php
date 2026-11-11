<?php
namespace App\Http\Controllers\Api;
use App\Models\User;
use App\Exceptions\BusinessException;
use App\Http\Requests\{UserAdminRequest,CatalogRequest};
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
class AdminUserController {
    /**
     * GET /api/admin/users
     *
     * Список. Доступ: admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Администрирование
     * @authenticated
     * @queryParam per_page integer Параметр списка. Example: 20
     * @queryParam page integer Параметр списка. Example: 1
     * @response 200 {"data":[{"id":2,"name":"Креатор ICC","email":"creator@icc.test","role":"creator","is_blocked":false,"created_at":"2026-11-11T00:00:00Z"}],"links":{"first":"https://api.icc.local/api/admin/users?page=1","last":"https://api.icc.local/api/admin/users?page=1","prev":null,"next":null},"meta":{"current_page":1,"last_page":1,"per_page":20,"total":1}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function index(CatalogRequest $r) { return UserResource::collection(User::latest()->paginate($r->integer('per_page',20))->withQueryString()); }
    /**
     * PATCH /api/admin/users/{user}
     *
     * Изменение. Доступ: admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Администрирование
     * @authenticated
     * @response 200 {"data":{"id":2,"name":"Креатор ICC","email":"creator@icc.test","role":"creator","is_blocked":false,"created_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function update(UserAdminRequest $r,User $user) {
        if($user->id===$r->user()->id) throw new BusinessException('Нельзя менять собственную роль или блокировать свой аккаунт.');
        DB::transaction(function()use($r,$user) {
            $user=User::lockForUpdate()->findOrFail($user->id);
            foreach($r->validated() as $key=>$value) $user->$key=$value;
            $user->save(); if($user->is_blocked || array_key_exists('role',$r->validated())) $user->tokens()->delete();
        });
        return new UserResource($user->refresh());
    }
}
