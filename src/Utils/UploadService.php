<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\Request;

class UploadService
{
    public function uploadImage($form, $field, $directory)
    {
        $file = $form->get($field)->getData();
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        $file->move($directory, $fileName);

        return $fileName;
    }
}
