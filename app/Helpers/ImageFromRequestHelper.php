<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;

class ImageFromRequestHelper extends FileHelper
{
    /**
     * @param UploadedFile $file
     * @param string $folder
     * @return string
     */
    public function saveAvatar(UploadedFile $file, string $folder): string
    {
        try {
            $file = $this->store($file, $folder);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $file;
    }

    /**
     * @param mixed $file
     * @throws ExtensionFileException
     * @throws UnableToWriteFile
     * @return string
     */
    protected function store(mixed $file, string $folder): string
    {
        $extension = $file->extension();

        if (!$extension) {
            throw new ExtensionFileException('Ошибка получения расширения');
        }

        if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS)) {
            throw new ExtensionFileException('Неверный тип расширения');
        }

        $name = uniqid('ibook_profile-');

        try {
            $file->storeAs($folder, $name);
        } catch (\Exception $e) {
            throw new UnableToWriteFile('Ошибка записи');
        }

        return $name . '.' . $extension;
    }
}
