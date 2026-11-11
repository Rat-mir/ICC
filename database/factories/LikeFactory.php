<?php
namespace Database\Factories;
use App\Models\{User,Post,Like};
use Illuminate\Database\Eloquent\Factories\Factory;
class LikeFactory extends Factory {
    protected $model=Like::class;
    public function definition(): array { return ['user_id'=>User::factory(),'post_id'=>Post::factory()]; }
}
