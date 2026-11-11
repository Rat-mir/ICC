<?php
namespace Database\Seeders;
use App\Models\{User,Maker,Asset,Character,Post,Comment,Like};
use App\Enums\{UserRole,MakerStatus};
use App\Services\DemoArtwork;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        $admin=User::factory()->create(['name'=>'Администратор ICC','email'=>'admin@icc.test','password'=>'DemoPass123!','role'=>UserRole::Admin]);
        $creator=User::factory()->create(['name'=>'Креатор ICC','email'=>'creator@icc.test','password'=>'DemoPass123!','role'=>UserRole::Creator]);
        $user=User::factory()->create(['name'=>'Пользователь ICC','email'=>'user@icc.test','password'=>'DemoPass123!','role'=>UserRole::User]);
        $others=User::factory()->count(7)->create();
        $art=app(DemoArtwork::class); $approved=[];
        $names=['Лунный путешественник','Лесная мастерская','Сахарные облака','Золотой час','Морской ветер','Тихий сад','Новый образ','Городской герой','Звёздная сказка','Мой первый мейкер'];
        $categories=['background','body','clothes','face','hair','accessory'];
        foreach($names as $i=>$name) {
            $status=$i<6?MakerStatus::Approved:($i<8?MakerStatus::Pending:($i===8?MakerStatus::Revision:MakerStatus::Draft));
            $maker=Maker::factory()->create(['user_id'=>$creator->id,'title'=>$name,'description'=>'Соберите собственного героя: выберите слои, настройте их порядок и сохраните образ. Авторские демонстрационные PNG 512×512.','status'=>$status,
                'submitted_at'=>$i<9?now():null,'reviewed_by'=>$i<6||$i===8?$admin->id:null,'reviewed_at'=>$i<6||$i===8?now():null,'review_comment'=>$i===8?'Добавьте альтернативный вариант причёски.':null]);
            $paths=[];
            foreach($categories as $order=>$category) {
                $path=$art->layer($category,$i); $paths[]=$path;
                Asset::factory()->create(['maker_id'=>$maker->id,'name'=>match($category){'body'=>'Основа','face'=>'Лицо','hair'=>'Причёска','clothes'=>'Одежда','background'=>'Фон','accessory'=>'Очки'},'category'=>$category,'layer_order'=>$order*10,'file_path'=>$path]);
            }
            if($i<6) $approved[]=[$maker,$paths];
        }
        for($i=0;$i<10;$i++) {
            [$maker,$paths]=$approved[$i%6]; $owner=$i<5?$user:$others[$i-5];
            $character=Character::factory()->create(['user_id'=>$owner->id,'maker_id'=>$maker->id,'name'=>'Герой '.($i+1)]);
            $pivot=[]; foreach($maker->assets as $order=>$asset) $pivot[$asset->id]=['layer_order'=>$order];
            $character->assets()->sync($pivot);
            $post=Post::factory()->create(['user_id'=>$owner->id,'character_id'=>$character->id,'title'=>'Мой герой: '.$maker->title,'image_path'=>$art->composite($paths,'post-'.$i)]);
            Comment::factory()->create(['user_id'=>$creator->id,'post_id'=>$post->id,'body'=>'Спасибо за работу! Образ отлично получился.']);
            Like::factory()->create(['user_id'=>$creator->id,'post_id'=>$post->id]);
        }
    }
}
