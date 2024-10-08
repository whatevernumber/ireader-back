<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Helpers\IsbnHelper;
use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Jobs\GoogleBookCoverJob;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Image;
use Exception;
use http\Exception\BadMethodCallException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Manticoresearch\Client;
use Manticoresearch\Index;
use PDO;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookController extends Controller
{
    //

    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return BookResource::collection(Book::paginate(env('BOOKS_PER_PAGE')));
     }

    /**
     * @throws NotFoundHttpException
     */
    public function get(string $isbn): BookResource
    {
        if (str_ends_with($isbn, 'X')) {
            $isbn = str_replace('X', '', $isbn);
        }

        if (!$book = Book::find($isbn)) {
            throw new NotFoundHttpException('Такой книги не существует', null, 404);
        }

        return new BookResource($book);
    }

    /**
     * @throws ValidationException
     * @throws ConflictHttpException
     * @throws Exception
     */
    public function create(BookRequest $request, IsbnHelper $isbnHelper): BookResource
    {
        $data = $request->validated();

        // removes everything except numbers
        $data['isbn'] = preg_replace('/([ISBN:-]|\s)/', '', $data['isbn']);

        // validates the isbn checksum
        $isValid = $isbnHelper->checkIsbn($data['isbn']);

        if (!$isValid) {
            throw ValidationException::withMessages(['isbn' => 'Некорретный isbn']);
        }

        // if the number contains 'x', it will be removed and the flag in the db will be set
        if (str_ends_with($data['isbn'], 'X')) {
            $data['isbn'] = str_replace('X', '', $data['isbn']);
            $data['has_x'] = true;
        }

        // checks if the book with this isbn exists
        if (Book::find($data['isbn'])) {
            throw new ConflictHttpException('Книга с таким ISBN уже есть в системе', null, 409);
        }

        // Wrapped in a transaction to avoid a book being saved without its author/genre
        DB::beginTransaction();

        try {
            $book = Book::create($data);

            // handles book's authors
            foreach ($data['authors'] as $new_author) {
                $author = Author::firstOrCreate([
                    'name' => $new_author,
                ]);

                $book->authors()->save($author);
            }

            // handles book's genres
            foreach ($data['genres'] as $selectedGenre) {
                $genre = Genre::firstOrCreate([
                    'value' => $selectedGenre,
                ]);

                $book->genres()->save($genre);
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        DB::commit();

        $book->refresh();

        GoogleBookCoverJob::dispatch($book);

        return new BookResource($book);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ValidationException
     * @throws Exception
     */
    public function update(BookRequest $request, string $isbn, IsbnHelper $isbnHelper, ImageHelper $imageHelper): BookResource
    {
        $isbnChanged = false;

        // finds the book to change
        if (str_ends_with($isbn, 'X')) {
            $isbn = str_replace('X', '', $isbn);
        }

        if (!$book = Book::find($isbn)) {
            throw new NotFoundHttpException('Такой книги не существует', null, 404);
        }

        $data = $request->validated();

        // removes everything except numbers
        $data['isbn'] = preg_replace('/([ISBN:-]|\s)/', '', $data['isbn']);

        // if the isbn was changed, check if the new one is correct and free
        if ((int)($data['isbn']) !== $book->isbn) {
            $isbnChanged = true;
            // validates the isbn checksum
            $isbnIsValid = $isbnHelper->checkIsbn($data['isbn']);

            if (!$isbnIsValid) {
                throw ValidationException::withMessages(['isbn' => 'Некорретный isbn']);
            }

            // detaches all the relations
            $book->authors()->detach();
            $book->genres()->detach();
            $book->savedBy()->detach();
            $book->addedBy()->detach();
            $book->finishedBy()->detach();

            if ($book->image) {
                $imageHelper->delete($book->image->image, 'public', env('BOOK_COVER_PATH'));
                $book->image()->delete();
            }
        }

        // Wrapped in a transaction to avoid a book being saved without its author/genre
        DB::beginTransaction();

        try {
            $book->update($data);

            // takes existing relationships compares them to collections of received genres/authors
            $authors = $book->authors()->pluck('name')->flatten();
            $genres = $book->genres()->pluck('value')->flatten();

            $genreCollection = collect($data['authors']);
            $authorCollection = collect($data['genres']);

            // if there are new authors added
            if ($diff = $authorCollection->diff($authors)) {
                foreach ($diff as $author) {
                    $author = Author::firstOrCreate([
                        'name' => $author,
                    ]);

                    $book->authors()->save($author);
                }
            }

            // if some of old authors were erased
            if ($diff = $authors->diff($authorCollection)) {
                foreach ($diff as $author) {
                    $author = Author::where(['name' => $author])->first();
                    $book->authors()->detach($author->id);
                }
            }

            // if there are new genres added
            if ($diff = $genreCollection->diff($genres)) {
                foreach ($diff as $genre) {
                    $genre = Genre::firstOrCreate([
                        'value' => $genre,
                    ]);

                    $book->genres()->save($genre);
                }
            }

            // if some of old genres were erased
            if ($diff = $genres->diff($genreCollection)) {
                foreach ($diff as $genre) {
                    $genre = Genre::where(['value' => $genre])->first();
                    $book->genres()->detach($genre->id);
                }
            }

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        DB::commit();

        $book->refresh();

        // if isbn changed, the cover must be replaced
        if ($isbnChanged) {
            GoogleBookCoverJob::dispatch($book);
        }

        return new BookResource($book);
    }

    /**
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function delete(string $isbn, ImageHelper $imageHelper): Response
    {
        // finds the book to change
        if (str_ends_with($isbn, 'X')) {
            $isbn = str_replace('X', '', $isbn);
        }

        if (!$book = Book::find($isbn)) {
            throw new NotFoundHttpException('Такой книги не существует', null, 404);
        }

        DB::beginTransaction();

        try {
            $book->authors()->detach();
            $book->genres()->detach();
            $book->savedBy()->detach();
            $book->addedBy()->detach();
            $book->finishedBy()->detach();

            if ($book->image()->get()->isNotEmpty()) {
                $imageHelper->delete($book->image->image, env('BOOK_COVER_PATH'));
                $book->image()->delete();
            }

            $book->delete();
        } catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        DB::commit();

        return response('', 204);
    }

    /**
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function find(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $genre = $request->query('genre');
        $author = $request->query('author');
        $text = $request->query('text');

        // if there are no parameters in the query
        if (!$genre && !$author && !$text) {
            throw new BadRequestHttpException('Укажите критерии поиска', null, 400);
        }

        $books = Book::with('authors', 'genres');

        if ($text) {
            $books = $books->where('title', 'ILIKE', '%' . $text . '%');

            // if there is text in the query and no authors mentioned, try to search in the authors name as well
            if (!$author) {
                $books = $books->orWhereHas('authors', function ($q) use ($text) {
                    $q->where('name', 'ILIKE', '%' . $text . '%');
                });
            }
        }

        if ($genre) {
            $books = $books->whereHas('genres', function ($q) use ($genre) {
                $q->where('value', $genre);
            });
        }

        if ($author) {
            $books = $books->whereHas('authors', function ($q) use ($author) {
                $q->where('name', 'ILIKE', '%' . $author . '%');
            });
        }

        $books = $books->get();

        if ($books->isEmpty()) {
            throw new NotFoundHttpException('Нет подходящих результатов', null, 404);
        }

        return BookResource::collection($books);
    }

    public function searchManticore(string $query): mixed
    {
        $connection = DB::connection('manticore');
        $result = $connection->table('ibooks')->selectRaw('id, title, name')->whereRaw("MATCH(?)", $query)
                                    ->groupBy('id')->get();

        return json_encode($result);
    }

    public function getRandomBooks(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $completedBooks = Book::query()->selectRaw('isbn, title, description, pages, published_year, books.rate')->join('books_in_progress', 'isbn', '=', 'books_in_progress.book_isbn');
        $allBooks = Book::query()->selectRaw('isbn, title, description, pages, published_year, books.rate')
            ->join('finished_books', 'isbn', '=', 'finished_books.book_isbn')
            ->union($completedBooks);

        $allBooks = Book::query()->fromSub($allBooks, 'sub')->selectRaw('*')->inRandomOrder()->limit(5)->get();

        return BookResource::collection($allBooks);
    }
}
