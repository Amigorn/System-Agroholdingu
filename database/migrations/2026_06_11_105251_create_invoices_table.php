<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number');                  // zamiast 'numer'
            $table->date('issue_date');               // zamiast 'data_wystawienia'
            $table->string('type');                   // 'purchase' lub 'sale' (zamiast 'zakup'/'sprzedaz')
            $table->string('contractor');             // zamiast 'kontrahent'
            $table->decimal('total', 10, 2)->default(0); // zamiast 'wartosc'
            $table->text('description')->nullable();  // zamiast 'opis'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};