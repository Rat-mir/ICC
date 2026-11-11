<?php
namespace App\Http\Requests;
class ReviewRequest extends ApiRequest {
    public function rules(): array { return ['decision'=>'required|in:approve,reject,request-revision','review_comment'=>'required_unless:decision,approve|nullable|string|max:2000']; }
    public function messages(): array { return parent::messages()+['required_unless'=>'Укажите причину отказа или возврата на доработку.']; }
}
