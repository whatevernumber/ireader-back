<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompletedBookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * User can complete the book
     */
    public function test_user_can_mark_book_as_completed(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $response = $this->post('api/completed/' . $book->isbn);

        $response->assertStatus(200);

        $this->assertDatabaseHas('finished_books',
        [
            'user_id' => $user->id,
            'book_isbn' => $book->isbn,
        ]);
    }

    /**
     * User can get list of his completed books
     */
    public function test_user_can_get_finished_books(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $books = Book::factory()->count(3)->create();

        foreach ($books as $book) {
            $user->finishedBooks()->attach($book);
        }

        $response = $this->get('api/completed');

        $response->assertJsonStructure(
            [
                'data' => [
                    ['isbn'],
                    ['isbn'],
                    ['isbn'],
                ]
            ]
        );
    }

    /**
     * User can delete a book from completed list
     */
    public function test_user_can_delete_book_from_list(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['update']);

        $book = Book::factory()->create();
        $user->finishedBooks()->attach($book->isbn);

        $response = $this->delete('api/completed/' . $book->isbn);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('finished_books',
            [
                'book_isbn' => $book->isbn,
            ]);
    }

    /**
     * User cannot delete a book from completed list if it's not there
     */
    public function test_user_cannot_delete_book_from_list(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['update']);

        $book = Book::factory()->create();
        $secondBook = Book::factory()->create();

        $user->finishedBooks()->attach($secondBook->isbn);

        $response = $this->delete('api/completed/' . $book->isbn);

        $response->assertStatus(409);
    }
}
