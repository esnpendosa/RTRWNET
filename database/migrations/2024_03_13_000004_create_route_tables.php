<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rute', function (Blueprint $table) {
            $table->id('id_rute');
            $table->unsignedBigInteger('id_teknisi');
            $table->date('tanggal_kunjungan');
            $table->decimal('titik_awal_lat', 10, 8);
            $table->decimal('titik_awal_lng', 11, 8);
            $table->string('metode')->default('Nearest Neighbor');
            $table->decimal('total_jarak_km', 10, 2)->default(0);
            $table->enum('status', ['Planned', 'In Progress', 'Completed', 'Cancelled'])->default('Planned');
            $table->timestamps();
            $table->foreign('id_teknisi')->references('id_teknisi')->on('teknisi')->onDelete('cascade');
        });

        Schema::create('rute_detail', function (Blueprint $table) {
            $table->id('id_rute_detail');
            $table->unsignedBigInteger('id_rute');
            $table->integer('urutan');
            $table->unsignedBigInteger('id_pelanggan');
            $table->decimal('jarak_dari_sebelumnya_km', 10, 2)->default(0);
            $table->integer('estimasi_waktu_menit')->default(0);
            $table->enum('status_kunjungan', ['Pending', 'Visited', 'Skipped'])->default('Pending');
            $table->text('catatan_teknisi')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();
            $table->foreign('id_rute')->references('id_rute')->on('rute')->onDelete('cascade');
            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rute_detail');
        Schema::dropIfExists('rute');
    }
};
