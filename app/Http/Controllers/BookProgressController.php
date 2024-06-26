<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookProgressController extends Controller
{

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return BookResource::collection($request->user()->onRead()->get());
    }

    /**
     * @throws NotFoundHttpException
     * @throws ConflictHttpException
     */
    public function create(Request $request, string $isbn): Response
    {
        if (str_ends_with($isbn, 'X')) {
            $isbn = str_replace('X', '', $isbn);
        }

        if (!$book = Book::find($isbn)) {
            throw new NotFoundHttpException('Такой книги не существует', null, 404);
        }

        if ($request->user()->finishedBooks()->get()->contains($book)) {
            throw new ConflictHttpException('Книга уже прочитана', null, 409);
        }

        if ($request->user()->onRead()->get()->contains($book)) {
            throw new ConflictHttpException('Книга уже в списке читаемых', null, 409);
        }

        $request->user()->onRead()->attach($book->isbn);

        return response('', 200);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function delete(Request $request, string $isbn): Response
    {
        if (str_ends_with($isbn, 'X')) {
            $isbn = str_replace('X', '', $isbn);
        }

        if (!$book = Book::find($isbn)) {
            throw new NotFoundHttpException('Такой книги не существует', null, 404);
        }

        if (!$request->user()->onRead()->get()->contains($book)) {
            throw new NotFoundHttpException('Такой книги нет в списке читаемых', null, 404);
        }

        $request->user()->onRead()->detach($book->isbn);

        return response('', 204);
    }
}
