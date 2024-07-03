<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavouriteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * User can add book in favourites
     */
    public function test_user_can_add_book_to_favourites(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $response = $this->post('api/favourites/' . $book->isbn);

        $response->assertStatus(200);

        $this->assertDatabaseHas('favourites', [
            'book_isbn' => $book->isbn,
            'user_id' => $user->id,
        ]);
    }

    /**
     * User can't add existing in the favourites book again
     */
    public function test_user_cannot_add_existing_book_to_favourites(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $user->favourites()->save($book);

        $response = $this->post('api/favourites/' . $book->isbn);

        $response->assertStatus(409);
    }

    /**
     * User can delete book from favourites
     */
    public function test_user_can_delete_book_from_favourites(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $user->favourites()->save($book);

        $response = $this->delete('api/favourites/' . $book->isbn);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('favourites', [
            'book_isbn' => $book->isbn,
            'user_id' => $user->id,
        ]);
    }

    /**
     * User can get list of his books in favourites
     */
    public function test_user_can_get_favourites(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $books = Book::factory()->count(3)->create();

        $user->favourites()->saveMany($books);

        $response = $this->get('api/favourites');

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
