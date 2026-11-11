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
    public function comment(CommentRequest $r,Post $post) { return (new CommentResource($this->service->comment($r->user(),$post,$r->validated())))->response()->setStatusCode(201); }
    public function destroy(Comment $comment) { Gate::authorize('delete',$comment); $comment->delete(); return response()->noContent(); }
    public function like(Request $r,Post $post) { return response()->json(['data'=>$this->service->like($r->user(),$post,true)]); }
    public function unlike(Request $r,Post $post) { return response()->json(['data'=>$this->service->like($r->user(),$post,false)]); }
}
