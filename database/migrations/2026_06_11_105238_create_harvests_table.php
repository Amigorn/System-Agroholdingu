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
            $table->dateTime('date');
            $table->decimal('quantity', 10, 2);
            $table->foreignId('unit_id')->constrained('unit_of_measures')->onDelete('cascade');
            $table->foreignId('crop_id')->constrained('crops')->onDelete('cascade');
            $table->foreignId('field_id')->constrained('fields')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
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