<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FavouriteController extends Controller
{

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return BookResource::collection($request->user()->favourites()->get());
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

        if ($request->user()->favourites()->get()->contains($book)) {
            throw new ConflictHttpException('Книга уже добавлена в избранное', null, 409);
        }

        $request->user()->favourites()->save($book);

        return response('', 200);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ConflictHttpException
     */
    public function delete(Request $request, string $isbn): Response
    {
        if (str_ends_with($isbn, 'X')) {
            $isbn = str_replace('X', '', $isbn);
        }

        if (!$book = Book::find($isbn)) {
            throw new NotFoundHttpException('Такой книги не существует', null, 404);
        }

        if (!$request->user()->favourites()->get()->contains($book)) {
            throw new ConflictHttpException('Такой книги нет в избранных', null, 409);
        }

        $request->user()->favourites()->detach($book->isbn);

        return response('', 204);
    }
}
