<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;
    /**
     * User can add book in cart
     */
    public function test_user_can_add_book_to_cart(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $response = $this->post('api/cart/' . $book->isbn);

        $response->assertStatus(200);

        $this->assertDatabaseHas('carts', [
            'book_isbn' => $book->isbn,
            'user_id' => $user->id,
        ]);
    }

    /**
     * User can't add existing in the cart book again
     */
    public function test_user_cannot_add_existing_book_to_cart(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $user->cart()->save($book);

        $response = $this->post('api/cart/' . $book->isbn);

        $response->assertStatus(409);
    }

    /**
     * User can't add already purchased book in the cart book again
     */
    public function test_user_cannot_add_purchased_book_to_cart(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $user->purchases()->attach($book, ['price' => $book->price]);

        $response = $this->post('api/cart/' . $book->isbn);

        $response->assertStatus(409);
    }

    /**
     * User can delete book in cart
     */
    public function test_user_can_delete_book_from_cart(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $user->cart()->save($book);

        $response = $this->delete('api/cart/' . $book->isbn);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('carts', [
            'book_isbn' => $book->isbn,
            'user_id' => $user->id,
        ]);
    }

    /**
     * User can get list of his books in cart
     */
    public function test_user_can_get_cart(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $books = Book::factory()->count(3)->create();

        $user->cart()->saveMany($books);

        $response = $this->get('api/cart');

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
}
