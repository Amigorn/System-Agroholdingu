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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faktura_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('produkt_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('ilosc', 10, 2);
            $table->decimal('cena_jednostkowa', 10, 2);
            $table->decimal('wartosc', 10, 2);
            $table->text('uwagi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};