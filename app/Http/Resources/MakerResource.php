<?php
namespace App\Http\Resources;
use App\Services\DiceBearArtwork;
use Illuminate\Http\Resources\Json\JsonResource;
class MakerResource extends JsonResource {
    public function toArray($r): array { return [
        'id'=>$this->id,'user_id'=>$this->user_id,'title'=>$this->title,'description'=>$this->description,'status'=>$this->status->value,
        'vector_style'=>$this->vector_style,
        'dicebear_meta'=>$this->when(
            $this->relationLoaded('assets') && DiceBearArtwork::supports($this->vector_style),
            fn () => DiceBearArtwork::metadata($this->vector_style)
        ),
        'creator'=>$this->whenLoaded('creator',fn()=>['id'=>$this->creator->id,'name'=>$this->creator->name,'is_creator'=>$this->creator->creator_since !== null]),
        'review_comment'=>$this->when($r->user() && ($r->user()->isAdmin() || $r->user()->id===$this->user_id),$this->review_comment),
        'reviewed_by'=>$this->when($r->user()?->isAdmin() || $r->user()?->id===$this->user_id,$this->reviewed_by),
        'submitted_at'=>$this->submitted_at,'reviewed_at'=>$this->reviewed_at,
        'assets'=>AssetResource::collection($this->whenLoaded('assets')),
        'assets_count'=>$this->whenCounted('assets'),'characters_count'=>$this->whenCounted('characters'),
        'created_at'=>$this->created_at,'updated_at'=>$this->updated_at,
    ]; }
}
