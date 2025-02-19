<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Handle file upload and return the stored path.
     */
    public function uploadFile(UploadedFile $file, string $directory = 'uploads'): string
    {
        return $file->store($directory, 'public');
    }

    /**
     * Delete a file from storage.
     */
    public function deleteFile(?string $filePath): void
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
    }

    /**
     * Check if a given path exists in storage.
     */
    public function fileExists(?string $filePath): bool
    {
        return $filePath && Storage::disk('public')->exists($filePath);
    }
}
