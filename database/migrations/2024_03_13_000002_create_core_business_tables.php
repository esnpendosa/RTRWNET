<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id('id_pelanggan');
            $table->string('kode_pelanggan')->unique();
            $table->string('nama_pelanggan');
            $table->text('alamat');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('usage_gb', 10, 2)->default(0);
            $table->integer('jumlah_device')->default(0);
            $table->string('prioritas_label')->nullable(); // High, Medium, Low
            $table->timestamps();
        });

        Schema::create('teknisi', function (Blueprint $table) {
            $table->id('id_teknisi');
            $table->unsignedBigInteger('id_user');
            $table->string('nama_teknisi');
            $table->string('no_hp');
            $table->decimal('base_latitude', 10, 8);
            $table->decimal('base_longitude', 11, 8);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('tiket_gangguan', function (Blueprint $table) {
            $table->id('id_tiket');
            $table->string('kode_tiket')->unique();
            $table->unsignedBigInteger('id_pelanggan');
            $table->string('prioritas');
            $table->enum('status', ['Open', 'In Progress', 'Resolved', 'Closed'])->default('Open');
            $table->text('keluhan');
            $table->unsignedBigInteger('id_teknisi')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->onDelete('cascade');
            $table->foreign('id_teknisi')->references('id_teknisi')->on('teknisi')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiket_gangguan');
        Schema::dropIfExists('teknisi');
        Schema::dropIfExists('pelanggan');
    }
};
