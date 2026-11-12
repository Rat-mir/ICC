<?php
namespace App\Http\Resources;
use App\Services\VectorArtwork;
use Illuminate\Http\Resources\Json\JsonResource;
class AssetResource extends JsonResource {
    public function toArray($r): array { return ['id'=>$this->id,'maker_id'=>$this->maker_id,'name'=>$this->name,'category'=>$this->category,'layer_order'=>$this->layer_order,'is_active'=>$this->is_active,'image_url'=>url('/api/assets/'.$this->id.'/image'),'vector'=>VectorArtwork::descriptor($this->file_path),'selected_layer_order'=>$this->whenPivotLoaded('asset_character',fn()=>$this->pivot->layer_order)]; }
}
