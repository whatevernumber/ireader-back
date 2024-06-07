<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CartController extends Controller
{

    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return BookResource::collection(Auth::user()->cart()->get());
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

        if ($request->user()->cart()->get()->contains($book)) {
            throw new ConflictHttpException('Книга уже есть в корзине!', null, 409);
        }

        if ($request->user()->purchases()->get()->contains($book)) {
            throw new ConflictHttpException('Книга уже куплена', null, 409);
        }

        $request->user()->cart()->attach($book->isbn);

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

        if (!$request->user()->cart()->get()->contains($book)) {
            throw new NotFoundHttpException('Такой книги нет в корзине', null, 404);
        }

        $request->user()->cart()->detach($book->isbn);

        return response('', 204);
    }
}
