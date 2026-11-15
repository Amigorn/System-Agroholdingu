<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tractor_id')->constrained('machines')->cascadeOnDelete(); // Ciągnik
            $table->foreignId('machine_id')->nullable()->constrained('machines')->cascadeOnDelete(); // Narzędzie
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Operator tego zestawu
            $table->text('notes')->nullable(); // Uwagi / Rola do tego zestawu
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_machines');
    }
};