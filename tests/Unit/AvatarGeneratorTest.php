<?php

namespace Tests\Unit;

use App\Helpers\AvatarGeneratorHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AvatarGeneratorTest extends TestCase
{
    /**
     * Checks if the Avatar was generated and saved to the disk
     */
    public function test_avatar_is_generated(): void
    {
        Storage::fake('public');

        $helper = new AvatarGeneratorHelper();
        $icon = $helper->createAvatar(Str::random(20), env('PROFILE_IMAGE_PATH'));

        $this->assertTrue(Storage::disk('public')->fileExists(env('PROFILE_IMAGE_PATH') . DIRECTORY_SEPARATOR . $icon));
    }
}
