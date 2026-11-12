<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->timestamp('creator_since')->nullable());
        Schema::table('makers', fn (Blueprint $table) => $table->string('vector_style', 24)->nullable()->unique());
        Schema::table('posts', fn (Blueprint $table) => $table->text('body')->nullable());

        DB::table('users')->whereExists(function ($query) {
            $query->selectRaw('1')->from('makers')
                ->whereColumn('makers.user_id', 'users.id')->whereNotNull('submitted_at');
        })->update(['creator_since' => now()]);
    }

    public function down(): void
    {
        Schema::table('posts', fn (Blueprint $table) => $table->dropColumn('body'));
        Schema::table('makers', function (Blueprint $table) {
            $table->dropUnique(['vector_style']);
            $table->dropColumn('vector_style');
        });
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('creator_since'));
    }
};
