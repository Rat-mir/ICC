<?php
namespace App\Http\Controllers\Api;
use App\Http\Requests\{RegisterRequest,LoginRequest};
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
class AuthController {
    public function __construct(private AuthService $service) {}
    public function register(RegisterRequest $r) { return response()->json($this->service->token($this->service->register($r->validated())),201); }
    public function login(LoginRequest $r) { return response()->json($this->service->token($this->service->login($r->validated()))); }
    public function user(Request $r) { return new UserResource($r->user()); }
    public function logout(Request $r) { $r->user()->currentAccessToken()?->delete(); return response()->noContent(); }
}
