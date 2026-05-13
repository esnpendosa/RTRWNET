<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_responses', function (Blueprint $table) {
            $table->string('menu_label')->nullable()->after('keyword');
        });
    }

    public function down(): void
    {
        Schema::table('bot_responses', function (Blueprint $table) {
            $table->dropColumn('menu_label');
        });
    }
};
