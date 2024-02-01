<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - DeviceInfo ==============
 * ================================
 */

namespace celionatti\Bolt\Helpers\Utils;

class DeviceInfo
{
	private static function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public static function getIP(): false|array|string
    {
        $ipHeaders = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipHeaders as $header) {
            if ($ip = getenv($header)) {
                return $ip;
            }
        }

        return 'UNKNOWN';
    }

    public static function getOS(): string
    {
        $userAgent = self::getUserAgent();
        $osPlatform = 'Unknown OS Platform';

        $osArray = [
            '/windows nt 10/i' => 'Windows 10',
            // Add other OS patterns here
        ];

        foreach ($osArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $osPlatform = $value;
                break;
            }
        }

        return $osPlatform;
    }

    public static function getBrowser(): string
    {
        $userAgent = self::getUserAgent();
        $browser = 'Unknown Browser';

        $browserArray = [
            '/msie/i' => 'Internet Explorer',
            // Add other browser patterns here
        ];

        foreach ($browserArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $browser = $value;
                break;
            }
        }

        return $browser;
    }

    public static function getDevice(): string
    {
        $userAgent = strtolower(self::getUserAgent());
        $tabletBrowser = 0;
        $mobileBrowser = 0;

        $tabletPatterns = ['tablet', 'ipad', 'playbook'];
        $mobilePatterns = ['up.browser', 'up.link', 'mmp', 'symbian', 'smartphone', 'midp', 'wap', 'phone', 'android', 'iemobile'];

        if (preg_match('/' . implode('|', $tabletPatterns) . '/i', $userAgent)) {
            $tabletBrowser++;
        }

        if (preg_match('/' . implode('|', $mobilePatterns) . '/i', $userAgent)) {
            $mobileBrowser++;
        }

        $mobileUA = substr(self::getUserAgent(), 0, 4);
        $mobileAgents = ['w3c ', 'acs-', 'alav', /* ... Add more mobile agents if needed ... */ 'xda-'];

        if (in_array($mobileUA, $mobileAgents)) {
            $mobileBrowser++;
        }

        if (strpos(strtolower(self::getUserAgent()), 'opera mini') > 0) {
            $mobileBrowser++;

            // Check for tablets on Opera Mini alternative headers
            $stockUA = strtolower($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] ?? ($_SERVER['HTTP_DEVICE_STOCK_UA'] ?? ''));
            
            if (preg_match('/' . implode('|', $tabletPatterns) . '/i', $stockUA)) {
                $tabletBrowser++;
            }
        }

        if ($tabletBrowser > 0) {
            return 'Tablet';
        } elseif ($mobileBrowser > 0) {
            return 'Mobile';
        } else {
            return 'Computer';
        }
    }
}