<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    use HasFactory;

    public $fillable = [
        'image'
    ];

    public $timestamps = false;

    /**
     * Creates new instance of self
     * @param string $name
     * @return Image
     */
    public static function from(string $name): Image
    {
        $newImage = new self();
        $newImage->image = $name;
        return $newImage;
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
