<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Post extends Model {
    use HasFactory;
    protected $fillable = ['title'];
    protected $attributes = ['is_visible'=>true];
    protected function casts(): array { return ['is_visible'=>'boolean']; }
    public function user() { return $this->belongsTo(User::class); }
    public function character() { return $this->belongsTo(Character::class); }
    public function comments() { return $this->hasMany(Comment::class)->latest(); }
    public function likes() { return $this->hasMany(Like::class); }
}
