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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->integer('ticket_number');
            $table->string('title');
            $table->string('description');
            $table->string('status');
            $table->string('priority');
            $table->foreignId('category_id')->constrained()->on('ticket_categories');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('assigned_to')->constrained()->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
