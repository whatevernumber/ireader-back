<?php

namespace App\Console\Commands;

use App\Jobs\BookCoverJob;
use Illuminate\Console\Command;

class DownloadCover extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-cover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        BookCoverJob::dispatch();
    }
}
