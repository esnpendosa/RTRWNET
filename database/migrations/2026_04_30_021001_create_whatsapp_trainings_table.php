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
        Schema::create('whatsapp_trainings', function (Blueprint $member) {
            $member->id();
            $member->string('remote_jid')->nullable();
            $member->string('sender_name')->nullable();
            $member->text('message')->nullable();
            $member->string('type')->default('chat');
            $member->timestamp('timestamp')->nullable();
            $member->boolean('is_from_me')->default(false);
            $member->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_trainings');
    }
};
