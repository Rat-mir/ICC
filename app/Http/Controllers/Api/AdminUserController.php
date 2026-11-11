<?php
namespace App\Http\Controllers\Api;
use App\Models\User;
use App\Exceptions\BusinessException;
use App\Http\Requests\{UserAdminRequest,CatalogRequest};
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
class AdminUserController {
    public function index(CatalogRequest $r) { return UserResource::collection(User::latest()->paginate($r->integer('per_page',20))->withQueryString()); }
    public function update(UserAdminRequest $r,User $user) {
        if($user->id===$r->user()->id) throw new BusinessException('Нельзя менять собственную роль или блокировать свой аккаунт.');
        DB::transaction(function()use($r,$user) {
            $user=User::lockForUpdate()->findOrFail($user->id);
            foreach($r->validated() as $key=>$value) $user->$key=$value;
            $user->save(); if($user->is_blocked || array_key_exists('role',$r->validated())) $user->tokens()->delete();
        });
        return new UserResource($user->refresh());
    }
}
