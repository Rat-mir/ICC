<?php
namespace App\Http\Controllers\Api;
use App\Models\{Maker,Asset};
use App\Http\Requests\AssetRequest;
use App\Http\Resources\AssetResource;
use App\Services\{MakerService,ImageService,DiceBearArtwork};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
class AssetController {
    public function __construct(private MakerService $service,private ImageService $images) {}
    /**
     * POST /api/makers/{maker}/assets
     *
     * Создание. Доступ: creator/admin; владелец. Успех 201. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Мейкеры и PNG
     * @authenticated
     * @response 201 {"data":{"id":1,"maker_id":1,"name":"Основа","category":"body","layer_order":10,"is_active":true,"image_url":"https://api.icc.local/api/assets/1/image"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function store(AssetRequest $r,Maker $maker) { Gate::authorize('update',$maker); return (new AssetResource($this->service->asset($maker,$r->validated())))->response()->setStatusCode(201); }
    /**
     * PUT /api/makers/{maker}/assets/{asset}
     *
     * Изменение. Доступ: creator/admin; владелец. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Мейкеры и PNG
     * @authenticated
     * @response 200 {"data":{"id":1,"maker_id":1,"name":"Основа","category":"body","layer_order":10,"is_active":true,"image_url":"https://api.icc.local/api/assets/1/image"}}
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function update(AssetRequest $r,Maker $maker,Asset $asset) { abort_unless($asset->maker_id===$maker->id,404); Gate::authorize('update',$maker); return new AssetResource($this->service->asset($maker,$r->validated(),$asset)); }
    /**
     * DELETE /api/makers/{maker}/assets/{asset}
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
    public function destroy(Maker $maker,Asset $asset) { abort_unless($asset->maker_id===$maker->id,404); Gate::authorize('update',$maker); $this->service->deleteAsset($maker,$asset); return response()->noContent(); }
    /**
     * GET /api/assets/{asset}/image
     *
     * PNG с проверкой доступа. Доступ: approved всем; приватные автору/admin. Успех 200. Ошибки 401,403,404,409,422,429,500. message для ошибок, errors для 422.
     * @group Мейкеры и PNG
     * @unauthenticated
     * @response 200
     * @response 401 {"message":"Необходим токен доступа."}
     * @response 403 {"message":"Недостаточно прав."}
     * @response 404 {"message":"Запись не найдена."}
     * @response 409 {"message":"Недопустимый переход статуса."}
     * @response 422 {"message":"Ошибка валидации","errors":{"title":["Поле обязательно."]}}
     */
    public function image(Request $r, Asset $asset) {
        Gate::forUser($r->user())->authorize('view', $asset->maker);
        if (!$asset->is_active) {
            abort_unless($r->user() && ($r->user()->isAdmin() || $r->user()->id === $asset->maker->user_id), 404);
        }
        $parts = null;
        if (DiceBearArtwork::supports($asset->maker->vector_style) && $r->has('parts')) {
            $raw = $r->query('parts');
            abort_unless(is_string($raw) && preg_match('/^\d{1,18}(,\d{1,18}){0,29}$/D', $raw), 422);
            $ids = explode(',', $raw);
            $parts = $asset->maker->assets()->where('is_active', true)->whereKey($ids)->get()
                ->map(fn (Asset $part) => DiceBearArtwork::descriptor($part->file_path))->all();
            abort_if(count($parts) !== count($ids) || in_array(null, $parts, true)
                || count(array_unique(array_column($parts, 'category'))) !== count($parts), 422);
        }
        return $this->images->response($asset->file_path, $r->query('plane') === 'back', $parts);
    }
}
