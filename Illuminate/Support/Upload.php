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
    protected $files = [];
    protected $maxSize = 5 * 1024 * 1024; // Default max size: 5MB
    protected $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    protected $uploadDir = 'uploads/';
    protected $thumbnailDir = 'thumbnails/';
    protected $chunkSize = 1024 * 1024; // Default chunk size: 1MB
    protected $overwrite = false;
    protected $customValidations = [];

    public function __construct(array $files)
    {
        $this->files = is_array($files['name']) ? $this->restructureFiles($files) : [$files];
        $this->ensureDirectoriesExist();
    }

    public function setMaxSize(int $bytes)
    {
        $this->maxSize = $bytes;
        return $this;
    }

    public function setAllowedTypes(array $types)
    {
        $this->allowedTypes = $types;
        return $this;
    }

    public function setUploadDir(string $dir)
    {
        $this->uploadDir = rtrim($dir, '/') . '/';
        return $this;
    }

    public function setThumbnailDir(string $dir)
    {
        $this->thumbnailDir = rtrim($dir, '/') . '/';
        return $this;
    }

    public function setChunkSize(int $bytes)
    {
        $this->chunkSize = $bytes;
        return $this;
    }

    public function setOverwrite(bool $overwrite)
    {
        $this->overwrite = $overwrite;
        return $this;
    }

    public function addCustomValidation(callable $callback)
    {
        $this->customValidations[] = $callback;
        return $this;
    }

    protected function ensureDirectoriesExist()
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        if (!is_dir($this->thumbnailDir)) {
            mkdir($this->thumbnailDir, 0755, true);
        }
    }

    public function validate($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception($this->getErrorMessage($file['error']));
        }

        if ($file['size'] > $this->maxSize) {
            throw new Exception('File exceeds the maximum allowed size.');
        }

        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new Exception('File type is not allowed.');
        }

        foreach ($this->customValidations as $callback) {
            if (!call_user_func($callback, $file)) {
                throw new InvalidArgumentException('Custom validation failed.');
            }
        }

        return true;
    }

    public function store($rename = true)
    {
        $storedFiles = [];

        foreach ($this->files as $file) {
            $this->validate($file);

            $filename = $rename ? $this->generateUniqueFilename($file) : $file['name'];
            $destination = $this->uploadDir . $filename;

            if (file_exists($destination) && !$this->overwrite) {
                throw new Exception('File already exists and overwrite is disabled.');
            }

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new Exception('Failed to move uploaded file.');
            }

            $storedFiles[] = $filename;
        }

        return $storedFiles;
    }

    public function storeChunked($chunk, $totalChunks, $filename)
    {
        $tempFile = $this->uploadDir . $filename . '.part';

        file_put_contents($tempFile, $chunk, FILE_APPEND);

        if (filesize($tempFile) >= $totalChunks * $this->chunkSize) {
            rename($tempFile, $this->uploadDir . $filename);
            return $filename;
        }

        return false;
    }

    public function generateThumbnail(string $filename, int $width, int $height)
    {
        $filepath = $this->retrieve($filename);
        $thumbnailPath = $this->thumbnailDir . $filename;

        $image = $this->resizeImage($filepath, $width, $height);
        imagejpeg($image, $thumbnailPath);

        return $thumbnailPath;
    }

    public function cropImage(string $filename, int $x, int $y, int $width, int $height)
    {
        $filepath = $this->retrieve($filename);
        $imageType = exif_imagetype($filepath);

        $src = $this->createImageResource($filepath, $imageType);

        $dst = imagecreatetruecolor($width, $height);
        imagecopy($dst, $src, 0, 0, $x, $y, $width, $height);

        $this->saveImageResource($dst, $filepath, $imageType);

        imagedestroy($src);
        imagedestroy($dst);

        return $filepath;
    }

    public function addWatermark(string $filename, string $watermarkImage)
    {
        $filepath = $this->retrieve($filename);
        $imageType = exif_imagetype($filepath);
        $watermarkType = exif_imagetype($watermarkImage);

        $image = $this->createImageResource($filepath, $imageType);
        $watermark = $this->createImageResource($watermarkImage, $watermarkType);

        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        $dstX = $imageWidth - $watermarkWidth - 10;
        $dstY = $imageHeight - $watermarkHeight - 10;

        imagecopy($image, $watermark, $dstX, $dstY, 0, 0, $watermarkWidth, $watermarkHeight);

        $this->saveImageResource($image, $filepath, $imageType);

        imagedestroy($image);
        imagedestroy($watermark);

        return $filepath;
    }

    public function retrieve(string $filename)
    {
        $filepath = $this->uploadDir . $filename;

        if (!file_exists($filepath)) {
            throw new Exception('File not found.');
        }

        return $filepath;
    }

    public function delete(string $filename)
    {
        $filepath = $this->uploadDir . $filename;

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        throw new Exception('File not found.');
    }

    protected function resizeImage(string $filepath, int $width, int $height, bool $keepAspectRatio = true)
    {
        $imageType = exif_imagetype($filepath);
        $src = $this->createImageResource($filepath, $imageType);

        $originalWidth = imagesx($src);
        $originalHeight = imagesy($src);

        if ($keepAspectRatio) {
            $aspectRatio = $originalWidth / $originalHeight;
            if ($width / $height > $aspectRatio) {
                $width = (int)($height * $aspectRatio);
            } else {
                $height = (int)($width / $aspectRatio);
            }
        }

        $dst = imagecreatetruecolor($width, $height);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

        imagedestroy($src);

        return $dst;
    }

    protected function createImageResource($filepath, $imageType)
    {
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filepath);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filepath);
            default:
                throw new Exception('Unsupported image type.');
        }
    }

    protected function saveImageResource($image, $filepath, $imageType)
    {
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($image, $filepath);
                break;
            case IMAGETYPE_PNG:
                imagepng($image, $filepath);
                break;
            default:
                throw new Exception('Unsupported image type.');
        }
    }

    protected function generateUniqueFilename($file)
    {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        return uniqid() . '.' . $extension;
    }

    protected function getErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];

        return $errors[$errorCode] ?? 'Unknown error.';
    }

    protected function restructureFiles(array $files)
    {
        $structured = [];
        foreach ($files['name'] as $key => $name) {
            $structured[] = [
                'name' => $name,
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key],
            ];
        }
        return $structured;
    }
}
