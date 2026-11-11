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
    public function index(CatalogRequest $r) { return PostResource::collection($this->service->list($r->validated(),$r->user())); }
    public function show(Request $r,Post $post) { return new PostResource($this->service->detail($post,$r->user())); }
    public function store(PostRequest $r) { return (new PostResource($this->service->create($r->user(),$r->validated())))->response()->setStatusCode(201); }
    public function destroy(Post $post) { Gate::authorize('delete',$post); $this->service->delete($post); return response()->noContent(); }
    public function image(Request $r,Post $post) { $this->service->visible($post,$r->user()); return $this->images->response($post->image_path); }
    public function hide(Post $post) { return new PostResource($this->service->hide($post)); }
    public function moderation(CatalogRequest $r) { return PostResource::collection($this->service->list($r->validated(),$r->user(),true)); }
}
