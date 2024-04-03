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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('document_id');
            $table->integer('total_pages')->default(1);
            $table->integer('page_start')->default(0);
            $table->integer('page_end')->default(0);
            $table->integer('amount_to_be_paid')->default(0);
            $table->integer('amount_collected')->default(0);
            $table->string('status')->default('Pending');
            $table->integer('no_copies')->default(1);
            $table->boolean('is_colored')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
