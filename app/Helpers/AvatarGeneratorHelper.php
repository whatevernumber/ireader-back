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
    {

    }
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
//            $filename = $this->saveAvatar($avatar, $folder);
            $filename = $this->imageHelper->store($avatar, $folder, env('GENERATED_AVATAR_PREFIX'));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $filename;
    }

//    /**
//     * @param mixed $avatar
//     * @param string $folder
//     * @throws UnableToWriteFile
//     * @return string
//     */
//    public function saveAvatar(mixed $avatar, string $folder)
//    {
//        $name = uniqid(env('GENERATED_AVATAR_PREFIX')) . '.' . 'svg';
//        $path = $folder . DIRECTORY_SEPARATOR . $name;
//
//        try {
//            Storage::disk('public')->put($path, $avatar);
//        } catch (\Exception $e) {
//            throw new UnableToWriteFile('Ошибка записи');
//        }
//
//        return $name;
//    }
}
