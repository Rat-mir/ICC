<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class AssetResource extends JsonResource {
    public function toArray($r): array { return ['id'=>$this->id,'maker_id'=>$this->maker_id,'name'=>$this->name,'category'=>$this->category,'layer_order'=>$this->layer_order,'is_active'=>$this->is_active,'image_url'=>url('/api/assets/'.$this->id.'/image'),'selected_layer_order'=>$this->whenPivotLoaded('asset_character',fn()=>$this->pivot->layer_order)]; }
}
