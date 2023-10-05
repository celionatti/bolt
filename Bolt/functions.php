<?php

declare(strict_types=1);

require __DIR__ . "/global-variables.php";


function bolt_env($data)
{
    if (isset($_ENV[$data])) {
        return $_ENV[$data];
    }

    return false;
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

function bolt_die($value, $message = '', $title = '<span style="color:teal;">B</span>OLT Error', $status_code = 500)
{
    http_response_code($status_code);

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>{$title}</title>
        <style>
            body {font-family: Arial, sans-serif;}
            .error-container {background-color: #F8F8F8; border: 1px solid #E0E0E0; margin: 20px; padding: 20px; text-align:center;}
            .error-title {font-size: 24px; color: #FF0000; font-weight: bold; margin-bottom: 10px;}
            .error-message {font-size: 18px; color: #333; margin-bottom: 20px;}
            .error-details {font-size: 16px; color: #777;}
        </style>
    </head>
    <body>
        <div class='error-container'>
            <div class='error-title'>{$title}</div>
            <div class='error-message'>{$message}</div>
            <div class='error-details'><pre>" . print_r($value, true) . "</pre></div>
        </div>
    </body>
    </html>";

    die;
}

function dd($value): void
{
    echo "<pre>";
    echo "<div style='background-color:#000; color:lightgreen; margin: 5px; padding:5px;border:3px solid;'>";
    echo "<h2 style='border:3px solid; border-color:teal; padding:5px; text-align:center;font-weight:bold;font-weight: bold;
    text-transform: uppercase;'>";
    echo "Error Type: Dump and die";
    echo "</h2>";
    var_dump($value);
    echo "</div>";
    echo "</pre>";

    die;
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
        // Add more sources as needed (e.g., 'session', 'cookie', etc.)
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
    $inputValue = $sources[$type][$key] ?? '';

    // Determine if the selected value matches the input value
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
    $inputValue = $sources[$type][$key] ?? '';

    // Determine if the checked value matches the input value
    $isChecked = ($strict ? $inputValue === $value : $inputValue == $value) || ($default == $value);

    return $isChecked ? 'checked' : '';
}


function get_image(?string $path = null, string $type = 'post'): string
{
    $defaultImageMap = [
        'post' => '/assets/images/no_image.jpg',
        'male' => '/assets/images/user_male.jpg',
        'female' => '/assets/images/user_female.jpg',
    ];

    $path = $path ?? '';

    if (!empty($path) && file_exists($path)) {
        return BOLT_ROOT . '/' . $path;
    }

    if (array_key_exists($type, $defaultImageMap)) {
        return BOLT_ROOT . $defaultImageMap[$type];
    }

    return BOLT_ROOT . $defaultImageMap['post'];
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

function csrf_token($name = 'csrf_token', $expiration = 3600, $tokenLength = 32)
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

function require_csrf_token($name = 'csrf_token', $expiration = 3600)
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

function verify_csrf_token($token, $name = 'csrf_token', $expiration = 3600)
{
    require_csrf_token($name, $expiration);

    if ($token === $_SESSION[$name]) {
        // Remove the token to prevent reuse
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_timestamp']);
        return true;
    }

    throw new Exception("CSRF token verification failed.");
}
