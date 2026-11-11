<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Asset extends Model {
    use HasFactory;
    protected $fillable = ['name','category','layer_order','is_active'];
    protected $attributes = ['is_active'=>true,'layer_order'=>0];
    protected function casts(): array { return ['layer_order'=>'integer','is_active'=>'boolean']; }
    public function maker() { return $this->belongsTo(Maker::class); }
    public function characters() { return $this->belongsToMany(Character::class)->withPivot('layer_order')->withTimestamps(); }
}
