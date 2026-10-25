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
    Schema::create('crops', function (Blueprint $table) {
        $table->id();
        $table->string('nazwa');
        $table->text('opis')->nullable();
        $table->foreignId('pole_id')->constrained('fields')->onDelete('cascade');
        $table->foreignId('sezon_uprawowy_id')->constrained('seasons')->onDelete('cascade'); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};
