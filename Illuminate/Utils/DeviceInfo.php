<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - DeviceInfo ==============
 * ================================
 */

namespace celionatti\Bolt\Illuminate\Utils;

class DeviceInfo
{
	/**
     * Retrieve user agent string
     */
    private static function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Retrieve client IP address with enhanced detection
     */
    public static function getIP(): string
    {
        $ipHeaders = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipHeaders as $header) {
            $ip = getenv($header);

            if ($ip) {
                // Validate IP address
                $ip = trim($ip);

                // Handle potential comma-separated IP lists (for X-Forwarded-For)
                $ips = explode(',', $ip);
                $validIP = filter_var(trim($ips[0]), FILTER_VALIDATE_IP);

                if ($validIP) {
                    return $validIP;
                }
            }
        }

        return 'UNKNOWN';
    }

    /**
     * Comprehensive OS detection
     */
    public static function getOS(): array
    {
        $userAgent = self::getUserAgent();

        $osPatterns = [
            'Windows' => [
                '/windows nt 10/i' => 'Windows 10',
                '/windows nt 6.3/i' => 'Windows 8.1',
                '/windows nt 6.2/i' => 'Windows 8',
                '/windows nt 6.1/i' => 'Windows 7',
                '/windows nt 6.0/i' => 'Windows Vista',
                '/windows nt 5.2/i' => 'Windows Server 2003',
                '/windows nt 5.1/i' => 'Windows XP',
            ],
            'Mac' => [
                '/macintosh|mac os x/i' => 'macOS',
            ],
            'Linux' => [
                '/linux/i' => 'Linux',
                '/ubuntu/i' => 'Ubuntu',
                '/fedora/i' => 'Fedora',
            ],
            'Mobile' => [
                '/android/i' => 'Android',
                '/iphone/i' => 'iOS',
                '/ipad/i' => 'iPadOS',
            ]
        ];

        foreach ($osPatterns as $osType => $patterns) {
            foreach ($patterns as $regex => $name) {
                if (preg_match($regex, $userAgent)) {
                    return [
                        'type' => $osType,
                        'name' => $name,
                        'userAgent' => $userAgent
                    ];
                }
            }
        }

        return [
            'type' => 'Unknown',
            'name' => 'Unknown OS',
            'userAgent' => $userAgent
        ];
    }

    /**
     * Advanced browser detection
     */
    public static function getBrowser(): array
    {
        $userAgent = self::getUserAgent();

        $browserPatterns = [
            'Chrome' => '/chrome/i',
            'Firefox' => '/firefox/i',
            'Safari' => '/safari/i',
            'Opera' => '/opera/i',
            'Edge' => '/edge/i',
            'Internet Explorer' => '/msie|trident/i',
        ];

        foreach ($browserPatterns as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                // Extract version
                preg_match('/(?:Chrome|Firefox|Version|OPR|Edge)\/([0-9.]+)/i', $userAgent, $matches);
                $version = $matches[1] ?? 'Unknown';

                return [
                    'name' => $name,
                    'version' => $version,
                    'userAgent' => $userAgent
                ];
            }
        }

        return [
            'name' => 'Unknown',
            'version' => 'Unknown',
            'userAgent' => $userAgent
        ];
    }

    /**
     * Advanced device type detection
     */
    public static function getDevice(): array
    {
        $userAgent = strtolower(self::getUserAgent());

        $devicePatterns = [
            'Mobile' => [
                'phones' => ['android', 'webos', 'iphone', 'ipad', 'ipod', 'blackberry', 'windows phone'],
                'mini_browsers' => ['up.browser', 'up.link', 'mmp', 'symbian', 'smartphone', 'midp', 'wap']
            ],
            'Tablet' => ['tablet', 'ipad', 'playbook', 'kindle', 'silk'],
            'Desktop' => ['windows', 'macintosh', 'linux']
        ];

        // Check for tablet first
        foreach ($devicePatterns['Tablet'] as $tabletPattern) {
            if (strpos($userAgent, $tabletPattern) !== false) {
                return [
                    'type' => 'Tablet',
                    'details' => $tabletPattern,
                    'userAgent' => self::getUserAgent()
                ];
            }
        }

        // Check for mobile
        foreach ($devicePatterns['Mobile']['phones'] as $mobilePattern) {
            if (strpos($userAgent, $mobilePattern) !== false) {
                return [
                    'type' => 'Mobile',
                    'details' => $mobilePattern,
                    'userAgent' => self::getUserAgent()
                ];
            }
        }

        // Check for desktop
        foreach ($devicePatterns['Desktop'] as $desktopPattern) {
            if (strpos($userAgent, $desktopPattern) !== false) {
                return [
                    'type' => 'Desktop',
                    'details' => $desktopPattern,
                    'userAgent' => self::getUserAgent()
                ];
            }
        }

        return [
            'type' => 'Unknown',
            'details' => 'Unable to determine device type',
            'userAgent' => self::getUserAgent()
        ];
    }

    /**
     * Get comprehensive device information
     */
    public static function getDeviceInfo(): array
    {
        return [
            'ip' => self::getIP(),
            'os' => self::getOS(),
            'browser' => self::getBrowser(),
            'device' => self::getDevice()
        ];
    }
}