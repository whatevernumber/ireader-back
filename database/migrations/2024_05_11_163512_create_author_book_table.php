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
        Schema::create('author_book', function (Blueprint $table) {
            $table->foreignId('author_id')->references('id')->on('authors')->cascadeOnDelete();
            $table->foreignId('book_isbn')->references('isbn')->on('books')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_author');
    }
};
