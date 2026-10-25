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
    Schema::create('resource_consumptions', function (Blueprint $table) {
        $table->id();
        $table->decimal('ilosc', 10, 2);
        $table->foreignId('zadanie_id')->constrained('tasks')->onDelete('cascade');
        $table->foreignId('surowiec_id')->constrained('resources')->onDelete('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_consumptions');
    }
};
