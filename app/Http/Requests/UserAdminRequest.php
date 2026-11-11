<?php
namespace App\Http\Requests;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;
class UserAdminRequest extends ApiRequest { public function rules(): array { return ['role'=>['sometimes',Rule::enum(UserRole::class)],'is_blocked'=>'sometimes|boolean']; } }
