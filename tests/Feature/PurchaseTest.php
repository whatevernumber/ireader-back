<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * User can buy a book from the cart
     */
    public function test_user_can_buy_book(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $user->cart()->save($book);

        $response = $this->post('api/purchase');

        $response->assertStatus(200);

        $this->assertDatabaseHas('purchases',
        [
            'user_id' => $user->id,
            'book_isbn' => $book->isbn,
        ]);
    }

    /**
     * User can't use more bonus than he has
     */
    public function test_user_cannot_use_more_bonus_than_has(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $book = Book::factory()->create();

        $user->cart()->save($book);

        $response = $this->post('api/purchase/' . ($user->bonus + 100));

        $response->assertStatus(409);
    }

    /**
     * User can get list of his purchases
     */
    public function test_user_can_get_purchases(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $books = Book::factory()->count(3)->create();

        foreach ($books as $book) {
            $user->purchases()->attach($book, ['price' => $book->price]);
        }

        $response = $this->get('api/purchases');

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
