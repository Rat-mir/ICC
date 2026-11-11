<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class AuthService {
    public function register(array $data): User { return User::create($data); }
    public function login(array $data): User {
        $user=User::where('email',$data['email'])->first();
        abort_unless($user && Hash::check($data['password'],$user->password),401,'Неверный email или пароль.');
        abort_if($user->is_blocked,403,'Аккаунт заблокирован.');
        return $user;
    }
    public function token(User $user): array { return ['user'=>new \App\Http\Resources\UserResource($user),'token'=>$user->createToken('icc-api')->plainTextToken,'token_type'=>'Bearer','expires_in'=>config('sanctum.expiration')*60]; }
}
