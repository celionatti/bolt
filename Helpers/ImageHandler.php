<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - ImageHandler ==============
 * ==================================
 */

namespace celionatti\Bolt\Helpers;

use Exception;

class ImageHandler
{
    protected $imagePath;
    protected $imageInfo;

    public function __construct($imagePath)
    {
        $this->imagePath = $imagePath;
        $this->imageInfo = getimagesize($imagePath);
    }

    public function getWidth()
    {
        return $this->imageInfo[0];
    }

    public function getHeight()
    {
        return $this->imageInfo[1];
    }

    public function save($outputPath)
    {
        $sourceImage = $this->createImage();
        return $this->saveImage($sourceImage, $outputPath);
    }

    protected function createImage()
    {
        $imageInfo = $this->imageInfo;
        $imagePath = $this->imagePath;

        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($imagePath);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($imagePath);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($imagePath);
            default:
                throw new Exception('Unsupported image type.');
        }
    }

    protected function saveImage($image, $outputPath = null)
    {
        $outputPath = $outputPath ?: $this->imagePath;

        $imageInfo = $this->imageInfo;

        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                return imagejpeg($image, $outputPath, 100);
            case IMAGETYPE_PNG:
                return imagepng($image, $outputPath, 9); // 9 is the highest compression level
            case IMAGETYPE_GIF:
                return imagegif($image, $outputPath);
            default:
                throw new Exception('Unsupported image type.');
        }
    }

    public function applyFilter($filter)
    {
        $sourceImage = $this->createImage();

        switch ($filter) {
            case 'grayscale':
                imagefilter($sourceImage, IMG_FILTER_GRAYSCALE);
                break;
            case 'sepia':
                $this->applySepiaFilter($sourceImage);
                break;
            case 'emboss':
                imagefilter($sourceImage, IMG_FILTER_EMBOSS);
                break;
                // Add more filters here
        }

        return $this->saveImage($sourceImage);
    }

    protected function applySepiaFilter($image)
    {
        $width = $this->getWidth();
        $height = $this->getHeight();

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $pixel = imagecolorat($image, $x, $y);
                $r = ($pixel >> 16) & 0xFF;
                $g = ($pixel >> 8) & 0xFF;
                $b = $pixel & 0xFF;

                $newR = min(255, ($r * 0.393) + ($g * 0.769) + ($b * 0.189));
                $newG = min(255, ($r * 0.349) + ($g * 0.686) + ($b * 0.168));
                $newB = min(255, ($r * 0.272) + ($g * 0.534) + ($b * 0.131));

                $newColor = imagecolorallocate($image, $newR, $newG, $newB);
                imagesetpixel($image, $x, $y, $newColor);
            }
        }
    }

    public function addWatermark($watermarkPath, $position = 'bottom-right', $opacity = 70)
    {
        $sourceImage = $this->createImage();
        $watermark = imagecreatefrompng($watermarkPath);

        $sourceWidth = $this->getWidth();
        $sourceHeight = $this->getHeight();
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        $positionX = 0;
        $positionY = 0;

        // Determine watermark position
        switch ($position) {
            case 'top-left':
                $positionX = 0;
                $positionY = 0;
                break;
            case 'top-right':
                $positionX = $sourceWidth - $watermarkWidth;
                $positionY = 0;
                break;
            case 'bottom-left':
                $positionX = 0;
                $positionY = $sourceHeight - $watermarkHeight;
                break;
            case 'bottom-right':
            default:
                $positionX = $sourceWidth - $watermarkWidth;
                $positionY = $sourceHeight - $watermarkHeight;
                break;
        }

        imagecopymerge($sourceImage, $watermark, $positionX, $positionY, 0, 0, $watermarkWidth, $watermarkHeight, $opacity);

        return $this->saveImage($sourceImage);
    }

    public function resize($width, $height)
    {
        $sourceImage = $this->createImage();

        $sourceWidth = $this->getWidth();
        $sourceHeight = $this->getHeight();

        // Calculate new dimensions while maintaining aspect ratio
        if ($width && !$height) {
            $height = ($width / $sourceWidth) * $sourceHeight;
        } elseif (!$width && $height) {
            $width = ($height / $sourceHeight) * $sourceWidth;
        } elseif ($width && $height) {
            // Resize to specific dimensions
        } else {
            // Invalid dimensions
            return false;
        }

        $resizedImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        return $this->saveImage($resizedImage);
    }

    public function crop($x, $y, $width, $height)
    {
        $sourceImage = $this->createImage();

        $sourceWidth = $this->getWidth();
        $sourceHeight = $this->getHeight();

        if ($x < 0 || $y < 0 || $width <= 0 || $height <= 0) {
            // Invalid crop parameters
            return false;
        }

        // Ensure crop area doesn't exceed image dimensions
        $x = min($x, $sourceWidth);
        $y = min($y, $sourceHeight);
        $width = min($width, $sourceWidth - $x);
        $height = min($height, $sourceHeight - $y);

        $croppedImage = imagecrop($sourceImage, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);

        return $this->saveImage($croppedImage);
    }

    public function rotate($degrees)
    {
        $sourceImage = $this->createImage();

        $rotatedImage = imagerotate($sourceImage, $degrees, 0);

        return $this->saveImage($rotatedImage);
    }

    public function grayscale()
    {
        $sourceImage = $this->createImage();

        imagefilter($sourceImage, IMG_FILTER_GRAYSCALE);

        return $this->saveImage($sourceImage);
    }
}
