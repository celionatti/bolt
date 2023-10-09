<?php

$filename = 'your_existing_file.txt';

// Open the file in append mode
$file = fopen($filename, 'a');

if ($file) {
    // Content to be added to the file
    $newContent = "This is new content that will be appended to the file.\n";

    // Write the new content to the file
    fwrite($file, $newContent);

    // Close the file
    fclose($file);

    echo "New content has been appended to the file.";
} else {
    echo "Unable to open the file for writing.";
}
