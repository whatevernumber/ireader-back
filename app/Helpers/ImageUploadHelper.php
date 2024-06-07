<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ImageUploadHelper extends FileHelper
{
    const ALLOWED_MIME_TYPES = [
        'image/jpg',
        'image/jpeg',
    ];

    const ALLOWED_IMAGE_EXTENSIONS = [
        'jpg',
        'jpeg',
    ];

    /**
     * @param $link string
     * @return string|bool
     * @throws FileException
     * @throws \Exception
     */
    public function uploadFromLink(string $link): string | bool
    {
        $response = Http::get($link);

        if ($response->ok()) {
            try {
                $file = $response->body();
                $mime = $this->getMime($file);

                if (!in_array($mime, self::ALLOWED_MIME_TYPES)) {
                    throw new FileException('Недопустимый тип файла');
                }

                return $this->store($file);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        } else {
            return false;
        }
    }

    /**
     * Saves the image to the disk
     * @param $file mixed
     * @return string
     * @throws ExtensionFileException
     * @throws UnableToWriteFile
     */
    protected function store(mixed $file): string
    {
        $extension = $this->getExt($file);

        if (!$extension) {
            throw new ExtensionFileException('Ошибка получения расширения');
        }

        if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS)) {
            throw new ExtensionFileException('Неверный тип расширения');
        }

        $name = uniqid('ibook-') . '.' . $extension;
        $path = env('BOOK_COVER_PATH') . DIRECTORY_SEPARATOR . $name;

        try {
            Storage::disk('public')->put($path, $file);
        } catch (\Exception $e) {
            throw new UnableToWriteFile('Ошибка записи');
        }

        return $name;
    }
}
