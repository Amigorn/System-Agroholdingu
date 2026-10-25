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
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('type');
        $table->dateTime('planned_at');
        $table->dateTime('completed_at')->nullable();
        $table->string('status');
        $table->text('description')->nullable();
        
        // Klucze obce w standardzie Laravela
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('machine_id')->nullable()->constrained()->onDelete('set null');
        $table->foreignId('field_id')->constrained()->onDelete('cascade');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
