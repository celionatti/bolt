<?php

declare(strict_types=1);

/**
 * ==========================================
 * Bolt - BoltUpload ========================
 * ==========================================
 */

namespace celionatti\Bolt\Illuminate\Support;

class BoltUpload
{
    const DEFAULT_MAX_FILE_SIZE = 10485760; // 10 MB

    protected $uploadDir;
    protected $allowedFileTypes = [];
    protected $maxFileSize = self::DEFAULT_MAX_FILE_SIZE;
    protected $overwriteExisting = false;
    protected $required = true;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = $uploadDir;
        $this->ensureUploadDirectoryExists($uploadDir);
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

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    public function uploadFile(string $fileInputName, bool $rename = true): array
    {
        if ($this->required && (!$this->hasFile($fileInputName))) {
            return ['error' => 'No file found for upload.'];
        }

        $file = $_FILES[$fileInputName];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Error during file upload.'];
        }

        return $this->handleValidUpload($file, $rename);
    }

    public function deleteFile($filePath): bool
    {
        if (file_exists($filePath)) {
            unlink($filePath);
            return true;
        }

        return false;
    }

    protected function ensureUploadDirectoryExists(string $uploadDir): void
    {
        if (!file_exists($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            throw new \RuntimeException('Failed to create the upload directory.');
        }
    }

    protected function hasFile(string $fileInputName): bool
    {
        return isset($_FILES[$fileInputName]) && !empty($_FILES[$fileInputName]['name']);
    }

    protected function handleValidUpload(array $file, bool $rename): array
    {
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

        return $this->moveFileToUploadDirectory($fileTmpPath, $uploadPath);
    }

    protected function moveFileToUploadDirectory(string $fileTmpPath, string $uploadPath): array
    {
        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
            return ['success' => 'File uploaded successfully.', 'path' => $uploadPath];
        }

        return ['error' => 'File upload failed.'];
    }

    protected function generateUniqueFileName(string $originalFileName): string
    {
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $fileName = pathinfo($originalFileName, PATHINFO_FILENAME);
        $fileName = substr($fileName, 0, 15);

        return $fileName . '_' . uniqid() . '.' . $extension;
    }

    private function formatBytes(int $bytes, $precision = 2, $decimalSeparator = '.', $thousandsSeparator = ','): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        $bytes = max($bytes, 0);
        $pow = floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);

        $formattedSize = number_format($bytes / (1024 ** $pow), $precision, $decimalSeparator, $thousandsSeparator);

        return $formattedSize . ' ' . $units[$pow];
    }
}
