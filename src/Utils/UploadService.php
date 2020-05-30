<?php

namespace App\Utils;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

class UploadService
{
    private $uploadDirectory;

    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadDirectory = $params->get('upload_directory');
    }


    public function uploadImage(File $file)
    {
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        $file->move($this->uploadDirectory, $fileName);
        $fileFolder = '/uploads/'. $fileName;
        return $fileFolder;
    }
}
