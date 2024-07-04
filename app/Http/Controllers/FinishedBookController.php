<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReadBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Notifications\BookRead;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FinishedBookController extends Controller
{
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query = $request->user()->finishedBooks();

        if ($request->query('page')) {
            $books = $query->paginate(env('BOOKS_PER_PAGE'));
        } else {
            $books = $query->get();
        }

        return BookResource::collection($books);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ConflictHttpException
     * @throws \Exception
     */
    public function create(ReadBookRequest $request, string $isbn): Response
    {
        $data = $request->validated();
        $days = null;

        if (str_ends_with($isbn, 'X')) {
            $isbn = str_replace('X', '', $isbn);
        }

        if (!$book = Book::find($isbn)) {
            throw new NotFoundHttpException('Такой книги не существует', null, 404);
        }

        $user = $request->user();

        $finishedBooks = $user->finishedBooks()->get();
        $booksInProgress = $user->onRead()->get();

        if ($finishedBooks->contains($book)) {
            throw new ConflictHttpException('Уже прочитана!', null, 409);
        }

        if ($booksInProgress->contains($book)) {
            $progressDate = $user->onRead()->where('isbn', $book->isbn)->first()->pivot->created_at;
            $origin = date_create();
            $target = date_create($progressDate);
            $interval = date_diff($origin, $target)->format('%a');

            if ($interval) {
                $days = $interval;
            } else {
                $days = 1;
            }

            $user->onRead()->detach($book->isbn);
        }

        $user->finishedBooks()->attach($book->isbn, [
            'comment' => $data['comment'] ?? '',
            'rate' => $data['rate'] ?? null,
            'completed_days' => $days ?? null,
        ]);

        try {
            $user->notify(new BookRead($book));
        } catch (\Exception $e) {
            // logs errors in the separate file
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/notification.log'),
            ])->error($e->getMessage());
        }

        return response('', 200);
    }

    public function update(ReadBookRequest $request, string $isbn) {

        $user = $request->user();
        $data = $request->validated();

        if (str_ends_with($isbn, 'X')) {
            $isbn = str_replace('X', '', $isbn);
        }

        if (!$book = Book::find($isbn)) {
            throw new NotFoundHttpException('Такой книги не существует', null, 404);
        }

        $finishedBooks = $user->finishedBooks()->get();

        if (!$finishedBooks->contains($book)) {
            throw new ConflictHttpException('Книга не отмечена прочитанной', null, 409);
        }

        $user->finishedBooks()->updateExistingPivot($book->isbn, [
           'rate' => $data['rate'],
           'comment' => $data['comment'],
        ]);

        $book->calculateRate();

        return response('', 201);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ConflictHttpException
     */
    public function delete(Request $request, string $isbn) {

        if (str_ends_with($isbn, 'X')) {
            $isbn = str_replace('X', '', $isbn);
        }

        if (!$book = Book::find($isbn)) {
            throw new NotFoundHttpException('Такой книги не существует', null, 404);
        }

        if (!$request->user()->finishedBooks()->get()->contains($book)) {
            throw new ConflictHttpException('Такой книги нет в списке прочитанного', null, 409);
        }

        $request->user()->finishedBooks()->detach($book->isbn);

        return response('', 204);
    }
}
