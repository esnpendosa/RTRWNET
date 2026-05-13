<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_responses', function (Blueprint $table) {
            $table->id();
            $table->string('keyword')->comment('Kata kunci yang dideteksi (pisahkan dengan koma jika banyak)');
            $table->text('response')->comment('Balasan otomatis dari bot');
            $table->boolean('is_exact_match')->default(false)->comment('Apakah harus sama persis (true) atau mengandung kata (false)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_responses');
    }
};
