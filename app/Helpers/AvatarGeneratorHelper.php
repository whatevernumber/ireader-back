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
    /**
     * @param string $string
     * @param string $folder
     * @return string
     * @throws \Exception
     */
    public function createAvatar(string $string, string $folder): string
    {
        $avatar = $this->generateAvatar($string);

        try {
            $filename = $this->saveAvatar($avatar, $folder);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $filename;
    }

    /**
     * @param string $string
     * @return string
     * @throws \Bitverse\Identicon\Color\WrongColorFormatException
     */
    public function generateAvatar(string $string): string
    {
        $generator = new PixelsGenerator();
        $generator->setBackgroundColor(Color::parseHex('#EEEEEE'));

        $identicon = new Identicon(new MD5Preprocessor(), $generator);

        return $identicon->getIcon($string);
    }

    /**
     * @param mixed $avatar
     * @param string $folder
     * @throws UnableToWriteFile
     * @return string
     */
    public function saveAvatar(mixed $avatar, string $folder)
    {
        $name = uniqid('ibook_avatar') . '.' . 'svg';
        $path = $folder . DIRECTORY_SEPARATOR . $name;

        try {
            Storage::disk('public')->put($path, $avatar);
        } catch (\Exception $e) {
            throw new UnableToWriteFile('Ошибка записи');
        }

        return $name;
    }
}
