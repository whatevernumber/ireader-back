<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ImageHelper extends FileHelper
{
    /**
     * @param $link string
     * @param $folder string
     * @return string|bool
     * @throws FileException
     * @throws \Exception
     */
    public function uploadFromLink(string $link, string $folder, string $prefix): string
    {
        $response = Http::get($link);

        if ($response->ok()) {
            try {
                $file = $response->body();
                $mime = $this->getMime($file);

                if (!in_array($mime, self::ALLOWED_MIME_TYPES)) {
                    throw new FileException('Недопустимый тип файла');
                }

                return $this->store($file, $folder, $prefix);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        } else {
            throw new \Exception('Что-то пошло не так', 500);
        }
    }

    /**
     * @param UploadedFile $file
     * @param string $folder
     * @return string
     * @throws \Exception
     */
    public function saveFromRequest(UploadedFile $file, string $folder, string $prefix): string
    {
        try {
            $file = $this->store($file, $folder, $prefix);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $file;
    }

    /**
     * @inheritDoc
     * @throws ExtensionFileException
     * @throws UnableToWriteFile
     */
    public function store(mixed $file, string $folder, string $prefix): string
    {
        $extension = $file instanceof UploadedFile ? $file->extension() : $this->getExt($file);
        $name = uniqid($prefix) . '.' . $extension;

        if (!$extension) {
            throw new ExtensionFileException('Ошибка получения расширения');
        }

        if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS)) {
            throw new ExtensionFileException('Неверный тип расширения');
        }

        $path = $folder . DIRECTORY_SEPARATOR;

        if ($file instanceof UploadedFile) {
            try {
                $file->storeAs($path, $name, 'public');
            } catch (\Exception $e) {
                throw new UnableToWriteFile('Ошибка записи');
            }
        } else {
            try {
                Storage::disk('public')->put(($path . DIRECTORY_SEPARATOR . $name), $file);
            } catch (\Exception $e) {
                throw new UnableToWriteFile('Ошибка записи');
            }
        }

        return $name;
    }
}
