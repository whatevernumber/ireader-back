<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'isbn' => $this->when($this->isbn, function () {
                if ($this->has_x) {
                    return $this->isbn . 'X';
                } else {
                    return $this->isbn;
                }
            }),
            'title' => $this->title,
            'description' => $this->description,
            'pages' => $this->pages,
            'price' => $this->price,
            'published_year' => $this->published_year,
            'authors' => AuthorResource::collection($this->authors),
            'genres' => GenreResource::collection($this->genres),
            'image' => new ImageResource($this->image),
        ];
    }
}
