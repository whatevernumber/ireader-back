<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenreRequest;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class GenreController extends Controller
{

    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return GenreResource::collection(Genre::all());
    }

    public function get(Genre $genre): GenreResource
    {
        return new GenreResource($genre);
    }

    public function create(GenreRequest $request): GenreResource
    {
        $data = $request->validated();
        $genre = Genre::create($data);

        return new GenreResource($genre);
    }

    public function update(GenreRequest $request, Genre $genre): GenreResource
    {
        $data = $request->validated();
        $genre->fill($data);
        $genre->save();

        return new GenreResource($genre);
    }

    /**
     * @throws ConflictHttpException
     */
    public function delete(Genre $genre): Response
    {
        if (!$genre->books()->get()->isEmpty()) {
            throw new ConflictHttpException('В системе остались книги с этим жанром. Сначала измените его', null, 409);
        }

        $genre->delete();
        return response('', 204);
    }
}
