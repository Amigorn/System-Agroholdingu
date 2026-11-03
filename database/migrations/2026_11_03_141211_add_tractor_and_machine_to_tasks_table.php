<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('tractor_id')->nullable()->constrained('machines')->nullOnDelete();
            // machine_id już istnieje w tabeli tasks, więc go tutaj nie dodajemy ponownie
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['tractor_id']);
            $table->dropColumn('tractor_id');
        });
    }
};