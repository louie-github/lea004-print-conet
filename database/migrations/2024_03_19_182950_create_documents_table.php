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
    Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('url');
            $table->string('name');
            $table->string('color');
            $table->integer('page_range_start')->default(1);
            $table->integer('page_range_end')->default(1);
            $table->integer('amount_paid')->default(0);
            $table->integer('no_of_copies')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
