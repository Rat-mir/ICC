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
    public function index(CatalogRequest $r) { return MakerResource::collection($this->service->list($r->validated(),$r->user())); }
    public function show(Request $r,Maker $maker) { Gate::forUser($r->user())->authorize('view',$maker); return new MakerResource($this->service->detail($maker,$r->user())); }
    public function store(MakerRequest $r) { Gate::authorize('create',Maker::class); return (new MakerResource($this->service->create($r->user(),$r->validated())))->response()->setStatusCode(201); }
    public function update(MakerRequest $r,Maker $maker) { Gate::authorize('update',$maker); return new MakerResource($this->service->update($maker,$r->validated())); }
    public function destroy(Maker $maker) { Gate::authorize('delete',$maker); $this->service->delete($maker); return response()->noContent(); }
    public function submit(Maker $maker) { Gate::authorize('update',$maker); return new MakerResource($this->service->submit($maker)); }
    public function review(ReviewRequest $r,Maker $maker) { return new MakerResource($this->service->review($maker,$r->user(),$r->validated())); }
    public function hide(Request $r,Maker $maker) { return new MakerResource($this->service->hide($maker,$r->user())); }
    public function moderation(CatalogRequest $r) { return MakerResource::collection($this->service->list($r->validated(),$r->user(),true)); }
    public function statistics() { return response()->json(['data'=>$this->service->statistics()]); }
}
