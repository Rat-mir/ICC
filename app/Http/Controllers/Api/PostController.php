<?php
namespace App\Http\Controllers\Api;
use App\Models\Post;
use App\Http\Requests\{CatalogRequest,PostRequest};
use App\Http\Resources\PostResource;
use App\Services\{PostService,ImageService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
class PostController {
    public function __construct(private PostService $service,private ImageService $images) {}
    /**
     * GET /api/posts
     *
     * Список. Доступ: видимые всем; скрытые автору/admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Публикации и реакции
     * @unauthenticated
     * @queryParam search string Параметр списка. Example: Лунный
     * @queryParam category string Параметр списка. Example: body
     * @queryParam status string Параметр списка. Example: approved
     * @queryParam sort string Параметр списка. Example: title
     * @queryParam order string Параметр списка. Example: asc
     * @queryParam per_page integer Параметр списка. Example: 20
     * @queryParam page integer Параметр списка. Example: 1
     * @response 200 {"data":[{"id":1,"user_id":3,"character_id":1,"title":"Моя работа","is_visible":true,"image_url":"https://api.icc.local/api/posts/1/image","author":{"id":3,"name":"Пользователь ICC"},"likes_count":1,"comments_count":1,"comments":[{"id":1,"user_id":3,"post_id":1,"body":"Отличный образ!","author":{"id":3,"name":"Пользователь ICC"},"created_at":"2026-11-11T00:00:00Z"}],"created_at":"2026-11-11T00:00:00Z"}],"links":{"first":"https://api.icc.local/api/posts?page=1","last":"https://api.icc.local/api/posts?page=1","prev":null,"next":null},"meta":{"current_page":1,"last_page":1,"per_page":20,"total":1}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function index(CatalogRequest $r) { return PostResource::collection($this->service->list($r->validated(),$r->user())); }
    /**
     * GET /api/posts/{post}
     *
     * Карточка. Доступ: видимые всем; скрытые автору/admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Публикации и реакции
     * @unauthenticated
     * @response 200 {"data":{"id":1,"user_id":3,"character_id":1,"title":"Моя работа","is_visible":true,"image_url":"https://api.icc.local/api/posts/1/image","author":{"id":3,"name":"Пользователь ICC"},"likes_count":1,"comments_count":1,"comments":[{"id":1,"user_id":3,"post_id":1,"body":"Отличный образ!","author":{"id":3,"name":"Пользователь ICC"},"created_at":"2026-11-11T00:00:00Z"}],"created_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function show(Request $r,Post $post) { return new PostResource($this->service->detail($post,$r->user())); }
    /**
     * POST /api/posts
     *
     * Создание. Доступ: авторизованный; свой объект/admin. Успех 201. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Публикации и реакции
     * @authenticated
     * @response 201 {"data":{"id":1,"user_id":3,"character_id":1,"title":"Моя работа","is_visible":true,"image_url":"https://api.icc.local/api/posts/1/image","author":{"id":3,"name":"Пользователь ICC"},"likes_count":1,"comments_count":1,"comments":[{"id":1,"user_id":3,"post_id":1,"body":"Отличный образ!","author":{"id":3,"name":"Пользователь ICC"},"created_at":"2026-11-11T00:00:00Z"}],"created_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function store(PostRequest $r) { return (new PostResource($this->service->create($r->user(),$r->validated())))->response()->setStatusCode(201); }
    /**
     * DELETE /api/posts/{post}
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
    public function destroy(Post $post) { Gate::authorize('delete',$post); $this->service->delete($post); return response()->noContent(); }
    /**
     * GET /api/posts/{post}/image
     *
     * PNG с проверкой доступа. Доступ: видимые всем; скрытые автору/admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Публикации и реакции
     * @unauthenticated
     * @response 200
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function image(Request $r,Post $post) { $this->service->visible($post,$r->user()); return $this->images->response($post->image_path); }
    /**
     * POST /api/admin/posts/{post}/hide
     *
     * Скрытие. Доступ: admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Администрирование
     * @authenticated
     * @response 200 {"data":{"id":1,"user_id":3,"character_id":1,"title":"Моя работа","is_visible":true,"image_url":"https://api.icc.local/api/posts/1/image","author":{"id":3,"name":"Пользователь ICC"},"likes_count":1,"comments_count":1,"comments":[{"id":1,"user_id":3,"post_id":1,"body":"Отличный образ!","author":{"id":3,"name":"Пользователь ICC"},"created_at":"2026-11-11T00:00:00Z"}],"created_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function hide(Post $post) { return new PostResource($this->service->hide($post)); }
    /**
     * GET /api/admin/posts
     *
     * Список для модерации. Доступ: admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Администрирование
     * @authenticated
     * @queryParam search string Параметр списка. Example: Лунный
     * @queryParam category string Параметр списка. Example: body
     * @queryParam status string Параметр списка. Example: approved
     * @queryParam sort string Параметр списка. Example: title
     * @queryParam order string Параметр списка. Example: asc
     * @queryParam per_page integer Параметр списка. Example: 20
     * @queryParam page integer Параметр списка. Example: 1
     * @response 200 {"data":[{"id":1,"user_id":3,"character_id":1,"title":"Моя работа","is_visible":true,"image_url":"https://api.icc.local/api/posts/1/image","author":{"id":3,"name":"Пользователь ICC"},"likes_count":1,"comments_count":1,"comments":[{"id":1,"user_id":3,"post_id":1,"body":"Отличный образ!","author":{"id":3,"name":"Пользователь ICC"},"created_at":"2026-11-11T00:00:00Z"}],"created_at":"2026-11-11T00:00:00Z"}],"links":{"first":"https://api.icc.local/api/admin/posts?page=1","last":"https://api.icc.local/api/admin/posts?page=1","prev":null,"next":null},"meta":{"current_page":1,"last_page":1,"per_page":20,"total":1}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function moderation(CatalogRequest $r) { return PostResource::collection($this->service->list($r->validated(),$r->user(),true)); }
}
