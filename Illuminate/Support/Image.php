<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - Image =====================
 * ==================================
 */

namespace celionatti\Bolt\Illuminate\Support;

use GdImage;
use InvalidArgumentException;
use RuntimeException;

class Image
{
    /**
     * Resizes an image to fit within a specified maximum size.
     *
     * @param string $filename
     * @param int $max_size
     * @return string The filename of the resized image.
     * @throws InvalidArgumentException If the file does not exist or is unsupported.
     */
    public function resize(string $filename, int $max_size = 700): string
    {
        $image = $this->loadImage($filename);
        [$src_w, $src_h] = $this->getImageDimensions($image);
        [$dst_w, $dst_h] = $this->calculateDestinationSize($src_w, $src_h, $max_size);

        $dst_image = imagecreatetruecolor($dst_w, $dst_h);
        $this->handleAlphaChannel($image, $dst_image);

        imagecopyresampled($dst_image, $image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);

        $this->saveImage($dst_image, $filename);
        $this->destroyImage($image, $dst_image);

        return $filename;
    }

    /**
     * Adds a watermark to the image.
     *
     * @param string $filename
     * @param string $watermarkPath
     * @param string $position
     * @param int $opacity
     * @return string The filename of the watermarked image.
     * @throws InvalidArgumentException If the file does not exist or is unsupported.
     */
    public function watermark(string $filename, string $watermarkPath, string $position = 'bottom-right', int $opacity = 50): string
    {
        $image = $this->loadImage($filename);
        $watermark = $this->loadImage($watermarkPath);
        
        $this->applyWatermark($image, $watermark, $position, $opacity);

        $this->saveImage($image, $filename);
        $this->destroyImage($image, $watermark);

        return $filename;
    }

    /**
     * Crops an image based on specified dimensions and starting point.
     *
     * @param string $filename
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     * @return string The filename of the cropped image.
     * @throws InvalidArgumentException If the file does not exist or is unsupported.
     * @throws RuntimeException If the crop operation fails.
     */
    public function crop(string $filename, int $width, int $height, int $x = 0, int $y = 0): string
    {
        $image = $this->loadImage($filename);

        $dst_image = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);

        if ($dst_image === false) {
            throw new RuntimeException('Crop operation failed.');
        }

        $this->saveImage($dst_image, $filename);
        $this->destroyImage($image, $dst_image);

        return $filename;
    }

    /**
     * Converts the image to grayscale.
     *
     * @param string $filename
     * @return string The filename of the grayscale image.
     * @throws InvalidArgumentException If the file does not exist or is unsupported.
     */
    public function grayscale(string $filename): string
    {
        $image = $this->loadImage($filename);
        imagefilter($image, IMG_FILTER_GRAYSCALE);

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        return $filename;
    }

    /**
     * Rotates the image by the specified number of degrees.
     *
     * @param string $filename
     * @param int $degrees
     * @return string The filename of the rotated image.
     * @throws InvalidArgumentException If the file does not exist or is unsupported.
     */
    public function rotate(string $filename, int $degrees = 90): string
    {
        $image = $this->loadImage($filename);
        $rotatedImage = imagerotate($image, $degrees, 0);

        $this->saveImage($rotatedImage, $filename);
        $this->destroyImage($image, $rotatedImage);

        return $filename;
    }

    /**
     * Flips the image either horizontally or vertically.
     *
     * @param string $filename
     * @param string $mode
     * @return string The filename of the flipped image.
     * @throws InvalidArgumentException If the file does not exist or is unsupported.
     */
    public function flip(string $filename, string $mode = 'horizontal'): string
    {
        $image = $this->loadImage($filename);

        $flipMode = $mode === 'horizontal' ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL;
        imageflip($image, $flipMode);

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        return $filename;
    }

    /**
     * Adds a border to the image.
     *
     * @param string $filename
     * @param string $color
     * @param int $size
     * @return string The filename of the image with the added border.
     * @throws InvalidArgumentException If the file does not exist or is unsupported.
     */
    public function addBorder(string $filename, string $color = '#000000', int $size = 10): string
    {
        $image = $this->loadImage($filename);

        $borderColor = $this->hexToRgb($color);
        $borderAllocatedColor = imagecolorallocate($image, $borderColor['r'], $borderColor['g'], $borderColor['b']);
        imagerectangle($image, $size, $size, imagesx($image) - $size - 1, imagesy($image) - $size - 1, $borderAllocatedColor);

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        return $filename;
    }

    /**
     * Applies a specified image filter to the image.
     *
     * @param string $filename
     * @param int $filterType
     * @return string The filename of the filtered image.
     * @throws InvalidArgumentException If the file does not exist or is unsupported.
     */
    public function applyFilter(string $filename, int $filterType = IMG_FILTER_GRAYSCALE): string
    {
        $image = $this->loadImage($filename);
        imagefilter($image, $filterType);

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        return $filename;
    }

    /**
     * Applies a Gaussian blur to the image.
     *
     * @param string $filename
     * @param int $intensity
     * @return string The filename of the blurred image.
     * @throws InvalidArgumentException If the file does not exist or is unsupported.
     */
    public function blur(string $filename, int $intensity = 5): string
    {
        $image = $this->loadImage($filename);

        for ($i = 0; $i < $intensity; $i++) {
            imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
        }

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        return $filename;
    }

    /**
     * Adds a text watermark to the image.
     *
     * @param string $filename
     * @param string $text
     * @param string $fontFile
     * @param int $fontSize
     * @param string $color
     * @param string $position
     * @return string The filename of the image with the added text watermark.
     * @throws InvalidArgumentException If the file does not exist or is unsupported.
     */
    public function addTextWatermark(string $filename, string $text, string $fontFile, int $fontSize = 20, string $color = '#000000', string $position = 'bottom-right'): string
    {
        $image = $this->loadImage($filename);

        $textColor = $this->hexToRgb($color);
        $allocatedTextColor = imagecolorallocate($image, $textColor['r'], $textColor['g'], $textColor['b']);

        [$x, $y] = $this->calculateTextPosition($position, $fontSize, $fontFile, $text, $image);
        imagettftext($image, $fontSize, 0, $x, $y, $allocatedTextColor, $fontFile, $text);

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        return $filename;
    }

    /**
     * Converts a hex color code to an RGB array.
     *
     * @param string $hex
     * @return array
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) == 6) {
            [$r, $g, $b] = sscanf($hex, "%02x%02x%02x");
        } else {
            [$r, $g, $b] = sscanf($hex, "%1x%1x%1x");
            $r = $r * 17;
            $g = $g * 17;
            $b = $b * 17;
        }
        return ['r' => $r, 'g' => $g, 'b' => $b];
    }

    /**
     * Loads an image from a file.
     *
     * @param string $filename
     * @return GdImage
     * @throws InvalidArgumentException If the file does not exist or is unsupported.
     */
    private function loadImage(string $filename): GdImage
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException("File not found: $filename");
        }

        $image = match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'jpeg', 'jpg' => imagecreatefromjpeg($filename),
            'png' => imagecreatefrompng($filename),
            'gif' => imagecreatefromgif($filename),
            'webp' => imagecreatefromwebp($filename),
            default => throw new InvalidArgumentException("Unsupported image format: $filename"),
        };

        if (!$image) {
            throw new InvalidArgumentException("Failed to load image: $filename");
        }

        return $image;
    }

    /**
     * Saves an image to a file.
     *
     * @param GdImage $image
     * @param string $filename
     * @return void
     */
    private function saveImage(GdImage $image, string $filename): void
    {
        match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'jpeg', 'jpg' => imagejpeg($image, $filename),
            'png' => imagepng($image, $filename),
            'gif' => imagegif($image, $filename),
            'webp' => imagewebp($image, $filename),
            default => throw new InvalidArgumentException("Unsupported image format: $filename"),
        };
    }

    /**
     * Destroys an image resource.
     *
     * @param GdImage ...$images
     * @return void
     */
    private function destroyImage(GdImage ...$images): void
    {
        foreach ($images as $image) {
            imagedestroy($image);
        }
    }

    /**
     * Handles the alpha channel for PNG images.
     *
     * @param GdImage $src_image
     * @param GdImage $dst_image
     * @return void
     */
    private function handleAlphaChannel(GdImage $src_image, GdImage $dst_image): void
    {
        imagealphablending($dst_image, false);
        imagesavealpha($dst_image, true);
        $transparent = imagecolorallocatealpha($dst_image, 0, 0, 0, 127);
        imagefilledrectangle($dst_image, 0, 0, imagesx($src_image), imagesy($src_image), $transparent);
    }

    /**
     * Calculates the destination size for resizing.
     *
     * @param int $src_w
     * @param int $src_h
     * @param int $max_size
     * @return array
     */
    private function calculateDestinationSize(int $src_w, int $src_h, int $max_size): array
    {
        $aspect_ratio = $src_w / $src_h;
        if ($src_w > $src_h) {
            $dst_w = $max_size;
            $dst_h = (int)($max_size / $aspect_ratio);
        } else {
            $dst_w = (int)($max_size * $aspect_ratio);
            $dst_h = $max_size;
        }
        return [$dst_w, $dst_h];
    }

    /**
     * Applies a watermark to the image.
     *
     * @param GdImage $image
     * @param GdImage $watermark
     * @param string $position
     * @param int $opacity
     * @return void
     */
    private function applyWatermark(GdImage $image, GdImage $watermark, string $position, int $opacity): void
    {
        [$src_w, $src_h] = $this->getImageDimensions($image);
        [$wm_w, $wm_h] = $this->getImageDimensions($watermark);
        [$dst_x, $dst_y] = $this->calculatePosition($position, $src_w, $src_h, $wm_w, $wm_h);

        imagecopymerge($image, $watermark, $dst_x, $dst_y, 0, 0, $wm_w, $wm_h, $opacity);
    }

    /**
     * Calculates the position for placing text or watermark.
     *
     * @param string $position
     * @param int $src_w
     * @param int $src_h
     * @param int $wm_w
     * @param int $wm_h
     * @return array
     */
    private function calculatePosition(string $position, int $src_w, int $src_h, int $wm_w, int $wm_h): array
    {
        return match ($position) {
            'top-left' => [10, 10],
            'top-right' => [$src_w - $wm_w - 10, 10],
            'bottom-left' => [10, $src_h - $wm_h - 10],
            'bottom-right' => [$src_w - $wm_w - 10, $src_h - $wm_h - 10],
            'center' => [($src_w - $wm_w) / 2, ($src_h - $wm_h) / 2],
            default => [0, 0],
        };
    }

    /**
     * Calculates the text position based on the specified alignment.
     *
     * @param string $position
     * @param int $fontSize
     * @param string $fontFile
     * @param string $text
     * @param GdImage $image
     * @return array
     */
    private function calculateTextPosition(string $position, int $fontSize, string $fontFile, string $text, GdImage $image): array
    {
        $textBox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);

        return match ($position) {
            'top-left' => [10, $textHeight + 10],
            'top-right' => [$imageWidth - $textWidth - 10, $textHeight + 10],
            'bottom-left' => [10, $imageHeight - 10],
            'bottom-right' => [$imageWidth - $textWidth - 10, $imageHeight - 10],
            'center' => [($imageWidth - $textWidth) / 2, ($imageHeight + $textHeight) / 2],
            default => [0, 0],
        };
    }

    /**
     * Retrieves the width and height of an image.
     *
     * @param GdImage $image
     * @return array
     */
    private function getImageDimensions(GdImage $image): array
    {
        return [imagesx($image), imagesy($image)];
    }
}
