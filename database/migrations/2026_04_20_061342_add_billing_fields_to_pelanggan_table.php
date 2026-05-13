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
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_router')->nullable()->after('id_pelanggan');
            $table->string('mikrotik_username')->nullable()->after('nama_pelanggan');
            $table->enum('mikrotik_type', ['pppoe', 'hotspot', 'static'])->default('pppoe')->after('mikrotik_username');
            $table->decimal('harga_layanan', 15, 2)->default(0)->after('jumlah_device');
            $table->boolean('is_active')->default(true)->after('harga_layanan');
            
            $table->foreign('id_router')->references('id_router')->on('routers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropForeign(['id_router']);
            $table->dropColumn(['id_router', 'mikrotik_username', 'mikrotik_type', 'harga_layanan', 'is_active']);
        });
    }
};
