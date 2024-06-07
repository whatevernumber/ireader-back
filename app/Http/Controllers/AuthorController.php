<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class AuthorController extends Controller
{

    public function index(): AnonymousResourceCollection
    {
        return AuthorResource::collection(Author::orderedByName()->paginate(env('AUTHORS_PER_PAGE')));
    }

    public function get(Author $author): AuthorResource
    {
        return new AuthorResource($author);
    }

    public function create(AuthorRequest $request): AuthorResource
    {
        $data = $request->validated();
        $author = Author::create($data);

        return new AuthorResource($author);
    }

    public function update(AuthorRequest $request, Author $author): AuthorResource
    {
        $data = $request->validated();
        $author->fill($data);
        $author->save();

        return new AuthorResource($author);
    }

    /**
     * @throws ConflictHttpException
     */
    public function delete(Author $author): Response
    {
        if (!$author->books()->get()->isEmpty()) {
            throw new ConflictHttpException('У автора есть книги!', null, 409);
        }

        $author->delete();
        return response('', 204);
    }

}
