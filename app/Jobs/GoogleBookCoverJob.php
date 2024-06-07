<?php

namespace App\Jobs;

use App\Helpers\GoogleBookApiHelper;
use App\Helpers\ImageUploadHelper;
use App\Models\Book;
use App\Models\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GoogleBookCoverJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Book $book)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleBookApiHelper $googleHelper, ImageUploadHelper $imageHelper): void
    {
        $data = $googleHelper->getData([':isbn' => $this->book->isbn]);

        if ($data['totalItems'] !== 0) {
            $link = $data['items'][0]['volumeInfo']['imageLinks']['thumbnail'];

            $filename = $imageHelper->uploadFromLink($link);

            if ($filename) {
                $this->book->image()->save(Image::from($filename));
            }
        }
    }
}
