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
         bool $httpOnly = true
     ) {
         // Convert expires to timestamp if it's a DateTime object
         if ($expires instanceof DateTime) {
             $expires = $expires->getTimestamp();
         }
 
         setcookie(
             $name,
             $value,
             $expires,
             $path,
             $domain,
             $secure,
             $httpOnly
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
     public static function get(string $name, $default = null)
     {
         return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
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
 