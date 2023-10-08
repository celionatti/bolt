<?php

private function create_folders_n_file($path)
    {
        $parts = explode("/", $path);
        $numParts = count($parts);

        if ($numParts === 1) {
            // If there is only one part (no "/"), treat it as the filename
            $filename = $path;
            $fullPath = $filename;

            // Create the file if it doesn't exist
            if (!file_exists($fullPath)) {
                file_put_contents($fullPath, '');
            }

            return $fullPath;
        }

        // Initialize the root path
        $currentPath = '';

        for ($i = 0; $i < $numParts - 1; $i++) {
            // Append each part to the current path
            $currentPath .= $parts[$i] . '/';

            // Create the directory if it doesn't exist
            if (!is_dir($currentPath)) {
                mkdir($currentPath);
            }
        }

        // The last part is the filename
        $filename = $parts[$numParts - 1];

        // Create the file within the last directory
        $fullPath = $currentPath . $filename;
        if (!file_exists($fullPath)) {
            file_put_contents($fullPath, '');
        }

        return $fullPath;
    }

    private function create_file($path)
    {
        $parts = explode('/', $path);
        $filePath = '';
        $basePath = __DIR__; // You can specify your base directory here

        foreach ($parts as $part) {
            $filePath = rtrim($filePath, '/') . '/' . $part;

            if (!is_dir($basePath . $filePath)) {
                mkdir($basePath . $filePath);
            }
        }

        $fullFilePath = $basePath . $filePath . '.txt'; // Append the file extension you want

        if (!file_exists($fullFilePath)) {
            file_put_contents($fullFilePath, $fileContent);
            echo "File created successfully: $fullFilePath";
        } else {
            echo "File already exists: $fullFilePath";
        }
    }