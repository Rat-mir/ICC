<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\{DemoArtwork, DiceBearArtwork};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VectorMakerSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $email = User::BUILTIN_MAKER_EMAIL;
            $owner = DB::table('users')->where('email', $email)->value('id');
            $owner ??= DB::table('users')->insertGetId([
                'name' => 'ICC', 'email' => $email, 'password' => Hash::make(Str::random(64)),
                'is_blocked' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('users')->where('id', $owner)->lockForUpdate()->first();
            DB::table('posts')->whereIn('image_path', DemoArtwork::postPaths())
                ->where('is_visible', true)->update(['is_visible' => false]);
            $styles = DiceBearArtwork::STYLES;
            foreach (DiceBearArtwork::REVIEW_STYLES as $style) {
                $title = ucwords(str_replace('-', ' ', $style));
                $styles[$style] = [$title, 'Редактор персонажей на основе набора DiceBear.'];
            }
            foreach ($styles as $style => [$title, $description]) {
                if (in_array($style, DiceBearArtwork::ARCHIVED_STYLES, true)) {
                    continue;
                }
                $maker = DB::table('makers')->where('vector_style', $style)->value('id');
                if ($maker === null) {
                    $pending = in_array($style, DiceBearArtwork::REVIEW_STYLES, true);
                    $maker = DB::table('makers')->insertGetId([
                        'user_id' => $owner, 'vector_style' => $style, 'title' => $title,
                        'description' => $description, 'status' => $pending ? 'pending' : 'approved',
                        'submitted_at' => $pending ? now() : null,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                } else {
                    DB::table('makers')->where('id', $maker)->update(['title' => $title, 'description' => $description]);
                }
                $previous = DB::table('assets')->where('maker_id', $maker)->get()->keyBy('file_path');
                $assets = [];
                $options = DiceBearArtwork::options($style);
                foreach ($options as $category => [$names, $colors]) {
                    $order = array_search($category, array_keys($options), true) * 10;
                    foreach ($names as $variant => $name) {
                        foreach (array_keys($colors) as $shade) {
                            $path = "dicebear/$style/$category/$variant/$shade";
                            if ($asset = $previous->get($path)) {
                                if ($asset->name !== $name) {
                                    DB::table('assets')->where('id', $asset->id)->update(['name' => $name]);
                                }
                                continue;
                            }
                            $assets[] = [
                                'maker_id' => $maker, 'name' => $name, 'category' => $category,
                                'file_path' => $path, 'layer_order' => $order,
                                'created_at' => now(), 'updated_at' => now(),
                            ];
                        }
                    }
                }
                foreach (array_chunk($assets, 300) as $chunk) {
                    DB::table('assets')->insert($chunk);
                }
            }
        });
    }
}
