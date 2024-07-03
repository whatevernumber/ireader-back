<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProgressTest extends TestCase
{
    use RefreshDatabase;
    /**
     * User can add book to progress list
     */
    public function test_user_can_add_book_to_progress_list(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $response = $this->post('api/progress/' . $book->isbn);

        $response->assertStatus(200);

        $this->assertDatabaseHas('books_in_progress', [
            'book_isbn' => $book->isbn,
            'user_id' => $user->id,
        ]);
    }

    /**
     * User can't add existing in the list book again
     */
    public function test_user_cannot_add_book_to_progress_list_twice(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $user->onRead()->save($book);

        $response = $this->post('api/progress/' . $book->isbn);

        $response->assertStatus(409);
    }

    /**
     * User can't add already read book in the progress list again
     */
    public function test_user_cannot_add_finished_book_to_progress_list(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $user->finishedBooks()->attach($book->isbn);

        $response = $this->post('api/progress/' . $book->isbn);

        $response->assertStatus(409);
    }

    /**
     * User can book from the progress list
     */
    public function test_user_can_delete_book_from_list(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $user->onRead()->attach($book->isbn);

        $response = $this->delete('api/progress/' . $book->isbn);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books_in_progress', [
            'book_isbn' => $book->isbn,
            'user_id' => $user->id,
        ]);
    }

    /**
     * User can get list of their books in cart
     */
    public function test_user_can_get_progress_list(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $books = Book::factory()->count(3)->create();

        $user->onRead()->saveMany($books);

        $response = $this->get('api/progress');

        $response->assertJsonStructure([
                'data' => [
                    ['isbn'],
                    ['isbn'],
                    ['isbn'],
                ]
            ]
        );
    }
}
