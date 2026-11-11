<?php
namespace App\Http\Requests;
class MakerRequest extends ApiRequest {
    public function rules(): array { $r=$this->isMethod('post')?'required':'sometimes'; return ['title'=>"$r|string|max:255",'description'=>"$r|string|max:5000",'status'=>'prohibited','user_id'=>'prohibited','reviewed_by'=>'prohibited']; }
}
