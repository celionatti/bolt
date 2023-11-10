<?php

declare(strict_types=1);

/**
 * ==========================================
 * Bolt - Upload ============================
 * ==========================================
 */

namespace celionatti\Bolt\Helpers;

class Upload
{
    const DEFAULT_MAX_FILE_SIZE = 10485760; // 10 MB

    protected $uploadDir;
    protected $allowedFileTypes = [];
    protected $maxFileSize = self::DEFAULT_MAX_FILE_SIZE;
    protected $overwriteExisting = false;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = $uploadDir;
        // Ensure the upload directory exists or create it if it doesn't.
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new \RuntimeException('Failed to create the upload directory.');
            }
        }
    }

    public function setAllowedFileTypes(array $allowedFileTypes): void
    {
        $this->allowedFileTypes = $allowedFileTypes;
    }

    public function setMaxFileSize(int $maxFileSize): void
    {
        $this->maxFileSize = $maxFileSize;
    }

    public function setOverwriteExisting(bool $overwriteExisting): void
    {
        $this->overwriteExisting = $overwriteExisting;
    }

    public function uploadFile(string $fileInputName, bool $rename = true): array
    {
        if (isset($_FILES[$fileInputName])) {
            $file = $_FILES[$fileInputName];

            if ($file['error'] === UPLOAD_ERR_OK) {
                $fileName = $rename ? $this->generateUniqueFileName($file['name']) : $file['name'];
                $fileTmpPath = $file['tmp_name'];
                $fileSize = $file['size'];

                if ($fileSize > $this->maxFileSize) {
                    return ['error' => 'File size exceeds the allowed limit. ' . $this->formatBytes($this->maxFileSize)];
                }

                $fileType = mime_content_type($fileTmpPath);

                if (!in_array($fileType, $this->allowedFileTypes)) {
                    return ['error' => 'Invalid file type.'];
                }

                $uploadPath = $this->uploadDir . '/' . $fileName;

                if (!$this->overwriteExisting && file_exists($uploadPath)) {
                    return ['error' => 'A file with the same name already exists.'];
                }

                if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                    return ['success' => 'File uploaded successfully.', 'path' => $uploadPath];
                } else {
                    return ['error' => 'File upload failed.'];
                }
            } else {
                return ['error' => 'Error during file upload.'];
            }
        } else {
            return ['error' => 'File not found.'];
        }
    }

    public function deleteFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
            return true;
        }

        return false;
    }

    protected function generateUniqueFileName($originalFileName)
    {
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $fileName = pathinfo($originalFileName, PATHINFO_FILENAME);
        $uniqueFileName = $fileName . '_' . uniqid() . '.' . $extension;

        return $uniqueFileName;
    }

    private function formatBytes(int $bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes, $precision) . $units[$pow];
    }
}
