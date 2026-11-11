<?php
namespace Database\Factories;
use App\Models\{Asset,Maker};
use Illuminate\Database\Eloquent\Factories\Factory;
class AssetFactory extends Factory {
    protected $model=Asset::class;
    public function definition(): array { return ['maker_id'=>Maker::factory(),'name'=>'Слой '.fake()->word(),'category'=>'body','layer_order'=>10,'file_path'=>'demo/body.png','is_active'=>true]; }
}
