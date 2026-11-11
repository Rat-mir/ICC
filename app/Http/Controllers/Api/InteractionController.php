<?php
namespace App\Http\Controllers\Api;
use App\Models\{Post,Comment};
use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
class InteractionController {
    public function __construct(private PostService $service) {}
    /**
     * POST /api/posts/{post}/comments
     *
     * Добавление комментария. Доступ: авторизованный; свой объект/admin. Успех 201. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Публикации и реакции
     * @authenticated
     * @response 201 {"data":{"id":1,"user_id":3,"post_id":1,"body":"Отличный образ!","author":{"id":3,"name":"Пользователь ICC"},"created_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function comment(CommentRequest $r,Post $post) { return (new CommentResource($this->service->comment($r->user(),$post,$r->validated())))->response()->setStatusCode(201); }
    /**
     * DELETE /api/comments/{comment}
     *
     * Удаление. Доступ: авторизованный; свой объект/admin. Успех 204. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Публикации и реакции
     * @authenticated
     * @response 204
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function destroy(Comment $comment) { Gate::authorize('delete',$comment); $comment->delete(); return response()->noContent(); }
    /**
     * PUT /api/posts/{post}/like
     *
     * Поставить лайк. Доступ: авторизованный; свой объект/admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Публикации и реакции
     * @authenticated
     * @response 200 {"data":{"liked":true,"likes_count":1}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function like(Request $r,Post $post) { return response()->json(['data'=>$this->service->like($r->user(),$post,true)]); }
    /**
     * DELETE /api/posts/{post}/like
     *
     * Снять лайк. Доступ: авторизованный; свой объект/admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Публикации и реакции
     * @authenticated
     * @response 200 {"data":{"liked":false,"likes_count":0}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function unlike(Request $r,Post $post) { return response()->json(['data'=>$this->service->like($r->user(),$post,false)]); }
}
