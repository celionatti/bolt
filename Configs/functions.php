<?php

declare(strict_types=1);

use celionatti\Bolt\Bolt;
use celionatti\Bolt\View\View;
use celionatti\Bolt\Helpers\CSRF\Csrf;
use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\Sessions\Handlers\DefaultSessionHandler;

function bolt_env($data)
{
    if (isset($_ENV[$data])) {
        return $_ENV[$data];
    }

    return false;
}

function bv_uuid()
{
    // Generate 16 bytes of random data
    $data = random_bytes(16);

    // Set the version (4) and variant (10xxxxxx) bits
    $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

    // Get the current timestamp in microseconds
    $timestamp = microtime(true) * 10000;

    // Convert the timestamp to a 64-bit binary string without zero padding
    $timestampBinary = substr(pack('J', $timestamp), 2);

    // Replace the first 8 bytes with the timestamp
    $data = substr_replace($data, $timestampBinary, 0, 8);

    // Add some additional randomness
    $randomBytes = random_bytes(8);
    $data .= $randomBytes;

    // Format the UUID without dashes
    $uuid = vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));

    return "bv_{$uuid}";
}

function findFile($dir, $targetFile)
{
    while (true) {
        // $configPath = $dir . '/' . $targetFile;
        $configPath = "{$dir}/{$targetFile}";

        if (file_exists($configPath)) {
            return $configPath;
        }

        // Move up one level
        $parentDir = dirname($dir);

        // Check if we've reached the root directory
        if ($parentDir === $dir) {
            return null; // File not found
        }

        $dir = $parentDir;
    }
}

function get_root_dir()
{
    // Get the current file's directory
    $currentDirectory = __DIR__;

    // Navigate up the directory tree until you reach the project's root
    while (!file_exists("{$currentDirectory}/vendor")) {
        // Go up one level
        $currentDirectory = dirname($currentDirectory);

        // Check if you have reached the filesystem root (to prevent infinite loop)
        if ($currentDirectory === '/') {
            echo "Error: Project root not found.\n";
            exit;
        }
    }

    return $currentDirectory;
}

function esc_url($url)
{
    // Use filter_var to sanitize the URL
    $sanitized_url = filter_var($url, FILTER_SANITIZE_URL);

    // Check if the result is a valid URL
    if (filter_var($sanitized_url, FILTER_VALIDATE_URL) !== false) {
        return $sanitized_url;
    } else {
        // If the URL is not valid, return an empty string or handle it as needed
        return '';
    }
}

function esc_html($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esc_js($javascript)
{
    return json_encode($javascript, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

function esc($data, $context = 'html', $encoding = 'UTF-8')
{
    if (is_array($data) || is_object($data)) {
        // Handle arrays or objects by recursively calling the function
        foreach ($data as &$value) {
            $value = esc($value, $context, $encoding);
        }
        return $data;
    }

    switch ($context) {
        case 'html':
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, $encoding);
        case 'attr':
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, $encoding);
        case 'url':
            return esc_url($data);
        case 'js':
            return esc_js($data);
        default:
            return $data;
    }
}

function isCurrentPage($pageUrl)
{
    $currentUrl = $_SERVER['REQUEST_URI'];
    $parts = explode("/", $currentUrl);

    // Remove the first element if it's an empty string
    if (empty($parts[0])) {
        array_shift($parts);
    }

    // Use the first part of the URL for comparison if it's not empty
    $compareUrl = !empty($parts[0]) ? $parts[0] : $currentUrl;

    // Compare the current URL to the specified page URL
    return $compareUrl === $pageUrl;
}

function bolt_die($value, $message = '', $title = 'BOLT Error - Oops! Something went wrong.', $status_code = 500)
{
    http_response_code($status_code);

    $value = str_replace('"', '', $value);

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>{$title}</title>
        <style>
            body {
                margin: 0;
                font-family: Arial, sans-serif;
                background: rgb(21,69,152);
                background: linear-gradient(90deg, rgba(21,69,152,1) 0%, rgba(22,155,173,1) 26%, rgba(235,230,232,1) 37%, rgba(99,156,173,1) 45%, rgba(37,78,149,1) 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                overflow: hidden;
            }
    
            .error-container {
                width: 100%;
                max-width: 900px;
                height400px;
                background-color: #fff;
                border-radius: 5px;
                box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
                display: flex;
            }
    
            .left-section {
                flex: 2;
                background-color: rgb(21,69,152);
                background-image: linear-gradient(160deg, rgba(21,69,152,1) 0%, #80D0C7 50%, rgba(21,69,152,1) 100%);
                padding: 60px;
                text-align: center;
            }
    
            .right-section {
                flex: 1;
                background: rgb(21,69,152);
                background: linear-gradient(90deg, rgba(21,69,152,1) 0%, rgba(22,155,173,1) 26%, rgba(235,230,232,1) 37%, rgba(99,156,173,1) 45%, rgba(37,78,149,1) 100%);
                padding: 20px;
                text-align: center;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
    
            h1 {
                font-size: 36px;
                color: #fff;
            }
    
            p {
                font-size: 28px;
                color: #ccc;
            }
    
            img {
                max-width: 100px;
            }
    
            a {
                text-decoration: none;
                color: #fff;
            }
            /* Style for the call-to-action button */
            .btn-primary {
                background-color: #054774;
                color: #fff;
                padding: 1rem 2rem;
                font-size: 1.25rem;
                border: none;
                border-radius: 0.25rem;
                cursor: pointer;
                transition: background-color 0.3s;
                text-decoration: none;
            }
        
            .btn-primary:hover {
                background-color: #054762;
            }

            .error-title {
                color: #fff;
                font-size: 20px;
            }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <div class='left-section'>
            <div class='error-title'>{$title}</div>
                <h1>{$message}</h1>
                <p>" . print_r($value, true) . "</p>
            </div>
            <div class='right-section'>
                <svg
                    xmlns='http://www.w3.org/2000/svg'
                    version='1.1'
                    width='400'
                    height='300'
                    viewBox='0 0 400 300'
                    xml:space='preserve'
                >
                    <defs>
                        <linearGradient id='gradient1' x1='0%' y1='0%' x2='100%' y2='100%'>
                            <stop offset='0%' style='stop-color:#00BFFF; stop-opacity:1' />
                            <stop offset='100%' style='stop-color:#1E90FF; stop-opacity:1' />
                        </linearGradient>
                    </defs>

                    <rect x='0' y='0' width='400' height='300' fill='url(#gradient1)' />

                    <!-- Cartoonish Error Graphic -->
                    <g transform='translate(150, 70)'>
                        <ellipse cx='50' cy='50' rx='50' ry='50' fill='#fff' />
                        <circle cx='35' cy='35' r='8' fill='#000' />
                        <circle cx='65' cy='35' r='8' fill='#000' />
                        <path d='M 30 70 Q 50 90, 70 70' stroke='#000' stroke-width='4' fill='none' />
                    </g>

                    <!-- Dynamic Error Message -->
                    <text
                        x='50%'
                        y='200'
                        font-family='Arial Black, Gadget, sans-serif'
                        font-size='20'
                        font-weight='bold'
                        font-style='italic'
                        fill='#ffffff'
                        text-anchor='middle'
                    >
                        Bolt Framework (PHPStrike)
                    </text>

                    <text
                        x='50%'
                        y='250'
                        font-family='serif, sans-serif'
                        font-size='18'
                        font-weight='bold'
                        font-style='italic'
                        fill='#ffffff'
                        text-anchor='middle'
                    >
                        Something Went Wrong!, But We Move!
                    </text>
                </svg>
            </div>
        </div>
    </body>
    </html>
    ";

    die;
}

function dump($value, $die = true)
{
    $frameworkDetails = [
        'Framework' => 'Bolt PHP Framework',
        'Version' => '1.0.8',
        'Environment' => 'Development',
        'PHP Version' => phpversion(),
        'Timestamp' => date('Y-m-d H:i:s')
    ];

    echo "<style>
    body {
        background-color: #282828;
        color: #52e3f6;
        font-family: Menlo, Monaco, monospace;
        margin: 0;
        padding: 16px;
    }
    .dump-container {
        display: flex;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 5px;
    }
    .details-column, .dump-column {
        padding: 16px;
        box-sizing: border-box;
    }
    .details-column {
        width: 30%;
        background-color: #1e1e1e;
        border-right: 1px solid #444;
        overflow-y: auto;
    }
    .dump-column {
        width: 70%;
        background-color: #282828;
        overflow-x: auto;
    }
    .details-column ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .details-column li {
        margin-bottom: 10px;
    }
    pre {
        margin: 0;
        word-wrap: normal;
        white-space: pre;
        direction: ltr;
        line-height: 1.2;
    }
    </style>";

    echo "<div class='dump-container'>
            <div class='details-column'>
                <ul>";

    foreach ($frameworkDetails as $key => $detail) {
        echo "<li><strong>$key:</strong> $detail</li>";
    }

    echo "    </ul>
            </div>
            <div class='dump-column'>
                <pre>";

    var_dump($value);

    echo "    </pre>
            </div>
          </div>";

    if ($die) {
        die;
    }
}

function redirect($url, $status_code = 302, $headers = [], $query_params = [], $exit = true)
{
    // Ensure a valid HTTP status code is used
    if (!is_numeric($status_code) || $status_code < 100 || $status_code >= 600) {
        $status_code = 302; // Default to a temporary (302) redirect
    }

    // Build the query string from the provided query parameters
    $query_string = !empty($query_params) ? '?' . http_build_query($query_params) : '';

    // Prepare and set custom headers
    $headers['Location'] = $url . $query_string;
    $headers['Status'] = $status_code . ' ' . http_response_code($status_code);

    // Send headers
    foreach ($headers as $key => $value) {
        header($key . ': ' . $value, true);
    }

    // Optionally exit to prevent further script execution
    if ($exit) {
        exit;
    }
}

function old_value(string $key, $default = '', string $type = 'post', string $dataType = 'string'): mixed
{
    // Define the input sources and their corresponding arrays
    $sources = [
        'post' => $_POST,
        'get' => $_GET,
        'session' => $_SESSION,
        // Add more sources as needed (e.g., 'cookie', 'custom_source', etc.)
    ];

    // Validate the input source
    if (!array_key_exists($type, $sources)) {
        throw new InvalidArgumentException("Invalid input source: $type");
    }

    // Get the value from the specified input source
    $value = $sources[$type][$key] ?? $default;

    // Cast the retrieved value to the specified data type
    switch ($dataType) {
        case 'int':
            return (int)$value;
        case 'float':
            return (float)$value;
        case 'bool':
            return (bool)$value;
        case 'array':
            if (!is_array($value)) {
                return [$value];
            }
            return $value;
        case 'string':
        default:
            return (string)$value;
    }
}

function old_select(string $key, string $value, $default = '', string $type = 'post', bool $strict = true): string
{
    $sources = [
        'post' => $_POST,
        'get' => $_GET,
        // Add more sources as needed (e.g., 'session', 'cookie', etc.)
    ];

    if (!array_key_exists($type, $sources)) {
        throw new InvalidArgumentException("Invalid input source: $type");
    }

    $inputValue = $sources[$type][$key] ?? $default;

    // Check if the selected value matches the input value or if it matches the default value
    $isSelected = ($strict ? $inputValue === $value : $inputValue == $value) || ($default == $value);

    return $isSelected ? 'selected' : '';
}


function old_checked(string $key, string $value, $default = '', string $type = 'post', bool $strict = true): string
{
    // Define the input sources and their corresponding arrays
    $sources = [
        'post' => $_POST,
        'get' => $_GET,
        // Add more sources as needed (e.g., 'session', 'cookie', etc.)
    ];

    // Validate the input source
    if (!array_key_exists($type, $sources)) {
        throw new InvalidArgumentException("Invalid input source: $type");
    }

    // Get the value from the specified input source
    $inputValue = $sources[$type][$key] ?? $default;

    // Determine if the checked value matches the input value
    $isChecked = ($strict ? $inputValue === $value : $inputValue == $value) || ($default == $value);

    return $isChecked ? 'checked' : '';
}

function get_image(?string $path = null, string $type = 'post'): string
{
    // Define default image paths
    $defaultImageMap = [
        'post' => '/assets/img/no_image.jpg',
        'avatar' => '/assets/img/avatar.jpg',
        'male' => '/assets/img/user_male.jpg',
        'female' => '/assets/img/user_female.jpg',
        'icon' => '/assets/img/favicon.ico',
    ];

    // Set the image path to the provided $path or an empty string if null
    $path = $path ?? '';

    // Check if the provided $path exists, and return it if found
    if (!empty($path) && file_exists($path)) {
        return URL_ROOT . '/' . $path;
    }

    // If $type exists in the defaultImageMap, return the corresponding default image
    if (empty($path) && isset($defaultImageMap[$type])) {
        return URL_ROOT . $defaultImageMap[$type];
    }
    return $path;
}

function get_assets_directory($directory): string
{
    return Bolt::$bolt->assetManager->getAssetPath("{$directory}");
}

function asset($path): string
{
    return get_assets_directory(DIRECTORY_SEPARATOR . $path);
}

function get_date(?string $date = null, string $format = "jS M, Y", string $timezone = "UTC"): string
{
    $date ?? '';

    if (empty($date)) {
        return '';
    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return 'Invalid Date';
    }

    $dateTime = new DateTime();
    $dateTime->setTimestamp($timestamp);
    $dateTime->setTimezone(new DateTimeZone($timezone));

    return $dateTime->format($format);
}

function validate_csrf_token($data, $toast = true)
{
    // Assuming that the Csrf class is defined and instantiated somewhere
    $csrf = new Csrf();

    // Get the referring URL or set a default redirect URL
    $redirect = $_SERVER['HTTP_REFERER'] ?? '';

    // Validate the CSRF token from the provided data
    if (!$csrf->validateToken($data["__bv_csrf_token"])) {
        $message = "CSRF Token Expires";

        // Display a toast message or use `bolt_die` to terminate with an error message
        if ($toast) {
            toast("info", $message);
        } else {
            bolt_die($message);
        }

        // Redirect to the referring URL or a default location
        redirect($redirect);
    }
}

/**
 * For displaying a color message, on the screen or in the console.
 *
 * @param string $message
 * @param boolean $die
 * @param boolean $timestamp
 * @param string $title
 * @return void
 */
function console_logger(string $message, bool $die = false, bool $timestamp = true, string $title = ''): void
{
    // Initialize output string
    $output = '';

    // Format the message with initial uppercase
    $formattedMessage = ucfirst($message);

    // Calculate total message length for padding and borders
    $messageLength = strlen($formattedMessage);
    $borderLength = $messageLength + 6; // Borders on both sides

    // Create the title section with light blue background color and padding
    $title = strtoupper($title);
    if ($title) {
        $titlePadding = str_repeat(' ', 2);
        $titleSection = "\033[1;37;46m{$titlePadding}{$title}{$titlePadding}\033[0m ";
    } else {
        $titleSection = '';
    }

    // Create the timestamp with a more friendly format
    $friendlyTimestamp = $timestamp ? "[" . date("M d, Y - H:i:s") . "] - " : '';

    // Build the top border with asterisks
    $topBorder = str_repeat('*', $borderLength) . PHP_EOL;

    // Calculate padding for centering the message
    $padding = str_repeat(' ', intval(floor(($borderLength - $messageLength) / 2))); // Ensure integer value

    // Build the middle content with borders and padding
    $middleContent = "*{$padding}{$formattedMessage}{$padding}*" . PHP_EOL;

    // Build the bottom border with asterisks
    $bottomBorder = str_repeat('*', $borderLength) . PHP_EOL;

    // Colorize output to light blue
    $output .= "\033[1;36m"; // Light blue color

    // Concatenate all parts: top border, title section, timestamp, middle content, bottom border
    $output .= "{$topBorder}{$titleSection}\033[1;36m{$friendlyTimestamp}{$middleContent}{$bottomBorder}";

    // Reset color after the message
    $output .= "\033[0m";

    // Output the formatted message
    echo $output . PHP_EOL;

    // Exit script if die flag is set
    if ($die) {
        die();
    }
}

function load_required_files($directoryPath)
{
    $requiredFileExtensions = ['php', 'txt', 'html', 'js', 'css']; // Define the file extensions you consider as required

    if (!is_dir($directoryPath)) {
        return []; // Return an empty array if the directory doesn't exist
    }

    $requiredFiles = [];

    // Scan the directory for files
    $files = scandir($directoryPath);

    foreach ($files as $file) {
        // Check if the file has one of the required extensions
        $fileInfo = pathinfo($file);
        if (in_array($fileInfo['extension'], $requiredFileExtensions)) {
            $requiredFiles[] = $file;
        }
    }

    return $requiredFiles;
}

/**
 * Bolt View Method
 * for rendering a view template
 * and can also set layout.
 *
 * @param string $path
 * @param array $data
 * @param string $layout
 * @return void
 */
function view(string $path, array $data = [], string $layout = 'default'): void
{
    $view = new View();

    $view->setLayout($layout);

    $view->render($path, $data);
}

function partials(string $path, $params = [])
{
    $view = new View();

    $view->partial($path, $params);
}

/**
 * Hash Password with salt.
 *
 * @param string $password
 * @param integer $cost Adjust the cost factor as needed (higher is slower but more secure)
 * @return void
 */
function hashPassword(string $password, $cost = 12): array
{
    $salt = bin2hex(random_bytes(16)); // Generate a random salt
    $hash = password_hash($password . $salt, PASSWORD_BCRYPT, ['cost' => $cost]);

    if ($hash === false) {
        throw new BoltException('Password hash could not be created.');
    }

    return [
        'hash' => $hash,
        'salt' => $salt
    ];
}

/**
 * Verify the Hash Password.
 *
 * @param string $password
 * @param string $hashedPassword
 * @return boolean
 */
function verifyPassword(string $password, string $hashedPassword): bool
{
    list($hash, $salt) = explode('$', $hashedPassword, 2);

    return password_verify($password . $salt, $hashedPassword);
}

function filterData($data, $filterCriteria)
{
    $filteredData = [];

    foreach ($data as $row) {
        $match = true;

        foreach ($filterCriteria as $key => $value) {
            if (!isset($row->$key) || $row->$key !== $value) {
                $match = false;
                break;
            }
        }

        if ($match) {
            $filteredData[] = $row;
        }
    }

    return $filteredData;
}

function retrieveSessionData($key, $default = [])
{
    // return Bolt::$bolt->session->get($key, $default);
    return $_SESSION[$key] ?? $default;
}

function storeSessionData($key, $data)
{
    // Bolt::$bolt->session->set($key, $data);
    $_SESSION[$key] = $data;
}

function unsetSessionArrayData(array $keys)
{
    foreach ($keys as $key) {
        unset($_SESSION[$key]);
    }
}

function sanitizeData($data)
{
    if (is_array($data)) {
        return array_map('sanitizeData', $data);
    } else {
        return htmlspecialchars($data);
    }
}

function toast($type, $message)
{
    // Validate the message type
    $validTypes = ['success', 'error', 'info', 'warning'];
    if (!in_array($type, $validTypes)) {
        throw new BoltException('Invalid toastr message type');
    }

    // Store the message, type, and attributes in the session
    $_SESSION['__bv_flash_toastr'] = [
        'message' => $message,
        'type' => $type,
    ];

    $toastr = $_SESSION['__bv_flash_toastr'] ?? null;
    return $toastr;
}

function setFormMessage($value): void
{
    $session = bv_session();
    $session->setFormMessage($value);
}

function getFormMessage()
{
    $session = bv_session();
    $session->getFormMessage();
}

function formatCurrency($amount, $currencyCode = "NGN")
{
    // Define currency symbols
    $currencySymbols = [
        'USD' => '$', // Dollar
        'NGN' => '₦', // Naira
        'EUR' => '€', // Euro
        // Add more currencies as needed
    ];

    // Check if the provided currency code is supported
    if (array_key_exists($currencyCode, $currencySymbols)) {
        // Convert the amount to float (if it's in string format)
        $amount = floatval($amount);

        // Format the amount with 2 decimal places and use commas for thousands
        $formattedAmount = number_format($amount, 2);

        // Concatenate the currency symbol with the formatted amount
        $formattedCurrency = $currencySymbols[$currencyCode] . $formattedAmount;

        return $formattedCurrency;
    } else {
        // If the currency code is not supported, return an error message
        return 'Unsupported currency code';
    }
}

function calReadTime($text, $wordsPerMinute = 200, $contentCategory = 'generic', $timeUnit = ' Min To Read')
{
    // Function to count the number of words in the text
    $countWords = function ($text) {
        return str_word_count(strip_tags($text));
    };

    // Adjust words per minute based on content category
    $categorySpeeds = [
        'generic' => 200,    // Default speed for generic content
        'technical' => 150,  // Adjust for technical content
        'leisure' => 250      // Adjust for leisurely reading
        // Add more categories as needed
    ];

    $categorySpeed = $categorySpeeds[$contentCategory] ?? $wordsPerMinute;

    $wordCount = $countWords($text);

    if ($wordCount <= 0 || $categorySpeed <= 0) {
        return "Invalid input";
    }

    $minutes = ceil($wordCount / $categorySpeed);

    return "{$minutes}{$timeUnit}";
    // return $minutes . ($minutes == 1 ? $timeUnit : $timeUnit .'s');
}

/**
 * Filter text to search for certain patterns.
 *
 * @param string $text The text to be filtered.
 * @param array $patterns An array of patterns to search for.
 * @return bool True if any of the patterns are found, false otherwise.
 */
function filterText($text, array $patterns)
{
    foreach ($patterns as $pattern) {
        if (strpos($text, $pattern) !== false) {
            // Pattern found in the text
            return true;
        }
    }

    // None of the patterns found in the text
    return false;
}

function randomizeNumber($existingIDs, $minID = 100000, $maxID = 999999)
{
    // Helper function to generate a random number within the specified range
    function getRandomNumber($min, $max)
    {
        return mt_rand($min, $max);
    }

    do {
        $newID = getRandomNumber($minID, $maxID);
    } while (in_array($newID, $existingIDs));

    return $newID;
}

function stringToken($length = 64)
{
    // Define the characters to be used in the token
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $token = '';

    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $token;
}

function generateKeyPhrase($numWords = 10)
{
    // Predefined list of words (expand this list as needed)
    $wordList = [
        // Fruits
        'apple',
        'banana',
        'cherry',
        'date',
        'elderberry',
        'fig',
        'grape',
        'honeydew',
        'kiwi',
        'lemon',
        'mango',
        'nectarine',
        'orange',
        'papaya',
        'quince',
        'raspberry',
        'strawberry',
        'tangerine',
        'ugli',
        'vanilla',
        'watermelon',
        'xigua',
        'yam',
        'zucchini',
        'apricot',
        'blackberry',
        'cantaloupe',
        'dragonfruit',
        'blueberry',
        'coconut',
        'currant',
        'durian',
        'gooseberry',
        'grapefruit',
        'guava',
        'jackfruit',
        'lime',
        'lychee',
        'mandarin',
        'mulberry',
        'olive',
        'passionfruit',
        'peach',
        'pear',
        'pineapple',
        'plum',
        'pomegranate',
        'starfruit',
        'soursop',
        'tamarind',

        // Places
        'paris',
        'london',
        'tokyo',
        'newyork',
        'sydney',
        'mumbai',
        'cairo',
        'moscow',
        'rome',
        'berlin',
        'amsterdam',
        'barcelona',
        'dubai',
        'beijing',
        'singapore',
        'losangeles',
        'chicago',
        'toronto',
        'miami',
        'seoul',
        'bangkok',
        'istanbul',
        'madrid',
        'boston',
        'vienna',

        // Animals
        'lion',
        'tiger',
        'elephant',
        'giraffe',
        'zebra',
        'kangaroo',
        'panda',
        'dolphin',
        'whale',
        'shark',
        'eagle',
        'falcon',
        'owl',
        'wolf',
        'bear',
        'fox',
        'rabbit',
        'squirrel',
        'koala',
        'leopard',
        'cheetah',
        'buffalo',
        'rhinoceros',
        'hippopotamus',
        'crocodile',
        'alligator',

        // Names
        'alice',
        'bob',
        'charlie',
        'david',
        'eve',
        'frank',
        'grace',
        'heidi',
        'ivan',
        'judy',
        'ken',
        'laura',
        'mike',
        'nancy',
        'oscar',
        'peggy',
        'quentin',
        'rachel',
        'sam',
        'tom',
        'ursula',
        'victor',
        'wendy',
        'xander',
        'yvonne',
        'zach',

        // Foods
        'pizza',
        'burger',
        'sushi',
        'pasta',
        'tacos',
        'burrito',
        'ramen',
        'steak',
        'sandwich',
        'salad',
        'soup',
        'omelette',
        'pancakes',
        'waffles',
        'bacon',
        'sausages',
        'noodles',
        'dumplings',
        'paella',
        'falafel',
        'hummus',
        'lasagna',
        'risotto',
        'curry',
        'quiche',
        'frittata',

        // Miscellaneous
        'galaxy',
        'universe',
        'planet',
        'comet',
        'asteroid',
        'nebula',
        'quasar',
        'blackhole',
        'volcano',
        'earthquake',
        'tsunami',
        'hurricane',
        'tornado',
        'avalanche',
        'blizzard',
        'storm',
        'desert',
        'forest',
        'ocean',
        'river',
        'mountain',
        'valley',
        'canyon',
        'lake',
        'waterfall'
    ];

    // Check if the requested number of words exceeds the available unique words
    if ($numWords > count($wordList)) {
        throw new Exception("Number of words requested exceeds the number of available unique words.");
    }

    $wordListLength = count($wordList);
    $keyPhrase = [];

    // Generate unique random words for the key phrase
    $usedIndices = [];
    for ($i = 0; $i < $numWords; $i++) {
        do {
            $randomIndex = random_int(0, $wordListLength - 1);
        } while (in_array($randomIndex, $usedIndices));

        $usedIndices[] = $randomIndex;
        $keyPhrase[] = $wordList[$randomIndex];
    }

    // Return the generated key phrase as a single string
    return implode(' ', $keyPhrase);
}

function compressToZip($source, $destination)
{
    $zip = new ZipArchive();
    if ($zip->open($destination, ZipArchive::CREATE) !== TRUE) {
        return false;
    }
    $zip->addFile($source, basename($source));
    $zip->close();
    return $destination;
}

function pluck(array $items, $column)
{
    return array_map(function ($item) use ($column) {
        return $item->{$column};
    }, $items);
}

function request()
{
    return \celionatti\Bolt\Http\Request::instance();
}

function response()
{
    return \celionatti\Bolt\Http\Response::instance();
}

function renderComponent(string $componentClass, array $data = [], array $slots = []): string
{
    if (!class_exists($componentClass)) {
        throw new \Exception("Component class {$componentClass} not found.");
    }

    $component = new $componentClass($data, $slots);

    if (!method_exists($component, 'render')) {
        throw new \Exception("The component class {$componentClass} does not have a render method.");
    }

    return $component->render();
}

// function route($to)
// {
//     // Sanitize the passed-in parameter
//     $to = filter_var($to, FILTER_SANITIZE_URL);

//     // Check if the request method is GET
//     if ($_SERVER['REQUEST_METHOD'] === 'GET') {
//         return $to;
//     }

//     // Throw an exception if the request method is not GET
//     throw new BoltException("Method Not Allowed", 405, "info");
// }

function route($to, array $params = [])
{
    // Sanitize the base URL
    $to = filter_var($to, FILTER_SANITIZE_URL);

    // Build the query string from the parameters
    if (!empty($params)) {
        $queryString = http_build_query($params);
        $to = $to . (strpos($to, '?') === false ? '?' : '&') . $queryString;
    }

    // Check if the request method is GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return $to;
    }

    // Throw an exception if the request method is not GET
    throw new BoltException("Method Not Allowed", 405, "info");
}

function bv_session()
{
    return new DefaultSessionHandler();
}
