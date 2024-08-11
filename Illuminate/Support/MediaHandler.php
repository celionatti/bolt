<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - MediaHandler ==============
 * ==================================
 */

namespace celionatti\Bolt\Illuminate\Support;


class MediaHandler
{
    protected $filePath;
    protected $ffmpegPath;
    protected $ffprobePath;

    public function __construct($filePath, $ffmpegPath = '/usr/bin/ffmpeg', $ffprobePath = '/usr/bin/ffprobe')
    {
        $this->filePath = $filePath;
        $this->ffmpegPath = $ffmpegPath;
        $this->ffprobePath = $ffprobePath;
    }

    /**
     * Uploads the media file.
     */
    public function upload($destination)
    {
        if (move_uploaded_file($this->filePath, $destination)) {
            return $destination;
        }
        throw new \Exception("File upload failed.");
    }

    /**
     * Converts media to a different format.
     */
    public function convert($outputFormat)
    {
        $outputFile = preg_replace('/\.\w+$/', '.' . $outputFormat, $this->filePath);
        $command = "{$this->ffmpegPath} -i {$this->filePath} {$outputFile}";

        exec($command, $output, $returnVar);
        if ($returnVar === 0) {
            return $outputFile;
        }
        throw new \Exception("Conversion failed: " . implode("\n", $output));
    }

    /**
     * Extracts metadata from the media file.
     */
    public function getMetadata()
    {
        $command = "{$this->ffprobePath} -v quiet -print_format json -show_format -show_streams {$this->filePath}";
        exec($command, $output);

        $metadata = json_decode(implode("\n", $output), true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $metadata;
        }
        throw new \Exception("Metadata extraction failed.");
    }

    /**
     * Generates a thumbnail for a video.
     */
    public function generateThumbnail($timeInSeconds, $outputImage)
    {
        $command = "{$this->ffmpegPath} -i {$this->filePath} -ss {$timeInSeconds} -vframes 1 {$outputImage}";
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return $outputImage;
        }
        throw new \Exception("Thumbnail generation failed.");
    }

    /**
     * Trims a video or audio file to a specific duration.
     */
    public function trim($startTime, $duration, $outputFile)
    {
        $command = "{$this->ffmpegPath} -i {$this->filePath} -ss {$startTime} -t {$duration} -c copy {$outputFile}";
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return $outputFile;
        }
        throw new \Exception("Trimming failed: " . implode("\n", $output));
    }

    /**
     * Generates an audio waveform image.
     */
    public function generateWaveform($outputImage, $width = 800, $height = 200, $color = 'blue')
    {
        $command = "{$this->ffmpegPath} -i {$this->filePath} -filter_complex \"[0:a]showwavespic=s={$width}x{$height}:colors={$color}\" -frames:v 1 {$outputImage}";
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return $outputImage;
        }
        throw new \Exception("Waveform generation failed: " . implode("\n", $output));
    }

    /**
     * Integrates with a third-party service (e.g., cloud storage or processing).
     * This is a placeholder for further integration.
     */
    public function uploadToThirdParty($service, $credentials)
    {
        // Example integration logic
        // $service could be AWS S3, Google Cloud, etc.
        // You would implement specific API calls here

        // This is just a placeholder
        throw new \Exception("Third-party integration is not implemented.");
    }

    /**
     * Extracts audio from a video file.
     */
    public function extractAudio($outputAudioFile)
    {
        $command = "{$this->ffmpegPath} -i {$this->filePath} -q:a 0 -map a {$outputAudioFile}";
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return $outputAudioFile;
        }
        throw new \Exception("Audio extraction failed: " . implode("\n", $output));
    }

    /**
     * Combines audio and video into one file.
     */
    public function combineAudioVideo($audioFilePath, $outputFile)
    {
        $command = "{$this->ffmpegPath} -i {$this->filePath} -i {$audioFilePath} -c:v copy -c:a aac -strict experimental {$outputFile}";
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return $outputFile;
        }
        throw new \Exception("Combining audio and video failed: " . implode("\n", $output));
    }

    /**
     * Resizes video resolution.
     */
    public function resizeVideo($width, $height, $outputFile)
    {
        $command = "{$this->ffmpegPath} -i {$this->filePath} -vf scale={$width}:{$height} {$outputFile}";
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return $outputFile;
        }
        throw new \Exception("Resizing video failed: " . implode("\n", $output));
    }
}
