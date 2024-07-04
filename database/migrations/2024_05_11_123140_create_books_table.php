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
        Schema::create('books', function (Blueprint $table) {
            $table->bigInteger('isbn')->primary();
            $table->boolean('has_x')->default(false);
            $table->tinyText('title');
            $table->text('description');
            $table->tinyInteger('published_year');
            $table->integer('pages')->nullable();
            $table->timestamps();
            $table->tinyInteger('rate')->nullable();

            $table->index('title');
            $table->fullText('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
