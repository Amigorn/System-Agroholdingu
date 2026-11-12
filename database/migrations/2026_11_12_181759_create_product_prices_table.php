<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('price', 10, 2); // Cena netto/brutto (zależy od konwencji, zazwyczaj netto)
            $table->date('valid_from');      // Obowiązuje od
            $table->date('valid_until')->nullable(); // Obowiązuje do (puste = bezterminowo / do odwołania)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};