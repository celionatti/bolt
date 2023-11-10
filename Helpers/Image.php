<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - Image =====================
 * ==================================
 */

namespace celionatti\Bolt\Helpers;

use GdImage;

class Image
{
    /**
     * Resizes an image to fit within a specified maximum size.
     *
     * @param string $filename
     * @param int $max_size
     *
     * @return string The filename of the resized image.
     *
     * @throws \InvalidArgumentException If the file does not exist.
     */
    public function resize($filename, $max_size = 700)
    {
        // Validation
        $this->validateFileExistence($filename);

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

    /**
     * Adds a watermark to the image.
     *
     * @param string $filename
     * @param string $watermarkPath
     * @param string $position
     *
     * @return string The filename of the watermarked image.
     *
     * @throws \InvalidArgumentException If the file does not exist.
     */
    public function watermark(string $filename, string $watermarkPath, string $position = 'bottom-right')
    {
        // Validation
        $this->validateFileExistence($filename);

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        // Validate watermark file existence
        $this->validateFileExistence($watermarkPath);

        $watermark = $this->createImageResource($watermarkPath, mime_content_type($watermarkPath));
        $this->applyWatermark($image, $watermark, $position);

        $this->saveResizedImage($type, $image, $filename);

        imagedestroy($image);
        imagedestroy($watermark);

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
     *
     * @return string The filename of the cropped image.
     *
     * @throws \InvalidArgumentException If the file does not exist.
     * @throws \RuntimeException If the crop operation fails.
     */
    public function crop(string $filename, int $width, int $height, int $x = 0, int $y = 0)
    {
        // Validation
        $this->validateFileExistence($filename);

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

    /**
     * Converts the image to grayscale.
     *
     * @param string $filename
     *
     * @return string The filename of the grayscale image.
     *
     * @throws \InvalidArgumentException If the file does not exist.
     */
    public function grayscale(string $filename)
    {
        // Validation
        $this->validateFileExistence($filename);

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        imagefilter($image, IMG_FILTER_GRAYSCALE);

        $this->saveResizedImage($type, $image, $filename);

        imagedestroy($image);

        return $filename;
    }

    /**
     * Rotates the image by the specified number of degrees.
     *
     * @param string $filename
     * @param int $degrees
     *
     * @return string The filename of the rotated image.
     *
     * @throws \InvalidArgumentException If the file does not exist.
     */
    public function rotate($filename, $degrees = 90)
    {
        // Validation
        $this->validateFileExistence($filename);

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        $rotatedImage = imagerotate($image, $degrees, 0);

        $this->saveResizedImage($type, $rotatedImage, $filename);

        imagedestroy($image);
        imagedestroy($rotatedImage);

        return $filename;
    }

    /**
     * Flips the image either horizontally or vertically.
     *
     * @param string $filename
     * @param string $mode
     *
     * @return string The filename of the flipped image.
     *
     * @throws \InvalidArgumentException If the file does not exist.
     */
    public function flip(string $filename, string $mode = 'horizontal')
    {
        // Validation
        $this->validateFileExistence($filename);

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        // Flip the image directly
        if ($mode === 'horizontal') {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        } else {
            imageflip($image, IMG_FLIP_VERTICAL);
        }

        $this->saveResizedImage($type, $image, $filename);

        imagedestroy($image);

        return $filename;
    }

    /**
     * Adds a border to the image.
     *
     * @param string $filename
     * @param string $color
     * @param int $size
     *
     * @return string The filename of the image with the added border.
     *
     * @throws \InvalidArgumentException If the file does not exist.
     */
    public function addBorder(string $filename, string $color = '#000000', int $size = 10)
    {
        // Validation
        $this->validateFileExistence($filename);

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

    /**
     * Applies a specified image filter to the image.
     *
     * @param string $filename
     * @param int $filterType
     *
     * @return string The filename of the filtered image.
     *
     * @throws \InvalidArgumentException If the file does not exist.
     */
    public function applyFilter(string $filename, int $filterType = IMG_FILTER_GRAYSCALE)
    {
        // Validation
        $this->validateFileExistence($filename);

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        imagefilter($image, $filterType);

        $this->saveResizedImage($type, $image, $filename);

        imagedestroy($image);

        return $filename;
    }

    /**
     * Applies a Gaussian blur to the image.
     *
     * @param string $filename
     * @param int $intensity
     *
     * @return string The filename of the blurred image.
     *
     * @throws \InvalidArgumentException If the file does not exist.
     */
    public function blur(string $filename, int $intensity = 5)
    {
        // Validation
        $this->validateFileExistence($filename);

        $type = mime_content_type($filename);
        $image = $this->createImageResource($filename, $type);

        for ($i = 0; $i < $intensity; $i++) {
            imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
        }

        $this->saveResizedImage($type, $image, $filename);

        imagedestroy($image);

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
     *
     * @return string The filename of the image with the added text watermark.
     *
     * @throws \InvalidArgumentException If the file does not exist.
     */
    public function addTextWatermark(string $filename, string $text, string $fontFile, int $fontSize = 20, string $color = '#000000', string $position = 'bottom-right')
    {
        // Validation
        $this->validateFileExistence($filename);

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

    /**
     * Creates an image resource based on the given filename and MIME type.
     *
     * @param string $filename
     * @param string $type MIME type of the image (e.g., 'image/jpeg', 'image/png').
     *
     * @return GdImage|resource An image resource identifier representing the loaded image.
     *
     * @throws \InvalidArgumentException If the image type is unsupported or the file does not exist.
     */
    private function createImageResource(string $filename, string $type)
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

    /**
     * Calculates the dimensions of the destination size for resizing an image while maintaining its aspect ratio.
     *
     * @param int $src_w Width of the source image.
     * @param int $src_h Height of the source image.
     * @param int $max_size Maximum size (width or height) for the destination image.
     *
     * @return array An array containing the calculated width and height for the destination size.
     */
    private function calculateDestinationSize(int $src_w, int $src_h, int $max_size)
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

    /**
     * Handles the alpha channel for PNG images by setting appropriate blending and saving settings.
     *
     * @param string $type MIME type of the image (e.g., 'image/png').
     * @param GdImage|resource $image An image resource identifier representing the loaded image.
     *
     * @return void
     */
    private function handleAlphaChannel(string $type, GdImage $image): void
    {
        if ($type == 'image/png') {
            // Disable blending to preserve alpha channel
            imagealphablending($image, false);

            // Save full alpha channel information
            imagesavealpha($image, true);
        }
    }

    /**
     * Saves the resized image to the specified filename based on its MIME type.
     *
     * @param string $type MIME type of the image (e.g., 'image/jpeg', 'image/png').
     * @param GdImage|resource $image An image resource identifier representing the resized image.
     * @param string $filename The filename to save the resized image to.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the image type is unsupported.
     */
    private function saveResizedImage(string $type, $image, string $filename): void
    {
        switch ($type) {
            case 'image/png':
                // Save PNG image with compression level 8
                imagepng($image, $filename, 8);
                break;
            case 'image/gif':
                // Save GIF image
                imagegif($image, $filename);
                break;
            case 'image/webp':
                // Save WebP image with quality 90
                imagewebp($image, $filename, 90);
                break;
            default:
                // Save JPEG image with quality 90
                imagejpeg($image, $filename, 90);
                break;
        }
    }


    /**
     * Applies a watermark to the image at a specified position.
     *
     * @param GdImage|resource $image An image resource identifier representing the loaded image.
     * @param GdImage|resource $watermark An image resource identifier representing the watermark image.
     * @param string $position The position of the watermark ('top-left', 'top-right', 'bottom-left', 'bottom-right').
     *
     * @return void
     */
    private function applyWatermark($image, $watermark, string $position): void
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

        // Copy the watermark onto the image at the specified position
        imagecopy($image, $watermark, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight);
    }


    /**
     * Converts a hexadecimal color value to an associative array representing RGB values.
     *
     * @param string $hex The hexadecimal color value (e.g., '#RRGGBB').
     *
     * @return array An associative array with 'r', 'g', and 'b' keys representing the RGB values.
     *
     * @throws \InvalidArgumentException If the input string is not a valid hexadecimal color.
     */
    private function hexToRgb(string $hex): array
    {
        // Remove the '#' character if present
        $hex = ltrim($hex, '#');

        // Validate the hexadecimal color format
        if (!preg_match('/^[a-fA-F0-9]{6}$/', $hex)) {
            throw new \InvalidArgumentException('Invalid hexadecimal color format.');
        }

        // Split the hex color into individual components
        $rgb = str_split($hex, 2);

        return [
            'r' => hexdec($rgb[0]),
            'g' => hexdec($rgb[1]),
            'b' => hexdec($rgb[2]),
        ];
    }


    /**
     * Calculates the starting position for placing text within an image based on the specified position.
     *
     * @param string $position The position for placing text ('top-left', 'top-right', 'bottom-left', 'bottom-right', 'center').
     * @param int $imageSize The size (width or height) of the image.
     * @param int $textSize The size (width or height) of the text to be placed.
     *
     * @return int The calculated starting position for placing text within the image.
     */
    private function calculateTextPosition(string $position, int $imageSize, int $textSize): int
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


    /**
     * Validates the existence of a file.
     *
     * @param string $filename
     *
     * @throws \InvalidArgumentException If the file does not exist.
     */
    private function validateFileExistence(string $filename): void
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File does not exist.');
        }
    }
}
