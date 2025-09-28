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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            $table->integer('num_loteamento');
            $table->integer('num_lote');
            $table->integer('num_quadra');
            $table->decimal('area_lote', 12, 2);
            $table->timestamps();

            $table->unique(['num_loteamento', 'num_quadra', 'num_lote'], 'lotes_uk_localizacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
