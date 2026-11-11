<?php
namespace App\Http\Controllers\Api;
use App\Models\Character;
use App\Http\Requests\{CharacterRequest,CatalogRequest};
use App\Http\Resources\CharacterResource;
use App\Services\CharacterService;
use Illuminate\Support\Facades\Gate;
class CharacterController {
    public function __construct(private CharacterService $service) {}
    /**
     * GET /api/characters
     *
     * Список. Доступ: авторизованный; свой объект/admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Персонажи
     * @authenticated
     * @queryParam per_page integer Параметр списка. Example: 20
     * @queryParam page integer Параметр списка. Example: 1
     * @response 200 {"data":[{"id":1,"user_id":3,"maker_id":1,"name":"Мой герой","maker":{"id":1,"user_id":2,"title":"Лунный путешественник","description":"Набор PNG 512×512","status":"approved","creator":{"id":2,"name":"Креатор ICC"},"submitted_at":"2026-11-11T00:00:00Z","reviewed_at":"2026-11-11T00:00:00Z","created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"},"assets":[{"id":1,"maker_id":1,"name":"Основа","category":"body","layer_order":10,"is_active":true,"image_url":"https://api.icc.local/api/assets/1/image","selected_layer_order":0}],"created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"}],"links":{"first":"https://api.icc.local/api/characters?page=1","last":"https://api.icc.local/api/characters?page=1","prev":null,"next":null},"meta":{"current_page":1,"last_page":1,"per_page":20,"total":1}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function index(CatalogRequest $r) { return CharacterResource::collection($this->service->list($r->user(),$r->integer('per_page',20))); }
    /**
     * GET /api/characters/{character}
     *
     * Карточка. Доступ: авторизованный; свой объект/admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Персонажи
     * @authenticated
     * @response 200 {"data":{"id":1,"user_id":3,"maker_id":1,"name":"Мой герой","maker":{"id":1,"user_id":2,"title":"Лунный путешественник","description":"Набор PNG 512×512","status":"approved","creator":{"id":2,"name":"Креатор ICC"},"submitted_at":"2026-11-11T00:00:00Z","reviewed_at":"2026-11-11T00:00:00Z","created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"},"assets":[{"id":1,"maker_id":1,"name":"Основа","category":"body","layer_order":10,"is_active":true,"image_url":"https://api.icc.local/api/assets/1/image","selected_layer_order":0}],"created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function show(Character $character) { Gate::authorize('view',$character); return new CharacterResource($character->load(['maker','assets'])); }
    /**
     * POST /api/characters
     *
     * Создание. Доступ: авторизованный; свой объект/admin. Успех 201. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Персонажи
     * @authenticated
     * @response 201 {"data":{"id":1,"user_id":3,"maker_id":1,"name":"Мой герой","maker":{"id":1,"user_id":2,"title":"Лунный путешественник","description":"Набор PNG 512×512","status":"approved","creator":{"id":2,"name":"Креатор ICC"},"submitted_at":"2026-11-11T00:00:00Z","reviewed_at":"2026-11-11T00:00:00Z","created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"},"assets":[{"id":1,"maker_id":1,"name":"Основа","category":"body","layer_order":10,"is_active":true,"image_url":"https://api.icc.local/api/assets/1/image","selected_layer_order":0}],"created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function store(CharacterRequest $r) { return (new CharacterResource($this->service->save($r->user(),$r->validated())))->response()->setStatusCode(201); }
    /**
     * PUT /api/characters/{character}
     *
     * Изменение. Доступ: авторизованный; свой объект/admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Персонажи
     * @authenticated
     * @response 200 {"data":{"id":1,"user_id":3,"maker_id":1,"name":"Мой герой","maker":{"id":1,"user_id":2,"title":"Лунный путешественник","description":"Набор PNG 512×512","status":"approved","creator":{"id":2,"name":"Креатор ICC"},"submitted_at":"2026-11-11T00:00:00Z","reviewed_at":"2026-11-11T00:00:00Z","created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"},"assets":[{"id":1,"maker_id":1,"name":"Основа","category":"body","layer_order":10,"is_active":true,"image_url":"https://api.icc.local/api/assets/1/image","selected_layer_order":0}],"created_at":"2026-11-11T00:00:00Z","updated_at":"2026-11-11T00:00:00Z"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function update(CharacterRequest $r,Character $character) { Gate::authorize('update',$character); return new CharacterResource($this->service->save($r->user(),$r->validated(),$character)); }
    /**
     * DELETE /api/characters/{character}
     *
     * Удаление. Доступ: авторизованный; свой объект/admin. Успех 204. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Персонажи
     * @authenticated
     * @response 204
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function destroy(Character $character) { Gate::authorize('delete',$character); $this->service->delete($character); return response()->noContent(); }
}
