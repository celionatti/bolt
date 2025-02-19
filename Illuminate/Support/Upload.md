Certainly! The `Upload` class you've provided is a robust PHP class designed to handle file uploads, including validation, storage, image manipulation (like thumbnail generation, cropping, and adding watermarks), and more. Below, I'll guide you through how to effectively use this class in your project, step by step.

### Table of Contents
1. [Prerequisites](#prerequisites)
2. [Instantiation](#instantiation)
3. [Configuration](#configuration)
4. [Uploading Files](#uploading-files)
5. [Handling Chunked Uploads](#handling-chunked-uploads)
6. [Image Manipulation](#image-manipulation)
    - [Generating Thumbnails](#generating-thumbnails)
    - [Cropping Images](#cropping-images)
    - [Adding Watermarks](#adding-watermarks)
7. [Retrieving and Deleting Files](#retrieving-and-deleting-files)
8. [Custom Validations](#custom-validations)
9. [Error Handling](#error-handling)
10. [Complete Example](#complete-example)

---

### Prerequisites

Before using the `Upload` class, ensure you have:

1. **PHP Environment**: PHP 7.4 or higher is recommended, given the use of type declarations.
2. **File System Permissions**: Ensure that the directories where files will be uploaded (`uploads/`, `thumbnails/`, etc.) are writable by the PHP process.
3. **GD Library**: For image manipulation functions (like `imagecreatefromjpeg`, `imagejpeg`, etc.), the GD library must be enabled in your PHP installation.

### Instantiation

To use the `Upload` class, you'll first need to include it in your project. Assuming you're using Composer and autoloading, you can instantiate the class as follows:

```php
<?php

use celionatti\Bolt\Illuminate\Support\Upload;

// Assuming a form with input name="files"
$files = $_FILES['files'];

// Instantiate the Upload class
$upload = new Upload($files);
```

**Note**: Replace `'files'` with the actual name attribute of your file input field.

### Configuration

The `Upload` class offers various configuration methods to tailor the upload process to your needs. These methods return the instance itself, allowing for method chaining.

```php
$upload = (new Upload($_FILES['files']))
    ->setMaxSize(10 * 1024 * 1024) // Set max file size to 10MB
    ->setAllowedTypes(['image/jpeg', 'image/png', 'application/pdf', 'image/gif']) // Allow additional MIME types
    ->setUploadDir('public/uploads/') // Specify a custom upload directory
    ->setThumbnailDir('public/thumbnails/') // Specify a custom thumbnail directory
    ->setOverwrite(true); // Allow overwriting existing files
```

### Uploading Files

After configuring, you can proceed to store the uploaded files.

```php
try {
    // Store files with unique filenames
    $storedFiles = $upload->store();

    // Or, store files with their original names
    // $storedFiles = $upload->store(false);

    echo "Files uploaded successfully:";
    print_r($storedFiles);
} catch (Exception $e) {
    echo "Upload failed: " . $e->getMessage();
}
```

**Explanation**:
- **`store($rename = true)`**: By default, files are renamed to ensure uniqueness. Pass `false` to retain original filenames.

### Handling Chunked Uploads

For large files, you might want to handle uploads in chunks. Here's how you can use the `storeChunked` method.

**Client-Side Consideration**: Ensure that your front-end is set up to send file chunks along with the total number of chunks and the filename.

```php
<?php

use celionatti\Bolt\Illuminate\Support\Upload;

// Instantiate the Upload class
$upload = new Upload($_FILES['file']);

// Example variables (these should come from your front-end)
$chunk = file_get_contents($_FILES['file']['tmp_name']); // Current chunk data
$totalChunks = 5; // Total number of chunks
$filename = 'large_file.zip'; // Original filename

try {
    $result = $upload->storeChunked($chunk, $totalChunks, $filename);
    if ($result) {
        echo "File uploaded successfully as {$result}";
    } else {
        echo "Chunk uploaded successfully. Awaiting more chunks.";
    }
} catch (Exception $e) {
    echo "Chunk upload failed: " . $e->getMessage();
}
```

**Explanation**:
- **`storeChunked($chunk, $totalChunks, $filename)`**: Appends the current chunk to a temporary file. Once all chunks are received, it renames the temporary file to the final filename.

### Image Manipulation

The `Upload` class provides methods for generating thumbnails, cropping images, and adding watermarks. These operations are only applicable to image files.

#### Generating Thumbnails

```php
try {
    $thumbnailPath = $upload->generateThumbnail('unique_filename.jpg', 150, 150);
    echo "Thumbnail created at: " . $thumbnailPath;
} catch (Exception $e) {
    echo "Thumbnail generation failed: " . $e->getMessage();
}
```

**Parameters**:
- **`$filename`**: The name of the uploaded file.
- **`$width` and `$height`**: Desired dimensions for the thumbnail.

#### Cropping Images

```php
try {
    // Crop 100x100 pixels from position (50, 50)
    $croppedPath = $upload->cropImage('unique_filename.jpg', 50, 50, 100, 100);
    echo "Image cropped at: " . $croppedPath;
} catch (Exception $e) {
    echo "Image cropping failed: " . $e->getMessage();
}
```

**Parameters**:
- **`$filename`**: The name of the uploaded file.
- **`$x` and `$y`**: Starting coordinates for cropping.
- **`$width` and `$height`**: Dimensions of the cropped area.

#### Adding Watermarks

```php
try {
    $watermarkImage = 'path/to/watermark.png';
    $watermarkedPath = $upload->addWatermark('unique_filename.jpg', $watermarkImage);
    echo "Watermark added at: " . $watermarkedPath;
} catch (Exception $e) {
    echo "Adding watermark failed: " . $e->getMessage();
}
```

**Parameters**:
- **`$filename`**: The name of the uploaded file.
- **`$watermarkImage`**: Path to the watermark image.

**Note**: The watermark image should be in a supported format (JPEG or PNG).

### Retrieving and Deleting Files

#### Retrieving a File

To get the full path of an uploaded file:

```php
try {
    $filepath = $upload->retrieve('unique_filename.jpg');
    echo "File is located at: " . $filepath;
} catch (Exception $e) {
    echo "Retrieving file failed: " . $e->getMessage();
}
```

#### Deleting a File

To delete an uploaded file:

```php
try {
    if ($upload->delete('unique_filename.jpg')) {
        echo "File deleted successfully.";
    }
} catch (Exception $e) {
    echo "Deleting file failed: " . $e->getMessage();
}
```

### Custom Validations

You can add custom validation logic by passing a callback function to the `addCustomValidation` method.

```php
$upload->addCustomValidation(function($file) {
    // Example: Ensure the filename does not contain "forbidden"
    if (strpos($file['name'], 'forbidden') !== false) {
        return false;
    }
    return true;
});

try {
    $storedFiles = $upload->store();
    echo "Files uploaded successfully:";
    print_r($storedFiles);
} catch (InvalidArgumentException $e) {
    echo "Custom validation failed: " . $e->getMessage();
} catch (Exception $e) {
    echo "Upload failed: " . $e->getMessage();
}
```

**Explanation**:
- The callback receives the `$file` array and should return `true` if the validation passes or `false` otherwise.
- If any custom validation fails, an `InvalidArgumentException` is thrown.

### Error Handling

The `Upload` class uses exceptions to handle errors. It's essential to wrap your upload logic within `try-catch` blocks to gracefully handle any issues.

```php
try {
    $storedFiles = $upload->store();
    echo "Files uploaded: " . implode(', ', $storedFiles);
} catch (InvalidArgumentException $e) {
    // Handle custom validation errors
    echo "Validation Error: " . $e->getMessage();
} catch (Exception $e) {
    // Handle other errors
    echo "Error: " . $e->getMessage();
}
```

### Complete Example

Below is a comprehensive example that ties everything together. This example assumes you're working within a Laravel controller, but the principles apply to any PHP project.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use celionatti\Bolt\Illuminate\Support\Upload;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        // Validate that the request has files
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file',
        ]);

        try {
            // Instantiate the Upload class with the uploaded files
            $upload = (new Upload($request->file('files')))
                ->setMaxSize(10 * 1024 * 1024) // 10MB
                ->setAllowedTypes(['image/jpeg', 'image/png', 'application/pdf'])
                ->setUploadDir(public_path('uploads/'))
                ->setThumbnailDir(public_path('thumbnails/'))
                ->setOverwrite(false)
                ->addCustomValidation(function($file) {
                    // Example: Limit to filenames without spaces
                    return strpos($file['name'], ' ') === false;
                });

            // Store the files
            $storedFiles = $upload->store();

            // Optionally, generate thumbnails for image files
            foreach ($storedFiles as $filename) {
                $filePath = $upload->retrieve($filename);
                $mimeType = mime_content_type($filePath);
                if (in_array($mimeType, ['image/jpeg', 'image/png'])) {
                    $upload->generateThumbnail($filename, 200, 200);
                }
            }

            return response()->json([
                'success' => true,
                'files' => $storedFiles,
            ], 200);

        } catch (InvalidArgumentException $e) {
            // Handle custom validation errors
            return response()->json([
                'success' => false,
                'error' => 'Validation Error: ' . $e->getMessage(),
            ], 422);
        } catch (Exception $e) {
            // Handle other errors
            return response()->json([
                'success' => false,
                'error' => 'Upload Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
```

**Explanation**:

1. **Validation**: Ensures that the `files` input is present and contains files.
2. **Instantiation & Configuration**: Sets up the `Upload` class with desired settings.
3. **Custom Validation**: Adds a validation to reject files with spaces in their filenames.
4. **Storing Files**: Attempts to store the files and captures any exceptions.
5. **Generating Thumbnails**: For image files, generates thumbnails.
6. **Response**: Returns a JSON response indicating success or failure.

### Conclusion

The `Upload` class is a versatile tool for managing file uploads in PHP applications. By following the steps outlined above, you can effectively integrate it into your project, customize it to your needs, and handle various file-related operations with ease. Remember to handle exceptions appropriately to ensure a smooth user experience and maintain the security and integrity of your application.


$uploader = new Upload('/path/to/uploads');
$result = $uploader->uploadFile('file_input');

if (!$result['success']) {
    // Handle error
    $error = $result['message'];
    return;
}

// Handle successful upload
$filePath = $result['path'];

// In controller
$uploader = new Upload('/uploads');

// Handle thumbnail generation
$result = $uploader->generateThumbnail('image.jpg', 200, 200);
if (!$result['success']) {
    // Handle error
    $error = $result['message'];
    return;
}
$thumbnailPath = $result['path'];

// Handle encryption
$encryptResult = $uploader->encryptFile('document.pdf', 'secret-key');
if (!$encryptResult['success']) {
    // Handle error
}
