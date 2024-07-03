<?php

namespace Tests\Feature;

use App\Helpers\GoogleBookApiHelper;
use App\Helpers\ImageUploadHelper;
use App\Jobs\GoogleBookCoverJob;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

class GoogleBookCoverJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Image from Google Book API can be stored and saved
     */
    public function test_image_is_saved_using_google_job(): void
    {
        Queue::fake();

        $book = Book::factory()->create();
        $imageUrl = fake()->imageUrl(200, 200, null, true, null, false, 'jpg');
        $data = [
            'totalItems' => 1,
            'items' => [
                0 => [
                    'volumeInfo' => [
                        'imageLinks' => [
                            'thumbnail' => $imageUrl
                        ]
                    ]
                ]
            ]
        ];

        $imageName = fake()->text(20);

        $mockData = $this->mock(GoogleBookApiHelper::class, function (MockInterface $mock) use ($data) {
            $mock->shouldReceive('getData')->andReturn($data);
        });

        $mockImage = $this->mock(ImageUploadHelper::class, function (MockInterface $mock) use ($imageName) {
           $mock->shouldReceive('uploadFromLink')->andReturn($imageName);
        });

        (new GoogleBookCoverJob($book))->handle($mockData, $mockImage);

        $this->assertDatabaseHas('images',[
            'book_isbn' => $book->isbn,
            'image' => $imageName,
        ]);
    }

    /**
     * GoogleBookCoverJob is pushed when new book created
     */
    public function test_queue_pushed_when_new_book_added()
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

        $this->assertDatabaseHas('books', [
                'isbn' => $book->isbn,
                'title' => $book->title,
                'description' => $book->description,
                'price' => $book->price,
                'published_year' => $book->published_year,
            ]);

        Queue::assertPushed(GoogleBookCoverJob::class);
    }

    /**
     * GoogleBookCoverJob is pushed when isbn of the book is updated
     */
    public function test_queue_pushed_when_isbn_updated(): void
    {
        Queue::fake();

        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user, ['update']);

        $genre = Genre::factory()->create();
        $author = Author::factory()->create();

        $book = Book::factory()->hasAttached($genre)->hasAttached($author)->create();
        $updatedBook = Book::factory()->make();

        $this->patchJson('api/books/' . $book->isbn, [
            'isbn' => $updatedBook->isbn,
            'title' => $updatedBook->title,
            'description' => $updatedBook->description,
            'price' => $updatedBook->price,
            'published_year' => $updatedBook->published_year,
            'authors' => [$author->name],
            'genres' => [$genre->value],
        ]);

        Queue::assertPushed(GoogleBookCoverJob::class);
    }

    /**
     * GoogleBookCoverJob is not pushed when the isbn is not updated
     */
    public function test_queue_not_pushed_when_isbn_not_updated(): void
    {
        Queue::fake();

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

        Queue::assertNotPushed(GoogleBookCoverJob::class);
    }
}
