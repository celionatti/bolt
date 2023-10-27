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
    protected $uploadDir;
    protected $allowedFileTypes = [];
    protected $maxFileSize = 10485760; // 10 MB
    protected $overwriteExisting = false;

    public function __construct($uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    public function setAllowedFileTypes($allowedFileTypes)
    {
        $this->allowedFileTypes = $allowedFileTypes;
    }

    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;
    }

    public function setOverwriteExisting($overwriteExisting)
    {
        $this->overwriteExisting = $overwriteExisting;
    }

    public function uploadFile($fileInputName, $rename = true)
    {
        if (isset($_FILES[$fileInputName])) {
            $file = $_FILES[$fileInputName];

            if ($file['error'] === UPLOAD_ERR_OK) {
                $fileName = $rename ? $this->generateUniqueFileName($file['name']) : $file['name'];
                $fileTmpPath = $file['tmp_name'];
                $fileSize = $file['size'];

                if ($fileSize > $this->maxFileSize) {
                    return ['error' => 'File size exceeds the allowed limit.'];
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
}
