<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wa_status_schedules', function (Blueprint $table) {
            $table->id();
            $table->text('content')->nullable();
            $table->string('media')->nullable();
            $table->dateTime('scheduled_at');
            $table->enum('status', ['pending', 'posted', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_status_schedules');
    }
};
