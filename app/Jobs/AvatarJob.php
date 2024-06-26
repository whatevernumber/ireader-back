<?php

namespace App\Jobs;

use App\Helpers\AvatarGeneratorHelper;
use App\Helpers\ImageFromRequestHelper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AvatarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private User $user)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(AvatarGeneratorHelper $avatarHelper): void
    {
        $avatarName = $avatarHelper->createAvatar($this->user->name, env('PROFILE_IMAGE_PATH'));

        if ($avatarName) {
            $this->user->image = $avatarName;
            $this->user->save();
        }
    }
}
