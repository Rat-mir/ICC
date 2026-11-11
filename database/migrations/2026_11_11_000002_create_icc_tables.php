<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('makers',function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('title'); $t->text('description'); $t->string('status',20)->default('draft');
            $t->text('review_comment')->nullable(); $t->dateTime('submitted_at')->nullable(); $t->dateTime('reviewed_at')->nullable(); $t->timestamps();
            $t->index('status'); $t->index('title'); $t->index(['user_id','status']);
        });
        Schema::create('assets',function(Blueprint $t) {
            $t->id(); $t->foreignId('maker_id')->constrained()->cascadeOnDelete();
            $t->string('name'); $t->string('category',30); $t->string('file_path'); $t->unsignedInteger('layer_order')->default(0);
            $t->boolean('is_active')->default(true); $t->timestamps(); $t->index('category'); $t->index(['maker_id','layer_order']);
        });
        Schema::create('characters',function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->restrictOnDelete(); $t->foreignId('maker_id')->constrained()->restrictOnDelete(); $t->string('name'); $t->timestamps();
        });
        Schema::create('asset_character',function(Blueprint $t) {
            $t->foreignId('asset_id')->constrained()->cascadeOnDelete(); $t->foreignId('character_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('layer_order')->default(0); $t->timestamps(); $t->primary(['asset_id','character_id']);
        });
        Schema::create('posts',function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->foreignId('character_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title'); $t->string('image_path'); $t->boolean('is_visible')->default(true); $t->timestamps(); $t->index(['is_visible','created_at']);
        });
        Schema::create('comments',function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->foreignId('post_id')->constrained()->cascadeOnDelete(); $t->text('body'); $t->timestamps(); $t->index(['post_id','created_at']);
        });
        Schema::create('likes',function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->foreignId('post_id')->constrained()->cascadeOnDelete(); $t->timestamps(); $t->unique(['user_id','post_id']);
        });
    }
    public function down(): void { foreach(['likes','comments','posts','asset_character','characters','assets','makers'] as $table) Schema::dropIfExists($table); }
};
