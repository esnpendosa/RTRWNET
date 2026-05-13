<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_responses', function (Blueprint $table) {
            $table->boolean('is_menu')->default(false)->after('is_exact_match');
            $table->unsignedBigInteger('parent_id')->nullable()->after('is_menu');
            $table->integer('sort_order')->default(0)->after('parent_id');
            $table->boolean('group_enabled')->default(false)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('bot_responses', function (Blueprint $table) {
            $table->dropColumn(['is_menu', 'parent_id', 'sort_order', 'group_enabled']);
        });
    }
};
