<?php
namespace App\Http\Requests;
class PostRequest extends ApiRequest {
    public function rules(): array { return ['title'=>'required|string|max:255','character_id'=>'nullable|integer|exists:characters,id','image'=>'required|file|image|mimes:png|mimetypes:image/png|max:5120|dimensions:min_width=16,min_height=16,max_width=2048,max_height=2048']; }
}
