<?php

declare(strict_types=1);

/**
 * ==========================================
 * Bolt - Upload ============================
 * ==========================================
 */

namespace celionatti\Bolt\Illuminate\Support;

use Exception;
use InvalidArgumentException;

class Upload
{
    protected string $uploadDir;
    protected int $maxFileSize;
    protected array $allowedFileTypes;
    protected array $beforeUploadCallbacks = [];
    protected array $afterUploadCallbacks = [];

    public function __construct(string $uploadDir, int $maxFileSize = 10485760, array $allowedFileTypes = ['image/jpeg', 'image/png'])
    {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->maxFileSize = $maxFileSize;
        $this->allowedFileTypes = $allowedFileTypes;
        $this->ensureUploadDirectoryExists($this->uploadDir);
    }

    protected function ensureUploadDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    public function uploadFile(string $fileInputName, bool $rename = true): array
    {
        $file = $_FILES[$fileInputName];
        $this->validateFile($fileInputName);

        $filename = $rename ? uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION) : $file['name'];
        $filePath = $this->uploadDir . $filename;

        $this->runBeforeUploadCallbacks($filename);

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('File upload failed.');
        }

        $this->runAfterUploadCallbacks($filename);
        $this->logUpload($filename);

        return ['success' => true, 'file' => $filePath, 'message' => 'File uploaded successfully.'];
    }

    public function uploadMultiple(array $fileInputNames, bool $rename = true): array
    {
        $uploadedFiles = [];
        foreach ($fileInputNames as $fileInputName) {
            $uploadedFiles[] = $this->uploadFile($fileInputName, $rename);
        }
        return $uploadedFiles;
    }

    public function uploadChunk(string $fileInputName, int $chunkIndex, int $totalChunks, string $uniqueId): array
    {
        $chunkDir = $this->uploadDir . 'chunks/' . $uniqueId . '/';
        $this->ensureUploadDirectoryExists($chunkDir);

        $file = $_FILES[$fileInputName];
        $chunkFile = $chunkDir . $chunkIndex;

        if (!move_uploaded_file($file['tmp_name'], $chunkFile)) {
            return ['error' => 'Failed to upload chunk.'];
        }

        if ($chunkIndex == $totalChunks - 1) {
            return $this->mergeChunks($chunkDir, $file['name'], $uniqueId);
        }

        return ['success' => true, 'message' => 'Chunk uploaded successfully.'];
    }

    protected function mergeChunks(string $chunkDir, string $fileName, string $uniqueId): array
    {
        $finalPath = $this->uploadDir . $fileName;
        $outputFile = fopen($finalPath, 'wb');

        foreach (glob($chunkDir . '*') as $chunk) {
            fwrite($outputFile, file_get_contents($chunk));
            unlink($chunk);
        }

        fclose($outputFile);
        rmdir($chunkDir);

        return ['success' => true, 'message' => 'File uploaded successfully.', 'file' => $finalPath];
    }

    public function generateThumbnail(string $filePath, int $width, int $height, string $thumbDir = 'thumbnails/'): string
    {
        $imageInfo = getimagesize($filePath);
        $mime = $imageInfo['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $srcImage = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $srcImage = imagecreatefrompng($filePath);
                break;
            default:
                throw new Exception('Unsupported image type.');
        }

        $thumbnail = imagecreatetruecolor($width, $height);
        imagecopyresampled($thumbnail, $srcImage, 0, 0, 0, 0, $width, $height, $imageInfo[0], $imageInfo[1]);

        $thumbnailPath = $this->uploadDir . $thumbDir . basename($filePath);
        imagejpeg($thumbnail, $thumbnailPath);

        imagedestroy($srcImage);
        imagedestroy($thumbnail);

        return $thumbnailPath;
    }

    public function addWatermark(string $filePath, string $watermarkImage): void
    {
        $imageInfo = getimagesize($filePath);
        $mime = $imageInfo['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filePath);
                break;
            default:
                throw new Exception('Unsupported image type.');
        }

        $watermark = imagecreatefrompng($watermarkImage);
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        $x = imagesx($image) - $watermarkWidth - 10;
        $y = imagesy($image) - $watermarkHeight - 10;

        imagecopy($image, $watermark, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight);
        imagejpeg($image, $filePath);

        imagedestroy($image);
        imagedestroy($watermark);
    }

    public function compressImage(string $filePath, int $quality = 75): string
    {
        $imageInfo = getimagesize($filePath);
        $mime = $imageInfo['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filePath);
                imagejpeg($image, $filePath, $quality);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filePath);
                imagepng($image, $filePath, round($quality / 10));
                break;
            default:
                throw new Exception('Unsupported image type.');
        }

        return $filePath;
    }

    public function validateFile(string $fileInputName): bool
    {
        $file = $_FILES[$fileInputName];

        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File exceeds maximum allowed size.');
        }

        $fileMimeType = mime_content_type($file['tmp_name']);
        if (!in_array($fileMimeType, $this->allowedFileTypes)) {
            throw new Exception('Invalid file type.');
        }

        if (in_array($fileMimeType, ['text/x-php', 'application/x-executable'])) {
            throw new Exception('Uploading dangerous file types is prohibited.');
        }

        return true;
    }

    protected function logUpload(string $filename): void
    {
        $logFile = $this->uploadDir . 'upload.log';
        $logMessage = sprintf("%s - File uploaded: %s\n", date('Y-m-d H:i:s'), $filename);
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    public function checkFileIntegrity(string $filePath, string $expectedHash, string $hashAlgo = 'sha256'): bool
    {
        $fileHash = hash_file($hashAlgo, $filePath);
        return $fileHash === $expectedHash;
    }

    public function encryptFile(string $filePath, string $encryptionKey): string
    {
        $data = file_get_contents($filePath);
        $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $encryptionKey, 0, $this->generateIV());

        $encryptedPath = $filePath . '.enc';
        file_put_contents($encryptedPath, $encryptedData);

        return $encryptedPath;
    }

    protected function generateIV(): string
    {
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    }

    public function beforeUpload(callable $callback): void
    {
        $this->beforeUploadCallbacks[] = $callback;
    }

    public function afterUpload(callable $callback): void
    {
        $this->afterUploadCallbacks[] = $callback;
    }

    protected function runBeforeUploadCallbacks(string $filename): void
    {
        foreach ($this->beforeUploadCallbacks as $callback) {
            call_user_func($callback, $filename);
        }
    }

    protected function runAfterUploadCallbacks(string $filename): void
    {
        foreach ($this->afterUploadCallbacks as $callback) {
            call_user_func($callback, $filename);
        }
    }
}
