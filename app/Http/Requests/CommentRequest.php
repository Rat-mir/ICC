<?php
namespace App\Http\Requests;
class CommentRequest extends ApiRequest { public function rules(): array { return ['body'=>'required|string|max:2000']; } }
