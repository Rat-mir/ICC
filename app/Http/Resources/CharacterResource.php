<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class CharacterResource extends JsonResource {
    public function toArray($r): array { return ['id'=>$this->id,'user_id'=>$this->user_id,'maker_id'=>$this->maker_id,'name'=>$this->name,'maker'=>new MakerResource($this->whenLoaded('maker')),'assets'=>AssetResource::collection($this->whenLoaded('assets')),'created_at'=>$this->created_at,'updated_at'=>$this->updated_at]; }
}
