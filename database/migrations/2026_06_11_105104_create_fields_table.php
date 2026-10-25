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
    Schema::create('fields', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->decimal('area', 8, 2); // powierzchnia
        $table->string('soil_type')->nullable(); // typ gleby
        $table->string('location')->nullable(); // lokalizacja
        $table->text('description')->nullable(); // opis
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
