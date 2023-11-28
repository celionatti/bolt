<?php

declare(strict_types=1);

use celionatti\Bolt\Bolt;
use celionatti\Bolt\Helpers\Csrf;
use celionatti\Bolt\View\BoltView;
use celionatti\Bolt\BoltException\BoltException;

function bolt_env($data)
{
    if (isset($_ENV[$data])) {
        return $_ENV[$data];
    }

    return false;
}

function generateUuidV4()
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

    return $uuid;
}

function findFile($dir, $targetFile)
{
    while (true) {
        $configPath = $dir . '/' . $targetFile;

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
    while (!file_exists($currentDirectory . '/vendor')) {
        // Go up one level
        $currentDirectory = dirname($currentDirectory);

        // Check if you have reached the filesystem root (to prevent infinite loop)
        if ($currentDirectory === '/') {
            echo "Error: Project root not found.\n";
            exit(1);
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

function esc_sql($sql)
{
    global $npdb;

    if (isset($npdb)) {
        return $npdb->prepare($sql);
    } else {
        // Handle the case where $npdb is not available, or customize as needed
        return $sql;
    }
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
        case 'sql':
            global $wpdb;
            return esc_sql($data);
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
                background: linear-gradient(45deg, #ff5733, #007bff);
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
                background-color: #025192;
                padding: 60px;
                text-align: center;
            }
    
            .right-section {
                flex: 1;
                background-color: #ff5733;
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
                <img src='/assets/img/404.svg' alt='Error Icon'>
            </div>
        </div>
    </body>
    </html>
    ";

    die;
}

function dd($value): void
{
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: 'Arial', sans-serif;
                margin: 0;
                padding: 0;
            }

            .sf-dump-container {
                display: grid;
                grid-template-columns: 1fr 1fr; /* Two equal-width columns */
                height: 100vh;
            }

            .sf-dump {
                font: 13px Menlo, Monaco, monospace;
                direction: ltr;
                text-align: left;
                white-space: pre;
                word-wrap: normal;
                background: #282828;
                color: #eeeeee;
                line-height: 1.2;
                margin: 0;
                padding: 16px;
                border-radius: 5px;
                overflow: hidden;
                z-index: 100000;
                grid-column: 2; /* Specify the column for the dump content */
            }

            .sf-dump-two {
                font: 12px 'Arial', sans-serif; /* Use a standard font for readability */
                background: #f0f0f0; /* Change to your desired background color */
                color: #333; /* Change to your desired text color */
                line-height: 1.2;
                margin: 0;
                padding: 16px;
                border-radius: 5px;
                overflow: hidden;
                z-index: 100000;
                grid-column: 1; /* Specify the column for the dump content */
            }

            .sf-dump span {
                display: inline;
            }

            .sf-dump a {
                color: #52e3f6;
                text-decoration: none;
            }

            .sf-dump a:hover {
                text-decoration: underline;
            }

            .sf-dump a:visited {
                color: #5e84ea;
            }

            .sf-dump .sf-dump-public {
                color: #568f3e;
            }

            .sf-dump .sf-dump-protected {
                color: #568f3e;
            }

            .sf-dump .sf-dump-private {
                color: #568f3e;
            }

            .sf-dump .sf-dump-ellipsis {
                font-weight: bold;
                color: #52e3f6;
            }

            .sf-dump .sf-dump-numeric {
                color: #a0a0a0;
            }

            .sf-dump .sf-dump-null {
                color: #aa0d91;
            }

            .sf-dump .sf-dump-bool {
                color: #4d73bf;
            }

            .sf-dump .sf-dump-resource {
                color: #6f42c1;
            }

            .sf-dump .sf-dump-string {
                color: #df9355;
            }

            .sf-dump .sf-dump-key {
                color: #a0a0a0;
            }

            .sf-dump .sf-dump-meta {
                color: #b729d9;
            }

            .sf-dump .sf-dump-public.sf-dump-ellipsis,
            .sf-dump .sf-dump-protected.sf-dump-ellipsis,
            .sf-dump .sf-dump-private.sf-dump-ellipsis {
                color: #52e3f6;
            }

            .sf-dump .sf-dump-sql {
                color: #52e3f6;
            }
        </style>
    </head>
    <body>
        <div class="sf-dump-container">
            <div class="sf-dump-two"></div>
            <pre class="sf-dump">
                <h4 class="sf-dump-public"><a>DETAILS</a></h4>
HTML;

    var_dump($value);

    echo <<<HTML
            </pre>
        </div>
    </body>
    </html>
HTML;

    die;
}

function dump($value, $die = true)
{
    echo "<pre style='background:#282828; color:#52e3f6; padding:16px;border-radius:6px;overflow:hidden;word-wrap:normal;font: 12px Menlo, Monaco, monospace;text-align: left;white-space: pre;direction: ltr;line-height: 1.2;z-index: 100000;margin:0;font-size:15px;margin-bottom:5px;'>";
    var_dump($value);
    echo "</pre>";

    if ($die) {
        die;
    }
}

function redirect($url, $status_code = 302, $headers = [], $query_params = [], $exit = true)
{
    // Ensure a valid HTTP status code is used
    if (!in_array($status_code, [301, 302, 303, 307, 308])) {
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
        exit();
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
    if (isset($defaultImageMap[$type])) {
        return URL_ROOT . ($defaultImageMap[$type]);
    }
}

function get_assets_directory($directory): string
{
    return Bolt::$bolt->assetManager->getAssetPath("assets" . $directory);
}

function get_package(string $package): string
{
    return get_assets_directory(DIRECTORY_SEPARATOR . "packages" . DIRECTORY_SEPARATOR . $package);
}

function get_bootstrap(string $path): string
{
    return get_assets_directory(DIRECTORY_SEPARATOR . "bootstrap" . DIRECTORY_SEPARATOR . $path);
}

function get_stylesheet(string $path): string
{
    return get_assets_directory(DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . $path);
}

function get_script($path): string
{
    return get_assets_directory(DIRECTORY_SEPARATOR . "js" . DIRECTORY_SEPARATOR . $path);
}

function get_date(?string $date = null, string $format = "jS M, Y", string $timezone = "UTC"): string
{
    $date = $date ?? '';

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

function csrf_token(string $name = 'csrf_token', int|float $expiration = 3600, int|float $tokenLength = 32)
{
    // Generate a CSRF token if one doesn't exist
    if (!isset($_SESSION[$name])) {
        $_SESSION[$name] = bin2hex(random_bytes($tokenLength));
        $_SESSION[$name . '_timestamp'] = time();
    }

    // Check if the token has expired
    if (time() - $_SESSION[$name . '_timestamp'] > $expiration) {
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_timestamp']);
        return false;
    }

    return $_SESSION[$name];
}

function check_csrf_token(string $name = 'csrf_token', int|float $expiration = 3600)
{
    if (!isset($_SESSION[$name]) || !isset($_SESSION[$name . '_timestamp'])) {
        throw new Exception("CSRF token is missing or expired.");
    }

    // Check if the token has expired
    if (time() - $_SESSION[$name . '_timestamp'] > $expiration) {
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_timestamp']);
        throw new Exception("CSRF token has expired.");
    }
}

function verify_csrf_token($token, string $name = 'csrf_token', int|float $expiration = 3600)
{
    check_csrf_token($name, $expiration);

    if ($token === $_SESSION[$name]) {
        // Remove the token to prevent reuse
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_timestamp']);
        return true;
    }

    throw new Exception("CSRF token verification failed.");
}

function validate_csrf_token($data, $toast = true)
{
    // Assuming that the Csrf class is defined and instantiated somewhere
    $csrf = new Csrf();

    // Get the referring URL or set a default redirect URL
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

    // Validate the CSRF token from the provided data
    if (!$csrf->validateToken($data["_csrf_token"])) {
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
 * @param string $level
 * @return void
 */
function console_logger(string $message, bool $die = false, bool $timestamp = true, string $level = 'info'): void
{
    $output = '';

    if ($timestamp) {
        $output .= "[" . date("Y-m-d H:i:s") . "] - ";
    }

    $output .= ucfirst($message) . PHP_EOL;

    switch ($level) {
        case 'info':
            $output = "\033[0;32m" . $output; // Green color for info
            break;
        case 'warning':
            $output = "\033[0;33m" . $output; // Yellow color for warning
            break;
        case 'error':
            $output = "\033[0;31m" . $output; // Red color for error
            break;
        default:
            break;
    }

    $output .= "\033[0m"; // Reset color

    echo $output;
    ob_flush();

    if ($die) {
        die();
    }
}

function load_required_files($directoryPath)
{
    $requiredFileExtensions = ['php', 'txt', 'html']; // Define the file extensions you consider as required

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
function view(string $path, array $data = [], string $layout): void
{
    $view = new BoltView('', ENABLE_BLADE, ENABLE_TWIG);

    $view->setLayout($layout);

    $view->render($path, $data);
}

function partials(string $path, $params = [])
{
    $view = new BoltView('', ENABLE_BLADE, ENABLE_TWIG);

    $view->partial($path, $params);
}

function loadData($object, $data, $options = [])
{
    $defaults = [
        'validate' => false,
        'type_cast' => false,
        'ignore_unknown' => false,
    ];

    $options = array_merge($defaults, $options);

    foreach ($data as $key => $value) {
        if (property_exists($object, $key)) {
            if ($options['validate']) {
                if (!validateData($key, $value)) {
                    // Handle validation errors (e.g., log, throw exceptions).
                    continue;
                }
            }

            if ($options['type_cast']) {
                $value = typeCastData($key, $value);
            }

            $object->{$key} = $value;
        } elseif (!$options['ignore_unknown']) {
            // Handle unknown properties (e.g., log, throw exceptions).
        }
    }
}

function validateData($key, $value)
{
    // Implement custom validation logic for each property.
    switch ($key) {
        case 'name':
            return is_string($value) && strlen($value) <= 255;
        case 'age':
            return is_int($value) && $value >= 18;
        case 'email':
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        case 'isSubscribed':
            return is_bool($value);
        case 'birthDate':
            return \DateTime::createFromFormat('Y-m-d', $value) !== false;
        default:
            return true;
    }
}

function typeCastData($key, $value)
{
    // Implement custom type casting logic for each property.
    switch ($key) {
        case 'age':
            return (int)$value;
        case 'name':
            return (string)$value;
        case 'username':
            return (string)$value;
        case 'surname':
            return (string)$value;
        case 'birthDate':
            return \DateTime::createFromFormat('Y-m-d', $value);
        case 'password':
            // Hash the password using a secure hashing algorithm like password_hash().
            return password_hash($value, PASSWORD_DEFAULT);
        default:
            return $value;
    }
}

/**
 * Hash Password with salt.
 *
 * @param string $password
 * @param integer $cost Adjust the cost factor as needed (higher is slower but more secure)
 * @return void
 */
function hashPassword(string $password, $cost = 12): string
{
    $salt = bin2hex(random_bytes(16)); // Generate a random salt
    $hash = password_hash($password . $salt, PASSWORD_BCRYPT, ['cost' => $cost]);

    if ($hash === false) {
        throw new BoltException('Password hash could not be created.');
    }

    return $hash;
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
    $filteredData = array();

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
    return Bolt::$bolt->session->get($key, $default);
}

function storeSessionData($key, $data)
{
    Bolt::$bolt->session->set($key, $data);
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
        throw new \InvalidArgumentException('Invalid toastr message type');
    }

    // Store the message, type, and attributes in the session
    $_SESSION['__flash_toastr'] = [
        'message' => $message,
        'type' => $type,
    ];

    $toastr = $_SESSION['__flash_toastr'] ?? null;
    return $toastr;
}
