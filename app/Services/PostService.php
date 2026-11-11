<?php
namespace App\Services;
use App\Models\{User,Post,Character};
use Illuminate\Support\Facades\{DB,Gate,Storage};
class PostService {
    public function __construct(private ImageService $images) {}
    public function list(array $f,?User $user,bool $admin=false) {
        return Post::with('user')->withCount(['likes','comments'])
            ->when(!$admin,fn($q)=>$q->where('is_visible',true))
            ->when(!empty($f['mine']) && $user,fn($q)=>$q->where('user_id',$user->id))
            ->when($f['search']??null,fn($q,$s)=>$q->where('title','like','%'.$s.'%'))
            ->orderBy($f['sort']??'created_at',$f['order']??'desc')->orderBy('id')
            ->paginate($f['per_page']??20)->withQueryString();
    }
    public function visible(Post $post,?User $user): void { abort_unless($post->is_visible || ($user && ($user->isAdmin() || $user->id===$post->user_id)),404); }
    public function detail(Post $post,?User $user): Post { $this->visible($post,$user); return $post->load(['user','comments.user'])->loadCount(['likes','comments']); }
    public function create(User $user,array $data): Post {
        if(!empty($data['character_id'])) Gate::forUser($user)->authorize('view',Character::findOrFail($data['character_id']));
        $path=$this->images->store($data['image'],'posts');
        try {
            $post=new Post(['title'=>$data['title']]); $post->user()->associate($user); $post->character_id=$data['character_id']??null;
            $post->image_path=$path; $post->save(); return $post->load('user')->loadCount(['likes','comments']);
        } catch(\Throwable $e) { Storage::disk('local')->delete($path); throw $e; }
    }
    public function delete(Post $post): void { $path=$post->image_path; $post->delete(); Storage::disk('local')->delete($path); }
    public function hide(Post $post): Post { $post->is_visible=false; $post->save(); return $post; }
    public function comment(User $user,Post $post,array $data) { abort_unless($post->is_visible,404); $comment=$post->comments()->make($data); $comment->user()->associate($user); $comment->save(); return $comment->load('user'); }
    public function like(User $user,Post $post,bool $add): array {
        return DB::transaction(function()use($user,$post,$add) {
            $post=Post::lockForUpdate()->findOrFail($post->id); abort_unless($post->is_visible,404);
            if($add) { $like=$post->likes()->where('user_id',$user->id)->first() ?? $post->likes()->make(); $like->user_id=$user->id; $like->save(); }
            else $post->likes()->where('user_id',$user->id)->delete();
            return ['liked'=>$add,'likes_count'=>$post->likes()->count()];
        });
    }
}
