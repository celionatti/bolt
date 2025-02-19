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

        if (!$this->ensureUploadDirectoryExists($this->uploadDir)) {
            throw new Exception("Failed to create upload directory: {$this->uploadDir}");
        }
    }

    protected function ensureUploadDirectoryExists(string $directory): bool
    {
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            return false;
        }
        return is_writable($directory);
    }

    public function uploadFile(string $fileInputName, bool $rename = true): array
    {
        if (!isset($_FILES[$fileInputName])) {
            return ['success' => false, 'message' => 'No file uploaded'];
        }

        $file = $_FILES[$fileInputName];

        if ($error = $this->validateUpload($file)) {
            return ['success' => false, 'message' => $error];
        }

        $filename = $rename
            ? uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION)
            : $this->sanitizeFilename($file['name']);

        $filePath = $this->uploadDir . $filename;

        if ($error = $this->runBeforeUploadCallbacks($filename)) {
            return ['success' => false, 'message' => $error];
        }

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['success' => false, 'message' => 'Failed to move uploaded file'];
        }

        $this->runAfterUploadCallbacks($filename);
        $this->logUpload($filename);

        return [
            'success' => true,
            'path' => $filePath,
            'filename' => $filename,
            'message' => 'File uploaded successfully'
        ];
    }

    protected function validateUpload(array $file): ?string
    {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server size limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit',
            UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
        ];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $errorMessages[$file['error']] ?? 'Unknown upload error';
        }

        if ($file['size'] > $this->maxFileSize) {
            return 'File exceeds maximum allowed size';
        }

        $mimeType = mime_content_type($file['tmp_name']);
        if (!in_array($mimeType, $this->allowedFileTypes)) {
            return 'Invalid file type. Allowed types: ' . implode(', ', $this->allowedFileTypes);
        }

        return null;
    }

    public function delete(string $filename): array
    {
        $filePath = realpath($this->uploadDir . $filename);

        if (!$filePath || strpos($filePath, realpath($this->uploadDir)) !== 0) {
            return ['success' => false, 'message' => 'Invalid file path'];
        }

        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'File not found'];
        }

        if (!unlink($filePath)) {
            return ['success' => false, 'message' => 'Failed to delete file'];
        }

        return ['success' => true, 'message' => 'File deleted successfully'];
    }

    public function uploadMultiple(array $fileInputNames, bool $rename = true): array
    {
        $results = [];
        foreach ($fileInputNames as $inputName) {
            $results[] = $this->uploadFile($inputName, $rename);
        }
        return $results;
    }

    protected function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_.]/', '', $filename);
    }

    public function beforeUpload(callable $callback): void
    {
        $this->beforeUploadCallbacks[] = $callback;
    }

    public function afterUpload(callable $callback): void
    {
        $this->afterUploadCallbacks[] = $callback;
    }

    protected function runBeforeUploadCallbacks(string $filename): ?string
    {
        foreach ($this->beforeUploadCallbacks as $callback) {
            $result = $callback($filename);
            if ($result !== null && $result !== true) {
                return is_string($result) ? $result : 'Upload canceled by callback';
            }
        }
        return null;
    }

    protected function runAfterUploadCallbacks(string $filename): void
    {
        foreach ($this->afterUploadCallbacks as $callback) {
            $callback($filename);
        }
    }

    protected function logUpload(string $filename): void
    {
        error_log("Uploaded file: {$filename} at " . date('Y-m-d H:i:s'));
    }

    public function uploadChunk(string $fileInputName, int $chunkIndex, int $totalChunks, string $uniqueId): array
    {
        if (!isset($_FILES[$fileInputName])) {
            return ['success' => false, 'message' => 'No chunk file uploaded'];
        }

        $chunkDir = $this->uploadDir . 'chunks/' . $uniqueId . '/';
        if (!$this->ensureUploadDirectoryExists($chunkDir)) {
            return ['success' => false, 'message' => 'Failed to create chunks directory'];
        }

        $file = $_FILES[$fileInputName];
        $chunkFile = $chunkDir . $chunkIndex;

        if (!move_uploaded_file($file['tmp_name'], $chunkFile)) {
            return ['success' => false, 'message' => 'Failed to move chunk file'];
        }

        if ($chunkIndex === $totalChunks - 1) {
            return $this->mergeChunks($chunkDir, $file['name'], $uniqueId);
        }

        return ['success' => true, 'message' => 'Chunk uploaded successfully'];
    }

    protected function mergeChunks(string $chunkDir, string $fileName, string $uniqueId): array
    {
        $finalPath = $this->uploadDir . $this->sanitizeFilename($fileName);
        $outputFile = fopen($finalPath, 'wb');

        if (!$outputFile) {
            return ['success' => false, 'message' => 'Failed to create output file'];
        }

        $chunks = glob($chunkDir . '*');
        if (empty($chunks)) {
            return ['success' => false, 'message' => 'No chunks found to merge'];
        }

        foreach ($chunks as $chunk) {
            $chunkContent = file_get_contents($chunk);
            if ($chunkContent === false) {
                continue;
            }
            fwrite($outputFile, $chunkContent);
            unlink($chunk);
        }

        fclose($outputFile);
        rmdir($chunkDir);

        return [
            'success' => true,
            'path' => $finalPath,
            'message' => 'File merged successfully'
        ];
    }

    public function generateThumbnail(string $filename, int $width, int $height, string $thumbDir = 'thumbnails/'): array
    {
        $filePath = $this->validateFilePath($filename);
        if (!$filePath) {
            return ['success' => false, 'message' => 'Invalid file path'];
        }

        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return ['success' => false, 'message' => 'Unsupported image type'];
        }

        [$srcImage, $error] = $this->createImageResource($filePath, $imageInfo['mime']);
        if ($error) {
            return ['success' => false, 'message' => $error];
        }

        $thumbnail = imagecreatetruecolor($width, $height);
        imagecopyresampled(
            $thumbnail, $srcImage,
            0, 0, 0, 0,
            $width, $height,
            $imageInfo[0], $imageInfo[1]
        );

        $thumbPath = $this->uploadDir . $thumbDir . basename($filePath);
        if (!$this->ensureUploadDirectoryExists(dirname($thumbPath))) {
            return ['success' => false, 'message' => 'Failed to create thumbnail directory'];
        }

        if (!imagejpeg($thumbnail, $thumbPath, 85)) {
            return ['success' => false, 'message' => 'Failed to save thumbnail'];
        }

        imagedestroy($srcImage);
        imagedestroy($thumbnail);

        return ['success' => true, 'path' => $thumbPath];
    }

    public function addWatermark(string $filename, string $watermarkImage): array
    {
        $filePath = $this->validateFilePath($filename);
        $watermarkPath = realpath($watermarkImage);

        if (!$filePath || !$watermarkPath) {
            return ['success' => false, 'message' => 'Invalid file paths'];
        }

        $imageInfo = @getimagesize($filePath);
        $watermarkInfo = @getimagesize($watermarkPath);

        if (!$imageInfo || !$watermarkInfo) {
            return ['success' => false, 'message' => 'Unsupported image types'];
        }

        [$image, $error] = $this->createImageResource($filePath, $imageInfo['mime']);
        if ($error) {
            return ['success' => false, 'message' => $error];
        }

        [$watermark, $watermarkError] = $this->createImageResource($watermarkPath, $watermarkInfo['mime']);
        if ($watermarkError) {
            return ['success' => false, 'message' => $watermarkError];
        }

        $position = [
            imagesx($image) - imagesx($watermark) - 10,
            imagesy($image) - imagesy($watermark) - 10
        ];

        if (!imagecopy($image, $watermark, ...$position, 0, 0, imagesx($watermark), imagesy($watermark))) {
            return ['success' => false, 'message' => 'Failed to apply watermark'];
        }

        if (!imagejpeg($image, $filePath)) {
            return ['success' => false, 'message' => 'Failed to save watermarked image'];
        }

        imagedestroy($image);
        imagedestroy($watermark);

        return ['success' => true, 'path' => $filePath];
    }

    public function compressImage(string $filename, int $quality = 75): array
    {
        $filePath = $this->validateFilePath($filename);
        if (!$filePath) {
            return ['success' => false, 'message' => 'Invalid file path'];
        }

        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return ['success' => false, 'message' => 'Unsupported image type'];
        }

        [$image, $error] = $this->createImageResource($filePath, $imageInfo['mime']);
        if ($error) {
            return ['success' => false, 'message' => $error];
        }

        $result = match ($imageInfo['mime']) {
            'image/jpeg' => imagejpeg($image, $filePath, $quality),
            'image/png' => imagepng($image, $filePath, round(9 * $quality / 100)),
            default => false
        };

        if (!$result) {
            return ['success' => false, 'message' => 'Failed to compress image'];
        }

        return ['success' => true, 'path' => $filePath];
    }

    public function checkFileIntegrity(string $filename, string $expectedHash, string $hashAlgo = 'sha256'): array
    {
        $filePath = $this->validateFilePath($filename);
        if (!$filePath) {
            return ['success' => false, 'message' => 'Invalid file path'];
        }

        $calculatedHash = @hash_file($hashAlgo, $filePath);
        if (!$calculatedHash) {
            return ['success' => false, 'message' => 'Failed to calculate file hash'];
        }

        return [
            'success' => true,
            'valid' => hash_equals($expectedHash, $calculatedHash),
            'hash' => $calculatedHash
        ];
    }

    public function encryptFile(string $filename, string $encryptionKey): array
    {
        $filePath = $this->validateFilePath($filename);
        if (!$filePath) {
            return ['success' => false, 'message' => 'Invalid file path'];
        }

        $data = @file_get_contents($filePath);
        if ($data === false) {
            return ['success' => false, 'message' => 'Failed to read file'];
        }

        $iv = $this->generateIV();
        $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $encryptionKey, 0, $iv);

        if ($encryptedData === false) {
            return ['success' => false, 'message' => 'Encryption failed'];
        }

        $encryptedPath = $filePath . '.enc';
        if (!file_put_contents($encryptedPath, $iv . $encryptedData)) {
            return ['success' => false, 'message' => 'Failed to write encrypted file'];
        }

        return ['success' => true, 'path' => $encryptedPath];
    }

    protected function createImageResource(string $path, string $mime): array
    {
        return match ($mime) {
            'image/jpeg' => [imagecreatefromjpeg($path), null],
            'image/png' => [imagecreatefrompng($path), null],
            default => [null, 'Unsupported image type']
        };
    }

    protected function validateFilePath(string $filename): ?string
    {
        $filePath = realpath($this->uploadDir . $filename);
        $uploadDirReal = realpath($this->uploadDir);

        if (!$filePath || !$uploadDirReal || strpos($filePath, $uploadDirReal) !== 0) {
            return null;
        }

        return $filePath;
    }

    protected function generateIV(): string
    {
        return random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    }
}
