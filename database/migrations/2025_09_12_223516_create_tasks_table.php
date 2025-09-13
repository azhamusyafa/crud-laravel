<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['To-Do', 'In Progress', 'Done'])->default('To-Do');
            $table->datetime('due_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('due_at');
            $table->index(['status', 'due_at']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};