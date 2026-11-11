<?php
namespace App\Http\Controllers\Api;
use App\Models\Character;
use App\Http\Requests\{CharacterRequest,CatalogRequest};
use App\Http\Resources\CharacterResource;
use App\Services\CharacterService;
use Illuminate\Support\Facades\Gate;
class CharacterController {
    public function __construct(private CharacterService $service) {}
    public function index(CatalogRequest $r) { return CharacterResource::collection($this->service->list($r->user(),$r->integer('per_page',20))); }
    public function show(Character $character) { Gate::authorize('view',$character); return new CharacterResource($character->load(['maker','assets'])); }
    public function store(CharacterRequest $r) { return (new CharacterResource($this->service->save($r->user(),$r->validated())))->response()->setStatusCode(201); }
    public function update(CharacterRequest $r,Character $character) { Gate::authorize('update',$character); return new CharacterResource($this->service->save($r->user(),$r->validated(),$character)); }
    public function destroy(Character $character) { Gate::authorize('delete',$character); $this->service->delete($character); return response()->noContent(); }
}
