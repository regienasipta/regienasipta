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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user');
            $table->string('image');
            $table->bigInteger('nis');
            $table->string('tingkatan');
            $table->string('jurusan');
            $table->string('kelas');
            $table->bigInteger('hp');
            $table->integer('status'); // 0=tidak aktif 1=aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
