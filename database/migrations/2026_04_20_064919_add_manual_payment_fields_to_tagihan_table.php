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
        Schema::table('tagihan', function (Blueprint $table) {
            $table->enum('metode_pembayaran', ['otomatis', 'manual', 'cash'])->nullable()->after('status');
            $table->string('bukti_bayar')->nullable()->after('metode_pembayaran');
            $table->text('catatan_admin')->nullable()->after('bukti_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            $table->dropColumn(['metode_pembayaran', 'bukti_bayar', 'catatan_admin']);
        });
    }
};
