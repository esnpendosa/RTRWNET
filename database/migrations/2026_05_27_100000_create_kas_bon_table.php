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
        Schema::create('kas_bon', function (Blueprint $table) {
            $table->id('id_kas_bon');
            $table->unsignedBigInteger('id_teknisi')->nullable();
            $table->string('nama_pekerja')->nullable();
            $table->decimal('jumlah', 15, 2);
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['belum_lunas', 'lunas', 'dibatalkan'])->default('belum_lunas');
            $table->timestamps();

            $table->foreign('id_teknisi')->references('id_teknisi')->on('teknisi')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_bon');
    }
};
