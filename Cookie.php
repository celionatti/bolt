<?php


declare(strict_types=1);

/**
 * ================================
 * Bolt - Cookie Class ============
 * ================================
 */

 namespace celionatti\Bolt;
 

 use DateTime;


 class Cookie
 {
    private const ENCRYPTION_METHOD = 'AES-256-CBC';
    
     /**
      * Set a cookie
      *
      * @param string $name    The name of the cookie
      * @param string $value   The value to store in the cookie
      * @param int    $expires The expiration time as a Unix timestamp or a DateTime object
      * @param string $path    The path on the server where the cookie will be available
      * @param string $domain  The (sub)domain that the cookie is available to
      * @param bool   $secure  Indicates whether the cookie should only be transmitted over a secure HTTPS connection
      * @param bool   $httpOnly When TRUE, the cookie will be made accessible only through the HTTP protocol
      */
     public static function set(
        string $name,
        string $value,
        $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ) {
        if (!defined('COOKIE_SECRET')) {
            throw new \RuntimeException('COOKIE_SECRET is not defined.');
        }

        // Convert expires to timestamp if it's a DateTime object
        if ($expires instanceof DateTime) {
            $expires = $expires->getTimestamp();
        }

        // Generate a random IV (Initialization Vector)
        $iv = random_bytes(16);

        // Encrypt the value
        $encryptedValue = openssl_encrypt($value, self::ENCRYPTION_METHOD, COOKIE_SECRET, 0, $iv);

        // Combine IV and encrypted value
        $combinedValue = base64_encode($iv . $encryptedValue);

        // Store the combined value in the cookie
        $secureFlag = $secure ? '1' : '0'; // '1' for true, '0' for false
        $sameSiteFlag = $sameSite ?: 'Lax'; // Default to Lax for better security
    
        $options = [
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secureFlag,
            'httponly' => $httpOnly,
            'samesite' => $sameSiteFlag,
        ];

        setcookie(
            $name,
            $combinedValue,
            $options
        );
    }
 
     /**
      * Get the value of a cookie
      *
      * @param string $name The name of the cookie
      * @param mixed $default The value to return if the cookie is not set
      *
      * @return mixed The value of the cookie or the default value if not set
      */
     public static function get(string $name)
    {
        if (isset($_COOKIE[$name])) {
            // Decode the combined value
            $combinedValue = base64_decode($_COOKIE[$name]);

            // Extract IV and encrypted value
            $iv = substr($combinedValue, 0, 16);
            $encryptedValue = substr($combinedValue, 16);

            // Decrypt the value
            $decryptedValue = openssl_decrypt($encryptedValue, self::ENCRYPTION_METHOD, COOKIE_SECRET, 0, $iv);

            return $decryptedValue !== false ? $decryptedValue : null;
        }

        return null;
    }
 
     /**
      * Check if a cookie exists
      *
      * @param string $name The name of the cookie
      *
      * @return bool True if the cookie exists, false otherwise
      */
     public static function has(string $name)
     {
         return isset($_COOKIE[$name]);
     }
 
     /**
      * Delete a cookie
      *
      * @param string $name The name of the cookie to delete
      * @param string $path The path on the server where the cookie was available
      * @param string $domain The (sub)domain that the cookie was available to
      * @param bool $secure Indicates whether the cookie was only transmitted over a secure HTTPS connection
      * @param bool $httpOnly When TRUE, the cookie was made accessible only through the HTTP protocol
      */
     public static function delete(
         string $name,
         string $path = '/',
         string $domain = '',
         bool $secure = false,
         bool $httpOnly = true
     ) {
         if (self::has($name)) {
             setcookie(
                 $name,
                 '',
                 time() - 3600,
                 $path,
                 $domain,
                 $secure,
                 $httpOnly
             );
         }
     }
 
     /**
      * Get all cookies as an associative array
      *
      * @return array Associative array containing all cookies
      */
     public static function getAll()
     {
         return $_COOKIE;
     }
 
     /**
      * Clear all cookies
      *
      * @param string $path The path on the server where cookies are available
      * @param string $domain The (sub)domain where cookies are available
      * @param bool $secure Indicates whether cookies should be cleared only over a secure HTTPS connection
      * @param bool $httpOnly When TRUE, clears only cookies accessible through the HTTP protocol
      */
     public static function clearAll(
         string $path = '/',
         string $domain = '',
         bool $secure = false,
         bool $httpOnly = true
     ) {
         foreach ($_COOKIE as $name => $value) {
             self::delete($name, $path, $domain, $secure, $httpOnly);
         }
     }
 }
 