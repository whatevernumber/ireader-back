<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ImageUploadHelper extends FileHelper
{
    /**
     * @param $link string
     * @param $folder string
     * @return string|bool
     * @throws FileException
     * @throws \Exception
     */
    public function uploadFromLink(string $link, string $folder): string
    {
        $response = Http::get($link);

        if ($response->ok()) {
            try {
                $file = $response->body();
                $mime = $this->getMime($file);

                if (!in_array($mime, self::ALLOWED_MIME_TYPES)) {
                    throw new FileException('Недопустимый тип файла');
                }

                return $this->store($file, $folder);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        } else {
            throw new \Exception('Что-то пошло не так', 500);
        }
    }

    /**
     * @inheritDoc
     * @throws ExtensionFileException
     * @throws UnableToWriteFile
     */
    protected function store(mixed $file, string $folder): string
    {
        $extension = $this->getExt($file);

        if (!$extension) {
            throw new ExtensionFileException('Ошибка получения расширения');
        }

        if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS)) {
            throw new ExtensionFileException('Неверный тип расширения');
        }

        $name = uniqid(env('BOOK_IMAGE_PREFIX')) . '.' . $extension;
        $path = $folder . DIRECTORY_SEPARATOR . $name;

        try {
            Storage::disk('public')->put($path, $file);
        } catch (\Exception $e) {
            throw new UnableToWriteFile('Ошибка записи');
        }

        return $name;
    }
}
