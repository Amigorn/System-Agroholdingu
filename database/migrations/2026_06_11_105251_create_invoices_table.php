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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('numer');
            $table->date('data_wystawienia');
            $table->string('typ'); // 'zakup' lub 'sprzedaz'
            $table->string('kontrahent');
            $table->decimal('wartosc', 10, 2)->default(0);
            $table->text('opis')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};