<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        DB::table('users')->insert([
            [
                'name' => 'Администратор',
                'email' => 'admin@test.ru',
                'is_admin' => true,
                'password' => Hash::make('test')
            ],
            [
                'name' => 'Покупатель',
                'email' => 'buyer@mail.ru',
                'is_admin' => false,
                'password' => Hash::make('test'),
            ]
        ]);

        DB::table('authors')->insert([
           'name' => 'Эл Фарбер'
        ]);

        DB::table('genres')->insert([
            'value' => 'Социальная фантастика'
        ]);

        DB::table('books')->insert([
            'isbn' => 9785045050647,
            'title' => 'Lenimentus',
            'description' => 'Рано или поздно это должно было случиться.
                               Разработанная человеком сеть искусственных интеллектов, питавшая саму жизнь, обернулась против него.
                               Планета была очищена и преобразована. Остатки человечества укрылись под энергетическим куполом неприступного «Квадрата Совета».
                               Барьер надёжно защищает от угроз извне, но способен ли он уберечь порядок внутри?
                               Есть ли у утративших самих себя людей право на то, чтобы вновь встать во главе мироздания, или их участь предрешена неумолимым Усовершенствованием?',
            'published_year' => '2022',
            'price' => '169',
        ]);

        DB::table('book_genre')->insert([
            'book_isbn' => 9785045050647,
            'genre_id' => 1,
        ]);

        DB::table('author_book')->insert([
            'book_isbn' => 9785045050647,
            'author_id' => 1,
        ]);

        DB::table('images')->insert([
            'image' => 'ibook-leni.jpeg',
            'book_isbn' => 9785045050647,
        ]);

        DB::table('favourites')->insert([
            'user_id' => 1,
            'book_isbn' => 9785045050647,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('book_genre')->truncate();
        DB::table('book_author')->truncate();
        DB::table('favourites')->truncate();
        DB::table('users')->truncate();
        DB::table('genres')->truncate();
        DB::table('authors')->truncate();
        DB::table('books')->truncate();
        DB::table('images')->truncate();
    }
};
