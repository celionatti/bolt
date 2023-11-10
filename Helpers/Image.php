<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - Image =====================
 * ==================================
 */

namespace celionatti\Bolt\Helpers;

use Exception;

class Image
{
    public function resize($filename, $max_size = 700)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File does not exist.');
        }

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        $src_w = imagesx($image);
        $src_h = imagesy($image);

        list($dst_w, $dst_h) = $this->calculateDestinationSize($src_w, $src_h, $max_size);

        $dst_image = imagecreatetruecolor($dst_w, $dst_h);
        $this->handleAlphaChannel($type, $dst_image);

        imagecopyresampled($dst_image, $image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        imagedestroy($image);

        $this->saveResizedImage($type, $dst_image, $filename);

        imagedestroy($dst_image);

        return $filename;
    }

    public function watermark($filename, $watermarkPath, $position = 'bottom-right')
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File does not exist.');
        }

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        $watermark = $this->createImageResource($watermarkPath, mime_content_type($watermarkPath));
        $this->applyWatermark($image, $watermark, $position);

        $this->saveResizedImage($type, $image, $filename);

        imagedestroy($image);
        imagedestroy($watermark);

        return $filename;
    }

    public function crop($filename, $width, $height, $x = 0, $y = 0)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File does not exist.');
        }

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        $dst_image = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);

        imagedestroy($image);

        if ($dst_image === false) {
            throw new \RuntimeException('Crop operation failed.');
        }

        $this->saveResizedImage($type, $dst_image, $filename);

        imagedestroy($dst_image);

        return $filename;
    }

    public function grayscale($filename)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File does not exist.');
        }

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        imagefilter($image, IMG_FILTER_GRAYSCALE);

        $this->saveResizedImage($type, $image, $filename);

        imagedestroy($image);

        return $filename;
    }

    public function rotate($filename, $degrees = 90)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File does not exist.');
        }

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        $rotatedImage = imagerotate($image, $degrees, 0);

        $this->saveResizedImage($type, $rotatedImage, $filename);

        imagedestroy($image);
        imagedestroy($rotatedImage);

        return $filename;
    }

    public function flip($filename, $mode = 'horizontal')
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File does not exist.');
        }

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        $flippedImage = $mode === 'horizontal'
            ? imageflip($image, IMG_FLIP_HORIZONTAL)
            : imageflip($image, IMG_FLIP_VERTICAL);

        $this->saveResizedImage($type, $flippedImage, $filename);

        imagedestroy($image);
        imagedestroy($flippedImage);

        return $filename;
    }

    public function addBorder($filename, $color = '#000000', $size = 10)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File does not exist.');
        }

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        $borderColor = $this->hexToRgb($color);

        imagesetthickness($image, $size);

        $borderColor = imagecolorallocate($image, $borderColor['r'], $borderColor['g'], $borderColor['b']);
        imagerectangle($image, 0, 0, imagesx($image) - 1, imagesy($image) - 1, $borderColor);

        $this->saveResizedImage($type, $image, $filename);

        imagedestroy($image);

        return $filename;
    }

    public function applyFilter($filename, $filterType = IMG_FILTER_GRAYSCALE)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File does not exist.');
        }

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        imagefilter($image, $filterType);

        $this->saveResizedImage($type, $image, $filename);

        imagedestroy($image);

        return $filename;
    }

    public function blur($filename, $intensity = 5)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File does not exist.');
        }

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        for ($i = 0; $i < $intensity; $i++) {
            imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
        }

        $this->saveResizedImage($type, $image, $filename);

        imagedestroy($image);

        return $filename;
    }

    public function addTextWatermark($filename, $text, $fontFile, $fontSize = 20, $color = '#000000', $position = 'bottom-right')
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File does not exist.');
        }

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        $textColor = $this->hexToRgb($color);
        $textColor = imagecolorallocate($image, $textColor['r'], $textColor['g'], $textColor['b']);

        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);

        $textBox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);

        $x = $this->calculateTextPosition($position, $imageWidth, $textWidth);
        $y = $this->calculateTextPosition($position, $imageHeight, $textHeight);

        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontFile, $text);

        $this->saveResizedImage($type, $image, $filename);

        imagedestroy($image);

        return $filename;
    }

    private function createImageResource($filename, $type)
    {
        switch ($type) {
            case 'image/png':
                return imagecreatefrompng($filename);
            case 'image/gif':
                return imagecreatefromgif($filename);
            case 'image/jpeg':
                return imagecreatefromjpeg($filename);
            case 'image/webp':
                return imagecreatefromwebp($filename);
            default:
                throw new \InvalidArgumentException('Unsupported image type.');
        }
    }

    private function calculateDestinationSize($src_w, $src_h, $max_size)
    {
        if ($src_w > $src_h) {
            if ($src_w < $max_size) {
                $max_size = $src_w;
            }
            return [(int) round($max_size), (int) round(($src_h / $src_w) * $max_size)];
        } else {
            if ($src_h < $max_size) {
                $max_size = $src_h;
            }
            return [(int) round(($src_w / $src_h) * $max_size), (int) round($max_size)];
        }
    }

    private function handleAlphaChannel($type, $image)
    {
        if ($type == 'image/png') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }
    }

    private function saveResizedImage($type, $image, $filename)
    {
        switch ($type) {
            case 'image/png':
                imagepng($image, $filename, 8);
                break;
            case 'image/gif':
                imagegif($image, $filename);
                break;
            case 'image/webp':
                imagewebp($image, $filename, 90);
                break;
            default:
                imagejpeg($image, $filename, 90);
                break;
        }
    }

    private function applyWatermark($image, $watermark, $position)
    {
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

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

        imagecopy($image, $watermark, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight);
    }

    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        $rgb = str_split($hex, 2);

        return [
            'r' => hexdec($rgb[0]),
            'g' => hexdec($rgb[1]),
            'b' => hexdec($rgb[2]),
        ];
    }

    private function calculateTextPosition($position, $imageSize, $textSize)
    {
        switch ($position) {
            case 'top-left':
            case 'bottom-left':
                return 0;
            case 'top-right':
            case 'bottom-right':
                return $imageSize - $textSize;
            case 'center':
                return ($imageSize - $textSize) / 2;
            default:
                return 0; // Default to top-left
        }
    }
}