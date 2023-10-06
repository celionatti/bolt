<?php

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

    public function resize($width, $height)
    {
        $sourceImage = $this->createImage();
        $resizedImage = imagecreatetruecolor($width, $height);

        imagecopyresampled(
            $resizedImage,
            $sourceImage,
            0, 0, 0, 0,
            $width, $height,
            $this->getWidth(), $this->getHeight()
        );

        return $this->saveImage($resizedImage);
    }

    public function crop($x, $y, $width, $height)
    {
        $sourceImage = $this->createImage();
        $croppedImage = imagecrop($sourceImage, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);

        return $this->saveImage($croppedImage);
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
}
