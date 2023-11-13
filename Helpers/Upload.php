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
        if (!isset($_FILES[$fileInputName]) || empty($_FILES[$fileInputName]['name'])) {
            return ['error' => 'No file found for upload.'];
        }

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

        // Limit the length of the file name to 100 characters
        $fileName = substr($fileName, 0, 15);

        $uniqueFileName = $fileName . '_' . uniqid() . '.' . $extension;

        return $uniqueFileName;
    }

    private function formatBytes(int $bytes, $precision = 2, $decimalSeparator = '.', $thousandsSeparator = ','): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        $bytes = max($bytes, 0);

        // Use a more precise logarithm function for accurate results
        $pow = floor(log($bytes, 1024));

        // Ensure the calculated unit is within the defined range
        $pow = min($pow, count($units) - 1);

        // Calculate the size with the specified precision
        $formattedSize = number_format($bytes / (1024 ** $pow), $precision, $decimalSeparator, $thousandsSeparator);

        return $formattedSize . ' ' . $units[$pow];
    }
}
