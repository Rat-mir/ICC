<?php
namespace App\Models;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected $attributes = ['role'=>'user','is_blocked'=>false];
    protected function casts(): array { return ['password'=>'hashed','role'=>UserRole::class,'is_blocked'=>'boolean','email_verified_at'=>'datetime']; }
    public function makers() { return $this->hasMany(Maker::class); }
    public function reviewedMakers() { return $this->hasMany(Maker::class, 'reviewed_by'); }
    public function characters() { return $this->hasMany(Character::class); }
    public function posts() { return $this->hasMany(Post::class); }
    public function comments() { return $this->hasMany(Comment::class); }
    public function likes() { return $this->hasMany(Like::class); }
    public function isAdmin(): bool { return $this->role === UserRole::Admin; }
}
