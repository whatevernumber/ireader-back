<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\UnableToDeleteFile;

abstract class FileHelper
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
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Saves the given file and returns its name
     * @param mixed $file
     * @param string $folder
     * @param string $prefix
     * @return string
     */
    abstract protected function store(mixed $file, string $folder, string $prefix): string;

    /**
     * Removes file at the given path
     * @param string $filename
     * @param string $disk
     * @param string $path
     * @return void
     */
    public function delete(string $filename, string $path, string $disk = 'public'): void
    {
        $filePath = $path . DIRECTORY_SEPARATOR . $filename;

        try {
            Storage::disk($disk)->delete($filePath);
        } catch (\Exception $e) {
            throw new UnableToDeleteFile();
        }
    }

    /**
     * Gets image's mime
     * @param $file mixed
     * @return string
     */
    protected function getMime(mixed $file): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        return $finfo->buffer($file);
    }

    /**
     * Gets image's extension
     * @param $file mixed
     * @return string
     */
    protected function getExt(mixed $file): string
    {
        $finfo = finfo_open(FILEINFO_EXTENSION);
        return Str::of($finfo->buffer($file))->before('/');
    }
}
