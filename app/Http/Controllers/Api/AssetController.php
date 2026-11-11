<?php
namespace App\Http\Controllers\Api;
use App\Models\{Maker,Asset};
use App\Http\Requests\AssetRequest;
use App\Http\Resources\AssetResource;
use App\Services\{MakerService,ImageService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
class AssetController {
    public function __construct(private MakerService $service,private ImageService $images) {}
    public function store(AssetRequest $r,Maker $maker) { Gate::authorize('update',$maker); return (new AssetResource($this->service->asset($maker,$r->validated())))->response()->setStatusCode(201); }
    public function update(AssetRequest $r,Maker $maker,Asset $asset) { abort_unless($asset->maker_id===$maker->id,404); Gate::authorize('update',$maker); return new AssetResource($this->service->asset($maker,$r->validated(),$asset)); }
    public function destroy(Maker $maker,Asset $asset) { abort_unless($asset->maker_id===$maker->id,404); Gate::authorize('update',$maker); $this->service->deleteAsset($maker,$asset); return response()->noContent(); }
    public function image(Request $r,Asset $asset) { Gate::forUser($r->user())->authorize('view',$asset->maker); if(!$asset->is_active) abort_unless($r->user() && ($r->user()->isAdmin() || $r->user()->id===$asset->maker->user_id),404); return $this->images->response($asset->file_path); }
}
