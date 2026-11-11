<?php
namespace App\Http\Requests;
class AssetRequest extends ApiRequest {
    public function rules(): array { $r=$this->isMethod('post')?'required':'sometimes'; return [
        'name'=>"$r|string|max:255",'category'=>"$r|in:body,face,hair,clothes,accessory,background",
        'layer_order'=>"$r|integer|between:0,1000",'is_active'=>'sometimes|boolean',
        'image'=>"$r|file|image|mimes:png|mimetypes:image/png|max:5120|dimensions:min_width=16,min_height=16,max_width=2048,max_height=2048",
    ]; }
}
