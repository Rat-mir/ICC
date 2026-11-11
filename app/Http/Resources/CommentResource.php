<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class CommentResource extends JsonResource {
    public function toArray($r): array { return ['id'=>$this->id,'user_id'=>$this->user_id,'post_id'=>$this->post_id,'body'=>$this->body,'author'=>$this->whenLoaded('user',fn()=>['id'=>$this->user->id,'name'=>$this->user->name]),'created_at'=>$this->created_at]; }
}
