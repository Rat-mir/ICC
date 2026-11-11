<?php
namespace App\Http\Requests;
use App\Enums\MakerStatus;
use Illuminate\Validation\Rule;
class CatalogRequest extends ApiRequest {
    public function rules(): array { return [
        'search'=>'nullable|string|max:100','category'=>'nullable|in:body,face,hair,clothes,accessory,background',
        'status'=>['nullable',Rule::enum(MakerStatus::class)],'from'=>'nullable|date','to'=>'nullable|date|after_or_equal:from',
        'sort'=>'nullable|in:id,title,created_at,updated_at','order'=>'nullable|in:asc,desc','page'=>'nullable|integer|min:1',
        'per_page'=>'nullable|integer|between:1,100','mine'=>'nullable|boolean',
    ]; }
}
