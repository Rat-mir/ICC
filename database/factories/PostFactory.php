<?php
namespace Database\Factories;
use App\Models\{User,Post};
use Illuminate\Database\Eloquent\Factories\Factory;
class PostFactory extends Factory {
    protected $model=Post::class;
    public function definition(): array { return ['user_id'=>User::factory(),'title'=>fake()->sentence(3),'image_path'=>'demo/post.png','is_visible'=>true]; }
}
