<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class PostResource extends JsonResource {
    public function toArray($r): array { return ['id'=>$this->id,'user_id'=>$this->user_id,'character_id'=>$this->character_id,'title'=>$this->title,'body'=>$this->body,'liked'=>$this->whenHas('liked'),'is_visible'=>$this->is_visible,'image_url'=>url('/api/posts/'.$this->id.'/image'),'author'=>$this->whenLoaded('user',fn()=>['id'=>$this->user->id,'name'=>$this->user->name,'is_creator'=>$this->user->creator_since !== null]),'likes_count'=>$this->whenCounted('likes'),'comments_count'=>$this->whenCounted('comments'),'comments'=>CommentResource::collection($this->whenLoaded('comments')),'created_at'=>$this->created_at]; }
}
