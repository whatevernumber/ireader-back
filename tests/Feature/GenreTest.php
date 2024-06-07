<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * New genre can be added
     */
    public function test_genre_can_be_added(): void
    {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user, ['update']);

        $genre = Genre::factory()->make();

        $this->postJson('api/genres', $genre->toArray());

        $this->assertDatabaseHas('genres',
            [
                'value' => $genre->value,
            ]);
    }

    /**
     * Existing genre can be edited
     */
    public function test_genre_can_be_edited(): void
    {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user, ['update']);

        $genre = Genre::factory()->create();
        $newGenre = Genre::factory()->make();

        $this->patchJson('api/genres/' . $genre->id, $newGenre->toArray());

        $this->assertDatabaseHas('genres',
            [
                'id' => $genre->id,
                'value' => $newGenre->value,
            ]);
    }

    /**
     * Genre can be deleted
     */
    public function test_genre_can_be_deleted(): void
    {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user, ['update']);

        $genre = Genre::factory()->create();

        $this->delete('api/genres/' . $genre->id);

        $this->assertDatabaseMissing('genres',
            [
                'id' => $genre->id,
                'value' => $genre->value,
            ]);
    }

    /**
     * Genre with books can't be deleted if there are books using it
     */
    public function test_genre_with_books_cannot_be_deleted(): void
    {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user, ['update']);

        $genre = Genre::factory()->create();

        Book::factory()->hasAttached($genre)->create();

        $response = $this->delete('api/genres/' . $genre->id);

        $response->assertStatus(409);

        $this->assertDatabaseHas('genres',
            [
                'id' => $genre->id,
                'value' => $genre->value,
            ]);
    }

    /**
     * Author can't be deleted by non-admin user
     */
    public function test_genre_cannot_be_deleted_by_not_admin(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $genre = Genre::factory()->create();

        $response = $this->delete('api/genres/' . $genre->id);

        $response->assertStatus(403);

        $this->assertDatabaseHas('genres',
            [
                'id' => $genre->id,
                'value' => $genre->value,
            ]);
    }

    /**
     * Genre can't be edited by non-admin user
     */
    public function test_genre_cannot_be_edited_by_not_admin(): void
    {

        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $genre = Genre::factory()->create();
        $newGenre = Genre::factory()->make();

        $response = $this->patchJson('api/genres/' . $genre->id, $newGenre->toArray());

        $response->assertStatus(403);

        $this->assertDatabaseHas('genres',
            [
                'id' => $genre->id,
                'value' => $genre->value,
            ]);
    }

    /**
     * Genre list can be obtained
     */
    public function test_genre_list_can_be_obtained(): void
    {

        Genre::factory()->count(3)->create();

        $response = $this->get('api/genres');

        $response->assertJsonStructure([
            'data' => [
                [
                   'id',
                   'value',
                ],
                [
                    'id',
                    'value',
                ],
                [
                    'id',
                    'value',
                ],
            ]
        ]);
    }

    /**
     * Specific Genre can be obtained
     */
    public function test_genre_can_be_obtained(): void
    {

        $genre = Genre::factory()->create();

        $response = $this->get('api/genres/' . $genre->id);

        $response->assertJson([
            'data' =>
                [
                    'id' => $genre->id,
                    'value' => $genre->value,
                ],
        ]);
    }
}
