<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('finished_books', function (Blueprint $table) {
            $table->id();
            $table->longText('comment')->nullable();
            $table->tinyInteger('rate')->nullable();
            $table->integer('completed_days')->nullable();

            $table->foreignId('book_isbn')->references('isbn')
                ->on('books')->cascadeOnDelete();
            $table->foreignId('user_id')->references('id')
                ->on('users')->cascadeOnDelete();

            $table->unique(['book_isbn', 'user_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('read');
    }
};
