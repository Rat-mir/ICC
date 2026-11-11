<?php
namespace Database\Factories;
use App\Models\{User,Maker};
use Illuminate\Database\Eloquent\Factories\Factory;
class MakerFactory extends Factory {
    protected $model=Maker::class;
    public function definition(): array { return ['user_id'=>User::factory(),'title'=>'Мейкер '.fake()->words(2,true),'description'=>fake()->paragraph(),'status'=>'draft']; }
}
