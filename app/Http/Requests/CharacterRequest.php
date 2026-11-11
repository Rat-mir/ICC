<?php
namespace App\Http\Requests;
class CharacterRequest extends ApiRequest {
    public function rules(): array { return ['name'=>'required|string|max:255','maker_id'=>'required|integer|exists:makers,id','asset_ids'=>'required|array|min:1|max:30','asset_ids.*'=>'required|integer|distinct|exists:assets,id']; }
}
