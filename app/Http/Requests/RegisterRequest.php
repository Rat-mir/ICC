<?php
namespace App\Http\Requests;
class RegisterRequest extends ApiRequest {
    public function rules(): array { return ['name'=>'required|string|max:100','email'=>'required|email|max:255|unique:users,email','password'=>'required|string|min:8|max:128|confirmed','role'=>'prohibited','is_blocked'=>'prohibited']; }
}
