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
            $table->date('read_date')->default(DB::raw('NOW()'));
            $table->longText('comment')->nullable();
            $table->tinyInteger('rate')->nullable();

            $table->foreignId('book_isbn')->references('isbn')
                ->on('books')->cascadeOnDelete();
            $table->foreignId('user_id')->references('id')
                ->on('users')->cascadeOnDelete();

            $table->unique(['book_isbn', 'user_id']);
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
