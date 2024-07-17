<?php

namespace App\Jobs;

use App\Helpers\ImageHelper;
use App\Models\Book;
use App\Models\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BookCoverJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ImageHelper $imageHelper): void
    {
        $links = DB::table('book_cover_links')->limit(20)->get();

        if ($links->isNotEmpty()) {
            foreach ($links as $link) {

                if (Image::where('book_isbn', $link->book_isbn)->doesntExist()) {
                    $filename = $imageHelper->uploadFromLink($link->link, env('BOOK_COVER_PATH'), env('BOOK_IMAGE_PREFIX'));
                    if ($filename) {
                        $image = Image::from($filename);
                        $image->book_isbn = $link->book_isbn;
                        $image->save();
                    }
                }
                DB::table('book_cover_links')->where('link', $link->link)->delete();
            }
        }
    }
}
