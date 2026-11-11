<?php
namespace App\Http\Controllers\Api;
use App\Models\Maker;
use App\Http\Requests\{CatalogRequest,MakerRequest,ReviewRequest};
use App\Http\Resources\MakerResource;
use App\Services\MakerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
class MakerController {
    public function __construct(private MakerService $service) {}
    /**
     * GET /api/makers
     *
     * Список. Доступ: approved всем; приватные автору/admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Мейкеры и PNG
     * @unauthenticated
     * @queryParam search string Параметр списка. Example: Лунный
     * @queryParam category string Параметр списка. Example: body
     * @queryParam status string Параметр списка. Example: approved
     * @queryParam sort string Параметр списка. Example: title
     * @queryParam order string Параметр списка. Example: asc
     * @queryParam per_page integer Параметр списка. Example: 20
     * @queryParam page integer Параметр списка. Example: 1
     * @response 200 {"data":[{"id":1,"user_id":2,"title":"Лунный путешественник","description":"Набор PNG 512×512","status":"approved","creator":{"id":2,"name":"Креатор ICC"},"submitted_at":"2026-11-11T00:00:00Z","reviewed_at":"2026-11-11T00:00:00Z","created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"}],"links":{"first":"https://api.icc.local/api/makers?page=1","last":"https://api.icc.local/api/makers?page=1","prev":null,"next":null},"meta":{"current_page":1,"last_page":1,"per_page":20,"total":1}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function index(CatalogRequest $r) { return MakerResource::collection($this->service->list($r->validated(),$r->user())); }
    /**
     * GET /api/makers/{maker}
     *
     * Карточка. Доступ: approved всем; приватные автору/admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Мейкеры и PNG
     * @unauthenticated
     * @response 200 {"data":{"id":1,"user_id":2,"title":"Лунный путешественник","description":"Набор PNG 512×512","status":"approved","creator":{"id":2,"name":"Креатор ICC"},"submitted_at":"2026-11-11T00:00:00Z","reviewed_at":"2026-11-11T00:00:00Z","created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function show(Request $r,Maker $maker) { Gate::forUser($r->user())->authorize('view',$maker); return new MakerResource($this->service->detail($maker,$r->user())); }
    /**
     * POST /api/makers
     *
     * Создание. Доступ: creator/admin; владелец. Успех 201. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Мейкеры и PNG
     * @authenticated
     * @response 201 {"data":{"id":1,"user_id":2,"title":"Лунный путешественник","description":"Набор PNG 512×512","status":"approved","creator":{"id":2,"name":"Креатор ICC"},"submitted_at":"2026-11-11T00:00:00Z","reviewed_at":"2026-11-11T00:00:00Z","created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function store(MakerRequest $r) { Gate::authorize('create',Maker::class); return (new MakerResource($this->service->create($r->user(),$r->validated())))->response()->setStatusCode(201); }
    /**
     * PUT /api/makers/{maker}
     *
     * Изменение. Доступ: creator/admin; владелец. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Мейкеры и PNG
     * @authenticated
     * @response 200 {"data":{"id":1,"user_id":2,"title":"Лунный путешественник","description":"Набор PNG 512×512","status":"approved","creator":{"id":2,"name":"Креатор ICC"},"submitted_at":"2026-11-11T00:00:00Z","reviewed_at":"2026-11-11T00:00:00Z","created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function update(MakerRequest $r,Maker $maker) { Gate::authorize('update',$maker); return new MakerResource($this->service->update($maker,$r->validated())); }
    /**
     * DELETE /api/makers/{maker}
     *
     * Удаление. Доступ: creator/admin; владелец. Успех 204. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Мейкеры и PNG
     * @authenticated
     * @response 204
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function destroy(Maker $maker) { Gate::authorize('delete',$maker); $this->service->delete($maker); return response()->noContent(); }
    /**
     * POST /api/makers/{maker}/submit
     *
     * Отправить на модерацию. Доступ: creator/admin; владелец. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Мейкеры и PNG
     * @authenticated
     * @response 200 {"data":{"id":1,"user_id":2,"title":"Лунный путешественник","description":"Набор PNG 512×512","status":"approved","creator":{"id":2,"name":"Креатор ICC"},"submitted_at":"2026-11-11T00:00:00Z","reviewed_at":"2026-11-11T00:00:00Z","created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function submit(Maker $maker) { Gate::authorize('update',$maker); return new MakerResource($this->service->submit($maker)); }
    /**
     * POST /api/admin/makers/{maker}/review
     *
     * Решение администратора. Доступ: admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Администрирование
     * @authenticated
     * @response 200 {"data":{"id":1,"user_id":2,"title":"Лунный путешественник","description":"Набор PNG 512×512","status":"approved","creator":{"id":2,"name":"Креатор ICC"},"submitted_at":"2026-11-11T00:00:00Z","reviewed_at":"2026-11-11T00:00:00Z","created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function review(ReviewRequest $r,Maker $maker) { return new MakerResource($this->service->review($maker,$r->user(),$r->validated())); }
    /**
     * POST /api/admin/makers/{maker}/hide
     *
     * Скрытие. Доступ: admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Администрирование
     * @authenticated
     * @response 200 {"data":{"id":1,"user_id":2,"title":"Лунный путешественник","description":"Набор PNG 512×512","status":"approved","creator":{"id":2,"name":"Креатор ICC"},"submitted_at":"2026-11-11T00:00:00Z","reviewed_at":"2026-11-11T00:00:00Z","created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function hide(Request $r,Maker $maker) { return new MakerResource($this->service->hide($maker,$r->user())); }
    /**
     * GET /api/admin/makers
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
     * @response 200 {"data":[{"id":1,"user_id":2,"title":"Лунный путешественник","description":"Набор PNG 512×512","status":"approved","creator":{"id":2,"name":"Креатор ICC"},"submitted_at":"2026-11-11T00:00:00Z","reviewed_at":"2026-11-11T00:00:00Z","created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"}],"links":{"first":"https://api.icc.local/api/admin/makers?page=1","last":"https://api.icc.local/api/admin/makers?page=1","prev":null,"next":null},"meta":{"current_page":1,"last_page":1,"per_page":20,"total":1}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function moderation(CatalogRequest $r) { return MakerResource::collection($this->service->list($r->validated(),$r->user(),true)); }
    /**
     * GET /api/admin/statistics
     *
     * Агрегированная статистика. Доступ: admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Администрирование
     * @authenticated
     * @response 200 {"data":{"makers_by_status":{"approved":6,"pending":2,"revision":1,"draft":1},"users":10,"posts":10,"characters":10}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function statistics() { return response()->json(['data'=>$this->service->statistics()]); }
}
