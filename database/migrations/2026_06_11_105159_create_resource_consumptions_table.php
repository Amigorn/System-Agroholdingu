<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('resource_consumptions', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantity', 10, 2);
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('resource_id')->constrained('resources')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_consumptions');
    }
};