<?php

namespace Tests\Feature;

use App\Jobs\GoogleBookCoverJob;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Admin can add a new book
     */
    public function test_admin_can_add_book(): void
    {
        Queue::fake();

        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user, ['update']);

        $book = Book::factory()->make();
        $genre = Genre::factory()->make();
        $author = Author::factory()->make();

        $this->postJson('api/books', [
            'isbn' => $book->isbn,
            'title' => $book->title,
            'description' => $book->description,
            'price' => $book->price,
            'published_year' => $book->published_year,
            'authors' => [$author->name],
            'genres' => [$genre->value],
        ]);

        $this->assertDatabaseHas('books',
        [
           'isbn' => $book->isbn,
           'title' => $book->title,
           'description' => $book->description,
           'price' => $book->price,
           'published_year' => $book->published_year,
        ]);

        Queue::assertPushed(GoogleBookCoverJob::class);
    }

    /**
     * Admin can update a book
     */
    public function test_admin_can_update_book(): void
    {

        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user, ['update']);

        $genre = Genre::factory()->create();
        $author = Author::factory()->create();

        $book = Book::factory()->hasAttached($genre)->hasAttached($author)->create();
        $updatedBook = Book::factory()->state(['isbn' => $book->isbn])->make();

        $this->patchJson('api/books/' . $book->isbn, [
            'isbn' => $updatedBook->isbn,
            'title' => $updatedBook->title,
            'description' => $updatedBook->description,
            'price' => $updatedBook->price,
            'published_year' => $updatedBook->published_year,
            'authors' => [$author->name],
            'genres' => [$genre->value],
        ]);

        $this->assertDatabaseHas('books',
        [
           'isbn' => $book->isbn,
           'title' => $updatedBook->title,
           'description' => $updatedBook->description,
           'price' => $updatedBook->price,
           'published_year' => $updatedBook->published_year,
        ]);
    }

    /**
     * Admin can delete a book
     */
    public function test_admin_can_delete_book(): void
    {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user, ['update']);

        $book = Book::factory()->hasAttached(Genre::factory()->create())->hasAttached(Author::factory()->create())->create();

        $response = $this->delete('api/books/' . $book->isbn);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books',
        [
            'isbn' => $book->isbn,
        ]);
    }

    /**
     * User cannot update book
     */
    public function test_user_cannot_update_books(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $genre = Genre::factory()->create();
        $author = Author::factory()->create();

        $book = Book::factory()->hasAttached($genre)->hasAttached($author)->create();
        $updatedBook = Book::factory()->state(['isbn' => $book->isbn])->make();

        $response = $this->patchJson('api/books/' . $book->isbn, [
            'isbn' => $updatedBook->isbn,
            'title' => $updatedBook->title,
            'description' => $updatedBook->description,
            'price' => $updatedBook->price,
            'published_year' => $updatedBook->published_year,
            'authors' => [$author->name],
            'genres' => [$genre->value],
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('books',
            [
                'isbn' => $book->isbn,
                'title' => $book->title,
                'description' => $book->description,
                'price' => $book->price,
                'published_year' => $book->published_year,
            ]);
    }

    /**
     * User cannot delete book
     */
    public function test_user_cannot_delete_book(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->hasAttached(Genre::factory()->create())->hasAttached(Author::factory()->create())->create();

        $response = $this->delete('api/books/' . $book->isbn);

        $response->assertStatus(403);
    }

    /**
     * User can get one book
     */
    public function test_user_can_get_book(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->hasAttached(Genre::factory()->create())->hasAttached(Author::factory()->create())->create();

        $response = $this->get('api/books/' . $book->isbn);

        $response->assertJson([
            'data' => [
                'isbn' => $book->isbn,
            ]
        ]);
    }

    /**
     * User can get list of books
     */
    public function test_user_can_get_books(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->hasAttached(Genre::factory()->create())->hasAttached(Author::factory()->create())->count(3)->create();

        $response = $this->get('api/books');

        $response->assertJsonStructure([
            'data' => [
                [
                    'isbn',
                    'title',
                    'description',
                    'price',
                    'published_year',
                    'authors',
                    'genres',
                    'image',
                ],
                [
                    'isbn',
                    'title',
                    'description',
                    'price',
                    'published_year',
                    'authors',
                    'genres',
                    'image',
                ],
                [
                    'isbn',
                    'title',
                    'description',
                    'price',
                    'published_year',
                    'authors',
                    'genres',
                    'image',
                ],
            ]
        ]);
    }

    /**
     * The book can be found by title
     */
    public function test_book_can_be_found_by_title(): void
    {
        $genre = Genre::factory()->create();
        $author = Author::factory()->create();

        $book = Book::factory()->hasAttached($genre)->hasAttached($author)->create();

        $response = $this->call('GET', '/api/books/search', [
            'text' => $book->title,
        ]);

        $response->assertStatus(200);

        $response->assertJson(fn (AssertableJson $json) =>
        $json->has('data')
            ->has('data.0', fn (AssertableJson $json) =>
            $json->where('isbn', intval($book->isbn))
                ->where('title', $book->title)
                ->etc()
            )
            ->etc()
        );
    }

    /**
     * The book can be found by author
     */
    public function test_book_can_be_found_by_author(): void
    {
        $genre = Genre::factory()->create();
        $author = Author::factory()->create();

        $book = Book::factory()->hasAttached($genre)->hasAttached($author)->create();

        $response = $this->call('GET', '/api/books/search', [
            'author' => $author->name,
        ]);

        $response->assertStatus(200);

        $response->assertJson(fn (AssertableJson $json) =>
        $json->has('data')
            ->has('data.0', fn (AssertableJson $json) =>
            $json->where('isbn', intval($book->isbn))
            ->has('authors')
                ->etc()
                ->has('authors.0', fn (AssertableJson $json) =>
                $json->where('id', $author->id)
                    ->where('name', $author->name)
                    ->etc()
                )
            )
            ->etc()
        );
    }

    /**
     * The book can be found by genre
     */
    public function test_book_can_be_found_by_genre(): void
    {
        $genre = Genre::factory()->create();
        $author = Author::factory()->create();

        $book = Book::factory()->hasAttached($genre)->hasAttached($author)->create();

        $response = $this->call('GET', '/api/books/search', [
            'genre' => $genre->value,
        ]);

        $response->assertStatus(200);

        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data')
                ->has('data.0', fn (AssertableJson $json) =>
                    $json->where('isbn', intval($book->isbn))
                    ->has('genres')
                        ->etc()
                    ->has('genres.0', fn (AssertableJson $json) =>
                    $json->where('id', $genre->id)
                        ->where('value', $genre->value)
                    ->etc()
                )
            )
            ->etc()
        );
    }

    /**
     * The book can be found by mix of parameters
     */
    public function test_book_can_be_found_with_few_params(): void
    {
        $genre = Genre::factory()->create();
        $author = Author::factory()->create();

        $book = Book::factory()->hasAttached($genre)->hasAttached($author)->create();

        $response = $this->call('GET', '/api/books/search', [
            'text' => $book->title,
            'author' => $author->name,
            'genre' => $genre->value,
        ]);

        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
        $json->has('data')
            ->has('data.0', fn (AssertableJson $json) =>
            $json->where('isbn', intval($book->isbn))
                ->where('title', $book->title,)
                ->has('genres')
                ->etc()
                ->has('genres.0', fn (AssertableJson $json) =>
                $json->where('id', $genre->id)
                    ->where('value', $genre->value)
                    ->etc()
                )
                ->has('authors.0', fn (AssertableJson $json) =>
                $json->where('id', $author->id)
                    ->where('name', $author->name)
                    ->etc()
                )
            )
            ->etc()
        );
    }

    /**
     * The book cannot be found without given parameters
     */
    public function test_book_cannot_be_found_without_params(): void
    {

        $genre = Genre::factory()->create();
        $author = Author::factory()->create();

        $book = Book::factory()->hasAttached($genre)->hasAttached($author)->create();

        $response = $this->get('api/books/search');

        $response->assertStatus(400);
    }
}
