<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * New Author can be added
     */
    public function test_author_can_be_added(): void
    {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user, ['update']);

        $author = Author::factory()->make();

        $this->postJson('api/authors', $author->toArray());

        $this->assertDatabaseHas('authors', [
           'name' => $author->name,
        ]);
    }

    /**
     * Existing Author can be edited
     */
    public function test_author_can_be_edited(): void
    {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user, ['update']);

        $author = Author::factory()->create();
        $newAuthor = Author::factory()->make();

        $this->patchJson('api/authors/' . $author->id, $newAuthor->toArray());

        $this->assertDatabaseHas('authors', [
                'id' => $author->id,
                'name' => $newAuthor->name,
            ]);
    }

    /**
     * Author can be deleted
     */
    public function test_author_can_be_deleted(): void
    {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user, ['update']);

        $author = Author::factory()->create();

        $this->delete('api/authors/' . $author->id);

        $this->assertDatabaseMissing('authors', [
                'id' => $author->id,
                'name' => $author->name,
            ]);
    }

    /**
     * Author with books can't be deleted if there are books using it
     */
    public function test_author_with_books_cannot_be_deleted(): void
    {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user, ['update']);

        $author = Author::factory()->create();

        Book::factory()->hasAttached($author)->create();

        $response = $this->delete('api/authors/' . $author->id);

        $response->assertStatus(409);

        $this->assertDatabaseHas('authors', [
                'id' => $author->id,
                'name' => $author->name,
            ]);
    }

    /**
     * Author can't be deleted by non-admin user
     */
    public function test_author_cannot_be_deleted_by_not_admin(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $author = Author::factory()->create();

        $response = $this->delete('api/authors/' . $author->id);

        $response->assertStatus(403);

        $this->assertDatabaseHas('authors', [
                'id' => $author->id,
                'name' => $author->name,
            ]);
    }

    /**
     * Author can't be edited by non-admin user
     */
    public function test_author_cannot_be_edited_by_not_admin(): void
    {

        $user = User::factory()->create();

        Sanctum::actingAs($user, ['see']);

        $author = Author::factory()->create();
        $newAuthor = Author::factory()->make();

        $response = $this->patchJson('api/authors/' . $author->id, $newAuthor->toArray());

        $response->assertStatus(403);

        $this->assertDatabaseHas('authors', [
                'id' => $author->id,
                'name' => $author->name,
            ]);
    }

    /**
     * Author list can be obtained
     */
    public function test_author_list_can_be_obtained(): void
    {

        Author::factory()->count(3)->create();

        $response = $this->get('api/authors');

        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                ],
                [
                    'id',
                    'name',
                ],
                [
                    'id',
                    'name',
                ],
            ]
        ]);
    }

    /**
     * Specific author can be obtained
     */
    public function test_author_can_be_obtained(): void
    {

        $author = Author::factory()->create();

        $response = $this->get('api/authors/' . $author->id);

        $response->assertJson([
            'data' => [
                'id' => $author->id,
                'name' => $author->name,
            ]
        ]);
    }
}
