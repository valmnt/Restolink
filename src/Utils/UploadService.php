<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService
{
    public function uploadImage(UploadedFile $file, $directory)
    {
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        $file->move($directory, $fileName);
        $fileFolder = '/uploads/'. $fileName;
        return $fileFolder;
    }
}
