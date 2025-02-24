<?php

declare(strict_types=1);

namespace celionatti\Bolt\Security;

class SecurityHeaders
{
    public static function getDefaults(): array
    {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'",
            'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains; preload',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        ];
    }
}