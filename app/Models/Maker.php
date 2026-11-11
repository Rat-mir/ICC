<?php
namespace App\Models;
use App\Enums\MakerStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Maker extends Model {
    use HasFactory;
    protected $fillable = ['title','description'];
    protected $attributes = ['status'=>'draft'];
    protected function casts(): array { return ['status'=>MakerStatus::class,'submitted_at'=>'datetime','reviewed_at'=>'datetime']; }
    public function creator() { return $this->belongsTo(User::class,'user_id'); }
    public function reviewer() { return $this->belongsTo(User::class,'reviewed_by'); }
    public function assets() { return $this->hasMany(Asset::class)->orderBy('layer_order')->orderBy('id'); }
    public function characters() { return $this->hasMany(Character::class); }
    public function scopeSearch($q, ?string $text) { return $q->when($text,fn($q)=>$q->where('title','like','%'.$text.'%')); }
}
