<?php
namespace Database\Factories;
use App\Models\{User,Post,Comment};
use Illuminate\Database\Eloquent\Factories\Factory;
class CommentFactory extends Factory {
    protected $model=Comment::class;
    public function definition(): array { return ['user_id'=>User::factory(),'post_id'=>Post::factory(),'body'=>fake()->sentence()]; }
}
