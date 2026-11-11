<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController,MakerController,AssetController,CharacterController,PostController,InteractionController,AdminUserController,HealthController};
Route::get('health',HealthController::class)->name('api.health');
Route::middleware('throttle:login')->group(function() {
    Route::post('register',[AuthController::class,'register'])->name('api.register');
    Route::post('login',[AuthController::class,'login'])->name('api.login');
});
Route::middleware(['optional.auth','throttle:api'])->group(function() {
    Route::apiResource('makers',MakerController::class)->only(['index','show']);
    Route::apiResource('posts',PostController::class)->only(['index','show']);
    Route::get('assets/{asset}/image',[AssetController::class,'image'])->name('assets.image');
    Route::get('posts/{post}/image',[PostController::class,'image'])->name('posts.image');
});
Route::middleware(['auth:sanctum','active','throttle:api'])->group(function() {
    Route::get('user',[AuthController::class,'user'])->name('api.user');
    Route::post('logout',[AuthController::class,'logout'])->name('api.logout');
    Route::apiResource('characters',CharacterController::class);
    Route::apiResource('posts',PostController::class)->only(['store','destroy']);
    Route::post('posts/{post}/comments',[InteractionController::class,'comment'])->name('comments.store');
    Route::delete('comments/{comment}',[InteractionController::class,'destroy'])->name('comments.destroy');
    Route::put('posts/{post}/like',[InteractionController::class,'like'])->name('likes.store');
    Route::delete('posts/{post}/like',[InteractionController::class,'unlike'])->name('likes.destroy');
    Route::middleware('role:creator,admin')->group(function() {
        Route::apiResource('makers',MakerController::class)->only(['store','update','destroy']);
        Route::post('makers/{maker}/submit',[MakerController::class,'submit'])->name('makers.submit');
        Route::post('makers/{maker}/assets',[AssetController::class,'store'])->name('assets.store');
        Route::match(['put','patch'],'makers/{maker}/assets/{asset}',[AssetController::class,'update'])->name('assets.update');
        Route::delete('makers/{maker}/assets/{asset}',[AssetController::class,'destroy'])->name('assets.destroy');
    });
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function() {
        Route::get('makers',[MakerController::class,'moderation'])->name('makers.index');
        Route::post('makers/{maker}/review',[MakerController::class,'review'])->name('makers.review');
        Route::post('makers/{maker}/hide',[MakerController::class,'hide'])->name('makers.hide');
        Route::get('posts',[PostController::class,'moderation'])->name('posts.index');
        Route::post('posts/{post}/hide',[PostController::class,'hide'])->name('posts.hide');
        Route::get('statistics',[MakerController::class,'statistics'])->name('statistics');
        Route::get('users',[AdminUserController::class,'index'])->name('users.index');
        Route::patch('users/{user}',[AdminUserController::class,'update'])->name('users.update');
    });
});
