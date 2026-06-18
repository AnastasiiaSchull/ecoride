<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private string $targetDirectory;
    
    public function __construct(string $targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function uploadProfilePicture(UploadedFile $file, string $targetDir, string $prefix = 'user'): string
    {
        $extension = $file->guessExtension() ?: 'png';

        $safeName = sprintf(
            '%s_%s_%s.%s',
            $prefix,
            time(),
            bin2hex(random_bytes(5)),
            $extension
        );

        $file->move($targetDir, $safeName);

        return $safeName;
    }

    
}