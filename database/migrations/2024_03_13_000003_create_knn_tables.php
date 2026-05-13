<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knn_parameter', function (Blueprint $table) {
            $table->id('id_knn_param');
            $table->integer('nilai_k');
            $table->string('distance_metric')->default('euclidean');
            $table->timestamps();
        });

        Schema::create('knn_hasil', function (Blueprint $table) {
            $table->id('id_knn_hasil');
            $table->unsignedBigInteger('id_pelanggan');
            $table->unsignedBigInteger('id_knn_param');
            $table->decimal('jarak_min', 15, 10);
            $table->string('label_hasil');
            $table->timestamps();
            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->onDelete('cascade');
            $table->foreign('id_knn_param')->references('id_knn_param')->on('knn_parameter')->onDelete('cascade');
        });

        Schema::create('knn_detail_tetangga', function (Blueprint $table) {
            $table->id('id_knn_detail');
            $table->unsignedBigInteger('id_knn_hasil');
            $table->integer('urutan');
            $table->unsignedBigInteger('id_pelanggan_tetangga');
            $table->decimal('jarak_euclidean', 15, 10);
            $table->string('label_tetangga');
            $table->timestamps();
            $table->foreign('id_knn_hasil')->references('id_knn_hasil')->on('knn_hasil')->onDelete('cascade');
            $table->foreign('id_pelanggan_tetangga')->references('id_pelanggan')->on('pelanggan')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knn_detail_tetangga');
        Schema::dropIfExists('knn_hasil');
        Schema::dropIfExists('knn_parameter');
    }
};
