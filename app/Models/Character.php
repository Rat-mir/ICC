<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Character extends Model {
    use HasFactory;
    protected $fillable = ['name'];
    public function user() { return $this->belongsTo(User::class); }
    public function maker() { return $this->belongsTo(Maker::class); }
    public function assets() { return $this->belongsToMany(Asset::class)->withPivot('layer_order')->withTimestamps()->orderByPivot('layer_order'); }
    public function posts() { return $this->hasMany(Post::class); }
}
