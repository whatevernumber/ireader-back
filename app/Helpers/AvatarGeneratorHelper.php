<?php

namespace App\Helpers;

use Bitverse\Identicon\Color\Color;
use Bitverse\Identicon\Generator\PixelsGenerator;
use Bitverse\Identicon\Identicon;
use Bitverse\Identicon\Preprocessor\MD5Preprocessor;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToWriteFile;

class AvatarGeneratorHelper
{
    public function __construct(protected Identicon $avatarGenerator, protected ImageHelper $imageHelper)
    {}

    /**
     * @param string $string
     * @param string $folder
     * @return string
     * @throws \Exception
     */
    public function createAvatar(string $string, string $folder): string
    {
        $avatar = $this->avatarGenerator->getIcon($string);

        try {
            $filename = $this->imageHelper->store($avatar, $folder, env('GENERATED_AVATAR_PREFIX'));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $filename;
    }
}
