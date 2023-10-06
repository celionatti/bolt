<?php

declare(strict_types=1);

/**
 * =========================================
 * Bolt - Request Validator ================
 * =========================================
 */

namespace Bolt\Bolt\Http;

class RequestValidator
{
    private $allowedIPs = [];
    private $blockedIPs = [];
    private $allowedUserAgents = [];
    private $blockedUserAgents = [];
    private $whitelistedURLs = [];
    private $blacklistedURLs = [];

    public function __construct($config = [])
    {
        if (isset($config['allowed_ips'])) {
            $this->allowedIPs = $config['allowed_ips'];
        }
        if (isset($config['blocked_ips'])) {
            $this->blockedIPs = $config['blocked_ips'];
        }
        if (isset($config['allowed_user_agents'])) {
            $this->allowedUserAgents = $config['allowed_user_agents'];
        }
        if (isset($config['blocked_user_agents'])) {
            $this->blockedUserAgents = $config['blocked_user_agents'];
        }
        if (isset($config['whitelisted_urls'])) {
            $this->whitelistedURLs = $config['whitelisted_urls'];
        }
        if (isset($config['blacklisted_urls'])) {
            $this->blacklistedURLs = $config['blacklisted_urls'];
        }
    }

    public function validateIP()
    {
        $userIP = $this->getClientIP();

        if ($this->isBlockedIP($userIP)) {
            return false;
        }

        if (!empty($this->allowedIPs)) {
            foreach ($this->allowedIPs as $allowedIP) {
                if ($this->ipInRange($userIP, $allowedIP)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function validateUserAgent()
    {
        $userAgent = $this->getUserAgent();

        if ($this->isBlockedUserAgent($userAgent)) {
            return false;
        }

        if (!empty($this->allowedUserAgents)) {
            return in_array($userAgent, $this->allowedUserAgents);
        }

        return false;
    }

    public function validateURL()
    {
        $currentURL = $this->getCurrentURL();

        if ($this->isBlacklistedURL($currentURL)) {
            return false;
        }

        if (!empty($this->whitelistedURLs)) {
            return in_array($currentURL, $this->whitelistedURLs);
        }

        return false;
    }

    public function validateRequest()
    {
        return $this->validateIP() && $this->validateUserAgent() && $this->validateURL();
    }

    private function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    private function isBlockedIP($ip)
    {
        return in_array($ip, $this->blockedIPs);
    }

    private function isBlockedUserAgent($userAgent)
    {
        return in_array($userAgent, $this->blockedUserAgents);
    }

    private function getCurrentURL()
    {
        return $_SERVER['REQUEST_URI'];
    }

    private function isBlacklistedURL($url)
    {
        return in_array($url, $this->blacklistedURLs);
    }

    private function ipInRange($ip, $range)
    {
        if (strpos($range, '/') !== false) {
            // CIDR notation (e.g., 192.168.1.0/24)
            list($subnet, $mask) = explode('/', $range);
            $subnet = ip2long($subnet);
            $ip = ip2long($ip);
            $mask = -1 << (32 - $mask);
            $subnet &= $mask; // Calculate the base address of the subnet
            return ($ip & $mask) == $subnet;
        } elseif (strpos($range, '-') !== false) {
            // IP range (e.g., 192.168.1.1-192.168.1.255)
            list($startIP, $endIP) = explode('-', $range);
            $startIP = ip2long($startIP);
            $endIP = ip2long($endIP);
            $ip = ip2long($ip);
            return ($ip >= $startIP) && ($ip <= $endIP);
        } else {
            // Single IP address (e.g., 192.168.1.100)
            return ip2long($ip) === ip2long($range);
        }
    }

    private function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }
}