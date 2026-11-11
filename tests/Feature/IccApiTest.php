<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{User,Maker,Asset,Character,Post,Comment,Like};
use App\Enums\{UserRole,MakerStatus};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Storage,Hash,DB};
use Laravel\Sanctum\Sanctum;
class IccApiTest extends TestCase {
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); Storage::fake('local'); }
    private function creator(): User { return User::factory()->create(['role'=>UserRole::Creator]); }
    private function admin(): User { return User::factory()->create(['role'=>UserRole::Admin]); }
    private function maker(?User $owner=null,string $status='draft'): Maker { return Maker::factory()->create(['user_id'=>($owner??$this->creator())->id,'status'=>$status]); }
    private function layer(Maker $maker,string $category='body',int $size=64): Asset {
        $path=UploadedFile::fake()->image($category.'.png',$size,$size)->store('assets','local');
        return Asset::factory()->create(['maker_id'=>$maker->id,'category'=>$category,'file_path'=>$path]);
    }
    private function complete(Maker $maker): array { return [$this->layer($maker),$this->layer($maker,'face')]; }
    public function test_health_checks_database(): void { $this->getJson('/api/health')->assertOk()->assertJsonPath('database','ok'); }
    public function test_registration_hashes_password_and_cannot_assign_role(): void {
        $data=['name'=>'Тест','email'=>'new@icc.test','password'=>'Password123!','password_confirmation'=>'Password123!'];
        $this->postJson('/api/register',$data+['role'=>'admin'])->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->postJson('/api/register',$data)->assertCreated()->assertJsonPath('user.role','user')->assertJsonStructure(['token','user'=>['id','email']])->assertJsonMissingPath('user.password');
        $this->assertTrue(Hash::check($data['password'],User::where('email',$data['email'])->first()->password));
    }
    public function test_registration_rejects_duplicate_email_and_bad_confirmation(): void {
        $user=User::factory()->create();
        $this->postJson('/api/register',['name'=>'Тест','email'=>$user->email,'password'=>'Password123!','password_confirmation'=>'wrong'])->assertUnprocessable()->assertJsonValidationErrors(['email','password']);
    }
    public function test_login_token_user_and_logout_revocation(): void {
        $user=User::factory()->create(['password'=>'Password123!']);
        $token=$this->postJson('/api/login',['email'=>$user->email,'password'=>'Password123!'])->assertOk()->json('token');
        $this->withToken($token)->getJson('/api/user')->assertOk()->assertJsonPath('data.id',$user->id);
        $this->withToken($token)->postJson('/api/logout')->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens',0);
        // Flush the guard between requests to emulate a separate HTTP request.
        app('auth')->forgetGuards();
        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
    }
    public function test_invalid_password_and_missing_token_are_401(): void {
        $user=User::factory()->create();
        $this->postJson('/api/login',['email'=>$user->email,'password'=>'wrong'])->assertUnauthorized();
        $this->getJson('/api/characters')->assertUnauthorized();
        $this->withToken('invalid')->getJson('/api/user')->assertUnauthorized();
    }
    public function test_blocked_user_cannot_login_or_use_protected_api(): void {
        $user=User::factory()->create(['password'=>'Password123!','is_blocked'=>true]);
        $this->postJson('/api/login',['email'=>$user->email,'password'=>'Password123!'])->assertForbidden();
        Sanctum::actingAs($user); $this->getJson('/api/characters')->assertForbidden();
    }
    public function test_guest_catalog_excludes_private_statuses(): void {
        $this->maker(status:'approved'); $this->maker(status:'pending'); $this->maker(status:'hidden');
        $this->getJson('/api/makers')->assertOk()->assertJsonCount(1,'data')->assertJsonPath('data.0.status','approved')->assertJsonPath('meta.total',1);
    }
    public function test_maker_creation_requires_creator_role(): void {
        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/makers',['title'=>'Новый','description'=>'Описание'])->assertForbidden();
        Sanctum::actingAs($owner=$this->creator());
        $id=$this->postJson('/api/makers',['title'=>'Новый','description'=>'Описание'])->assertCreated()->assertJsonPath('data.status','draft')->json('data.id');
        $this->assertDatabaseHas('makers',['id'=>$id,'user_id'=>$owner->id]);
    }
    public function test_maker_owner_policy_and_missing_record(): void {
        $maker=$this->maker(); Sanctum::actingAs($this->creator());
        $this->patchJson('/api/makers/'.$maker->id,['title'=>'Чужой'])->assertForbidden();
        $this->getJson('/api/makers/99999')->assertNotFound()->assertJsonPath('message','Запись не найдена.');
    }
    public function test_owner_sees_only_own_drafts_in_mine(): void {
        $owner=$this->creator(); $this->maker($owner); $this->maker();
        Sanctum::actingAs($owner); $this->getJson('/api/makers?mine=1')->assertOk()->assertJsonCount(1,'data')->assertJsonPath('data.0.user_id',$owner->id);
    }
    public function test_asset_png_upload_and_metadata_update(): void {
        $owner=$this->creator(); $maker=$this->maker($owner); Sanctum::actingAs($owner);
        $id=$this->postJson('/api/makers/'.$maker->id.'/assets',['name'=>'Основа','category'=>'body','layer_order'=>10,'image'=>UploadedFile::fake()->image('body.png',64,64)])
            ->assertCreated()->assertJsonPath('data.category','body')->json('data.id');
        $this->patchJson("/api/makers/$maker->id/assets/$id",['name'=>'Новая основа','layer_order'=>20])->assertOk()->assertJsonPath('data.layer_order',20);
        $this->assertDatabaseHas('assets',['id'=>$id,'name'=>'Новая основа']);
    }
    public function test_non_png_and_wrong_dimensions_are_rejected(): void {
        $owner=$this->creator(); $maker=$this->maker($owner); Sanctum::actingAs($owner);
        $url='/api/makers/'.$maker->id.'/assets'; $data=['name'=>'Файл','category'=>'body','layer_order'=>0];
        $this->postJson($url,$data+['image'=>UploadedFile::fake()->image('bad.jpg',64,64)])->assertUnprocessable()->assertJsonValidationErrors('image');
        $this->postJson($url,$data+['image'=>UploadedFile::fake()->image('small.png',8,8)])->assertUnprocessable();
        $this->postJson($url,$data+['image'=>UploadedFile::fake()->create('fake.png',10,'image/png')])->assertUnprocessable();
        $this->postJson($url,$data+['image'=>UploadedFile::fake()->image('huge.png',64,64)->size(5121)])->assertUnprocessable();
    }
    public function test_nested_asset_cannot_be_changed_through_another_maker(): void {
        $owner=$this->creator(); $maker=$this->maker($owner); $other=$this->maker($owner); $asset=$this->layer($other); Sanctum::actingAs($owner);
        $this->patchJson("/api/makers/$maker->id/assets/$asset->id",['name'=>'Подмена'])->assertNotFound();
    }
    public function test_submit_requires_body_and_face_and_rolls_back(): void {
        $owner=$this->creator(); $maker=$this->maker($owner); $this->layer($maker); Sanctum::actingAs($owner);
        $this->postJson("/api/makers/$maker->id/submit")->assertUnprocessable()->assertJsonValidationErrors('assets');
        $this->assertDatabaseHas('makers',['id'=>$maker->id,'status'=>'draft','submitted_at'=>null]);
    }
    public function test_submit_rejects_mismatched_canvas_sizes(): void {
        $owner=$this->creator(); $maker=$this->maker($owner); $this->layer($maker); $this->layer($maker,'face',128); Sanctum::actingAs($owner);
        $this->postJson("/api/makers/$maker->id/submit")->assertUnprocessable()->assertJsonValidationErrors('assets');
    }
    public function test_full_revision_resubmission_and_approval_scenario(): void {
        $owner=$this->creator(); $maker=$this->maker($owner); $this->complete($maker); Sanctum::actingAs($owner);
        $this->postJson("/api/makers/$maker->id/submit")->assertOk()->assertJsonPath('data.status','pending');
        $this->postJson("/api/makers/$maker->id/submit")->assertConflict();
        $this->patchJson("/api/makers/$maker->id",['title'=>'Нельзя'])->assertConflict();
        Sanctum::actingAs($admin=$this->admin());
        $url="/api/admin/makers/$maker->id/review";
        $this->postJson($url,['decision'=>'request-revision'])->assertUnprocessable()->assertJsonValidationErrors('review_comment');
        $this->postJson($url,['decision'=>'request-revision','review_comment'=>'Исправьте название'])->assertOk()->assertJsonPath('data.status','revision');
        Sanctum::actingAs($owner);
        $this->patchJson("/api/makers/$maker->id",['title'=>'Исправлено'])->assertOk();
        $this->postJson("/api/makers/$maker->id/submit")->assertOk();
        Sanctum::actingAs($admin);
        $this->postJson($url,['decision'=>'approve'])->assertOk()->assertJsonPath('data.status','approved');
        $this->assertDatabaseHas('makers',['id'=>$maker->id,'reviewed_by'=>$admin->id,'status'=>'approved']);
    }
    public function test_non_admin_cannot_review_and_repeat_review_is_conflict(): void {
        $owner=$this->creator(); $maker=$this->maker($owner,'pending'); $this->complete($maker); Sanctum::actingAs($owner);
        $url="/api/admin/makers/$maker->id/review";
        $this->postJson($url,['decision'=>'approve'])->assertForbidden();
        Sanctum::actingAs($this->admin());
        $this->postJson($url,['decision'=>'reject','review_comment'=>'Нарушение правил'])->assertOk()->assertJsonPath('data.status','rejected');
        $this->postJson($url,['decision'=>'approve'])->assertConflict();
    }
    public function test_approved_edit_returns_to_draft_and_removes_from_catalog(): void {
        $owner=$this->creator(); $maker=$this->maker($owner,'approved'); Sanctum::actingAs($owner);
        $this->patchJson("/api/makers/$maker->id",['description'=>'Изменено'])->assertOk()->assertJsonPath('data.status','draft');
        $this->getJson('/api/makers')->assertJsonCount(0,'data');
    }
    public function test_private_png_not_available_to_guests(): void {
        $maker=$this->maker(); $asset=$this->layer($maker);
        $this->getJson("/api/assets/$asset->id/image")->assertForbidden();
        $maker->status=MakerStatus::Approved; $maker->save();
        $this->get("/api/assets/$asset->id/image")->assertOk()->assertHeader('Content-Type','image/png');
    }
    public function test_character_crud_and_ordered_many_to_many(): void {
        $maker=$this->maker(status:'approved'); [$body,$face]=$this->complete($maker); $user=User::factory()->create(); Sanctum::actingAs($user);
        $data=['name'=>'Алиса','maker_id'=>$maker->id,'asset_ids'=>[$body->id,$face->id]];
        $id=$this->postJson('/api/characters',$data)->assertCreated()->assertJsonCount(2,'data.assets')->json('data.id');
        $this->getJson("/api/characters/$id")->assertOk()->assertJsonPath('data.name','Алиса');
        $this->putJson("/api/characters/$id",array_replace($data,['name'=>'Новый образ','asset_ids'=>[$face->id,$body->id]]))->assertOk()->assertJsonPath('data.assets.0.id',$face->id);
        $this->assertDatabaseHas('asset_character',['character_id'=>$id,'asset_id'=>$face->id,'layer_order'=>0]);
        $this->deleteJson("/api/characters/$id")->assertNoContent(); $this->assertDatabaseCount('asset_character',0);
    }
    public function test_foreign_or_duplicate_category_assets_do_not_create_character(): void {
        $maker=$this->maker(status:'approved'); $body=$this->layer($maker); $body2=$this->layer($maker); $foreign=$this->layer($this->maker(status:'approved'));
        Sanctum::actingAs(User::factory()->create());
        $data=['name'=>'Нельзя','maker_id'=>$maker->id,'asset_ids'=>[$body->id,$foreign->id]];
        $this->postJson('/api/characters',$data)->assertUnprocessable()->assertJsonValidationErrors('asset_ids');
        $this->postJson('/api/characters',array_replace($data,['asset_ids'=>[$body->id,$body2->id]]))->assertUnprocessable();
        $this->assertDatabaseCount('characters',0); $this->assertDatabaseCount('asset_character',0);
    }
    public function test_character_from_unapproved_maker_is_conflict(): void {
        $maker=$this->maker(); $asset=$this->layer($maker); Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/characters',['name'=>'Тест','maker_id'=>$maker->id,'asset_ids'=>[$asset->id]])->assertConflict();
    }
    public function test_foreign_character_is_forbidden_and_list_is_private(): void {
        $c=Character::factory()->create(); Sanctum::actingAs(User::factory()->create());
        $this->getJson("/api/characters/$c->id")->assertForbidden();
        $this->deleteJson("/api/characters/$c->id")->assertForbidden();
        $this->getJson('/api/characters')->assertJsonCount(0,'data');
    }
    public function test_post_upload_comments_likes_and_deletion(): void {
        $user=User::factory()->create(); Sanctum::actingAs($user);
        $id=$this->postJson('/api/posts',['title'=>'Работа','image'=>UploadedFile::fake()->image('art.png',64,64)])->assertCreated()->json('data.id');
        $this->getJson("/api/posts/$id")->assertOk()->assertJsonPath('data.likes_count',0);
        $comment=$this->postJson("/api/posts/$id/comments",['body'=>'Привет'])->assertCreated()->json('data.id');
        $this->putJson("/api/posts/$id/like")->assertOk()->assertJsonPath('data.likes_count',1);
        $this->putJson("/api/posts/$id/like")->assertOk()->assertJsonPath('data.likes_count',1);
        $this->assertDatabaseCount('likes',1);
        $this->deleteJson("/api/posts/$id/like")->assertOk()->assertJsonPath('data.likes_count',0);
        $this->deleteJson("/api/comments/$comment")->assertNoContent();
        $this->deleteJson("/api/posts/$id")->assertNoContent(); $this->assertDatabaseCount('posts',0);
    }
    public function test_post_cannot_reference_someone_elses_character(): void {
        $c=Character::factory()->create(); Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/posts',['title'=>'Подмена','character_id'=>$c->id,'image'=>UploadedFile::fake()->image('art.png',64,64)])->assertForbidden();
    }
    public function test_comment_validation_and_ownership(): void {
        $post=Post::factory()->create(); $comment=Comment::factory()->create(['post_id'=>$post->id]); Sanctum::actingAs(User::factory()->create());
        $this->postJson("/api/posts/$post->id/comments",['body'=>''])->assertUnprocessable()->assertJsonValidationErrors('body');
        $this->deleteJson("/api/comments/$comment->id")->assertForbidden();
        $this->deleteJson("/api/posts/$post->id")->assertForbidden();
    }
    public function test_hidden_posts_are_absent_from_public_feed_and_image(): void {
        $post=Post::factory()->create(['is_visible'=>false]);
        $this->getJson('/api/posts')->assertJsonCount(0,'data');
        $this->getJson("/api/posts/$post->id")->assertNotFound();
        $this->get("/api/posts/$post->id/image")->assertNotFound();
    }
    public function test_admin_hide_statistics_and_user_management(): void {
        $admin=$this->admin(); $user=User::factory()->create(); $maker=$this->maker(status:'approved'); $post=Post::factory()->create();
        Sanctum::actingAs($admin);
        $this->postJson("/api/admin/makers/$maker->id/hide")->assertOk()->assertJsonPath('data.status','hidden');
        $this->postJson("/api/admin/posts/$post->id/hide")->assertOk()->assertJsonPath('data.is_visible',false);
        $this->getJson('/api/admin/statistics')->assertOk()->assertJsonPath('data.makers_by_status.hidden',1);
        $this->patchJson("/api/admin/users/$user->id",['role'=>'creator'])->assertOk()->assertJsonPath('data.role','creator');
        $this->patchJson("/api/admin/users/$user->id",['is_blocked'=>true])->assertOk()->assertJsonPath('data.is_blocked',true);
        $this->patchJson("/api/admin/users/$admin->id",['is_blocked'=>true])->assertConflict();
    }
    public function test_filter_sort_and_pagination_with_meta(): void {
        $owner=$this->creator();
        for($i=0;$i<5;$i++) { $m=$this->maker($owner,'approved'); $m->title='Лунный '.$i; $m->save(); $this->layer($m); }
        $this->maker(status:'approved');
        $this->getJson('/api/makers?search=Лунный&category=body&sort=title&order=asc&per_page=2&page=2')
            ->assertOk()->assertJsonCount(2,'data')->assertJsonPath('meta.total',5)->assertJsonPath('meta.current_page',2)->assertJsonPath('meta.last_page',3)->assertJsonPath('data.0.title','Лунный 2');
        $this->getJson('/api/makers?sort=password&per_page=999')->assertUnprocessable()->assertJsonValidationErrors(['sort','per_page']);
    }
    public function test_referenced_maker_and_asset_deletion_return_409(): void {
        $owner=$this->creator(); $maker=$this->maker($owner); $asset=$this->layer($maker);
        $c=Character::factory()->create(['maker_id'=>$maker->id]); $c->assets()->attach($asset->id);
        Sanctum::actingAs($owner);
        $this->deleteJson("/api/makers/$maker->id")->assertConflict();
        $this->deleteJson("/api/makers/$maker->id/assets/$asset->id")->assertConflict();
        $this->assertDatabaseHas('makers',['id'=>$maker->id]); $this->assertDatabaseHas('assets',['id'=>$asset->id]);
    }
    public function test_cors_preflight_allows_only_configured_client(): void {
        $this->withHeaders(['Origin'=>'http://127.0.0.1:5173','Access-Control-Request-Method'=>'POST','Access-Control-Request-Headers'=>'authorization,content-type'])
            ->options('/api/makers')->assertNoContent()->assertHeader('Access-Control-Allow-Origin','http://127.0.0.1:5173');
        $r=$this->withHeaders(['Origin'=>'https://untrusted.example','Access-Control-Request-Method'=>'POST'])->options('/api/makers');
        $this->assertNotSame('https://untrusted.example',$r->headers->get('Access-Control-Allow-Origin'));
    }
    public function test_api_error_does_not_expose_stack_or_sql(): void {
        config(['app.debug'=>false]);
        $this->getJson('/api/makers/not-an-id')->assertNotFound()->assertJsonMissingPath('trace')->assertJsonMissingPath('exception');
    }
    public function test_database_unique_like_constraint(): void {
        $like=Like::factory()->create();
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('likes')->insert(['user_id'=>$like->user_id,'post_id'=>$like->post_id,'created_at'=>now(),'updated_at'=>now()]);
    }
}
