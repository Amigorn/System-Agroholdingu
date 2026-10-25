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
    Schema::create('harvests', function (Blueprint $table) {
        $table->id();
        $table->dateTime('data_zbioru');
        $table->decimal('ilosc', 10, 2);
        $table->foreignId('jednostka_id')->constrained('unit_of_measures')->onDelete('cascade');
        $table->foreignId('uprawa_id')->constrained('crops')->onDelete('cascade');
        $table->foreignId('pole_id')->constrained('fields')->onDelete('cascade');
        $table->foreignId('produkt_id')->constrained('products')->onDelete('cascade'); // Powiązanie zbioru z produktem z diagramu
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
