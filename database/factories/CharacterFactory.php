<?php
namespace Database\Factories;
use App\Models\{User,Maker,Character};
use Illuminate\Database\Eloquent\Factories\Factory;
class CharacterFactory extends Factory {
    protected $model=Character::class;
    public function definition(): array { return ['user_id'=>User::factory(),'maker_id'=>Maker::factory(),'name'=>fake()->firstName()]; }
}
