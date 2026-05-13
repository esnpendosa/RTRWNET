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
            $table->string('ip_address')->nullable()->after('mikrotik_type');
            $table->boolean('last_online_status')->default(true)->after('ip_address');
            $table->timestamp('last_ping_at')->nullable()->after('last_online_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'last_online_status', 'last_ping_at']);
        });
    }
};
