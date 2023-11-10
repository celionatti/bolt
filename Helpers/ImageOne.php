<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - ImageOne =====================
 * ==================================
 */

namespace celionatti\Bolt\Helpers;

use Exception;

class ImageOne
{
	private $image;
    private $imageType;

    public function __construct($filePath) {
        $this->loadImage($filePath);
    }

    private function loadImage($filePath) {
        $info = getimagesize($filePath);
        $this->imageType = $info[2];

        switch ($this->imageType) {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($filePath);
                break;
            default:
                throw new Exception("Unsupported image type");
        }
    }

    public function addWatermark($watermarkPath, $position = 'bottom-right') {
        $watermark = imagecreatefrompng($watermarkPath);
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        $imageWidth = imagesx($this->image);
        $imageHeight = imagesy($this->image);

        switch ($position) {
            case 'top-left':
                $x = 0;
                $y = 0;
                break;
            case 'top-right':
                $x = $imageWidth - $watermarkWidth;
                $y = 0;
                break;
            case 'bottom-left':
                $x = 0;
                $y = $imageHeight - $watermarkHeight;
                break;
            case 'bottom-right':
            default:
                $x = $imageWidth - $watermarkWidth;
                $y = $imageHeight - $watermarkHeight;
                break;
        }

        imagecopy($this->image, $watermark, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight);
        imagedestroy($watermark);
    }

    public function resize($width, $height) {
        $resizedImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($resizedImage, $this->image, 0, 0, 0, 0, $width, $height, imagesx($this->image), imagesy($this->image));
        imagedestroy($this->image);
        $this->image = $resizedImage;
    }

    public function rotate($degrees) {
        $this->image = imagerotate($this->image, $degrees, 0);
    }

    public function setBackgroundColor($color) {
        $rgb = $this->hexToRgb($color);
        $bgColor = imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);
        imagefill($this->image, 0, 0, $bgColor);
    }

    public function save($filePath, $quality = 100) {
        switch ($this->imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->image, $filePath, $quality);
                break;
            case IMAGETYPE_PNG:
                imagepng($this->image, $filePath);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->image, $filePath);
                break;
        }
    }

    private function hexToRgb($hex) {
        $hex = ltrim($hex, '#');
        $rgb = array();
        $rgb['r'] = hexdec(substr($hex, 0, 2));
        $rgb['g'] = hexdec(substr($hex, 2, 2));
        $rgb['b'] = hexdec(substr($hex, 4, 2));
        return $rgb;
    }

    public function display() {
        header('Content-Type: image/png');
        imagepng($this->image);
    }

    public function __destruct() {
        imagedestroy($this->image);
    }
}