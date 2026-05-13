<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id('id_inventory');
            $table->string('nama_alat');
            $table->string('kategori')->nullable(); // e.g. Tang, Splicer, Router
            $table->string('merk')->nullable();
            $table->string('serial_number')->nullable()->unique();
            $table->enum('kondisi', ['baik', 'rusak', 'perlu_perbaikan'])->default('baik');
            $table->enum('status', ['tersedia', 'digunakan', 'hilang', 'service'])->default('tersedia');
            $table->unsignedBigInteger('id_teknisi')->nullable(); // Assigned to technician
            $table->unsignedBigInteger('id_user')->nullable(); // Assigned to internal user/admin
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_teknisi')->references('id_teknisi')->on('teknisi')->onDelete('set null');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id('id_log');
            $table->unsignedBigInteger('id_inventory');
            $table->string('aksi'); // e.g. pinjam, kembali, service, update_kondisi
            $table->unsignedBigInteger('id_user_executor'); // Who performed the action
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_inventory')->references('id_inventory')->on('inventory_items')->onDelete('cascade');
            $table->foreign('id_user_executor')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('inventory_items');
    }
};
