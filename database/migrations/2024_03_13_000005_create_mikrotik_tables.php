<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_router', function (Blueprint $table) {
            $table->id('id_router');
            $table->string('nama_router');
            $table->string('ip_host');
            $table->integer('api_port')->default(8728);
            $table->string('username');
            $table->string('password_encrypted');
            $table->boolean('is_active')->default(true);
            $table->string('status_koneksi')->default('Disconnected');
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mikrotik_stat', function (Blueprint $table) {
            $table->id('id_stat');
            $table->unsignedBigInteger('id_router');
            $table->string('uptime')->nullable();
            $table->integer('cpu_load')->default(0);
            $table->bigInteger('memory_free')->default(0);
            $table->bigInteger('rx_bps')->default(0);
            $table->bigInteger('tx_bps')->default(0);
            $table->timestamp('recorded_at');
            $table->foreign('id_router')->references('id_router')->on('mikrotik_router')->onDelete('cascade');
        });

        Schema::create('mikrotik_interface', function (Blueprint $table) {
            $table->id('id_interface');
            $table->unsignedBigInteger('id_router');
            $table->string('nama_interface');
            $table->string('status')->default('unknown');
            $table->bigInteger('rx_bps')->default(0);
            $table->bigInteger('tx_bps')->default(0);
            $table->timestamp('recorded_at');
            $table->foreign('id_router')->references('id_router')->on('mikrotik_router')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_interface');
        Schema::dropIfExists('mikrotik_stat');
        Schema::dropIfExists('mikrotik_router');
    }
};
