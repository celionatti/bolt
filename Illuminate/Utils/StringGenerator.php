<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - StringGenerator =========
 * ================================
 */

namespace celionatti\Bolt\Illuminate\Utils;

class StringGenerator
{
    private $prefix;
    private $length;
    private $usedCodes;

    /**
     * Initialize the ticket code generator
     *
     * @param string $prefix Optional prefix for ticket codes
     * @param int $length Length of the random part of the code (excluding prefix)
     */
    public function __construct(string $prefix = '', int $length = 8) {
        $this->prefix = $prefix;
        $this->length = $length;
        $this->usedCodes = [];
    }

    /**
     * Generate a unique ticket code
     *
     * @return string The generated ticket code
     * @throws Exception if unable to generate unique code after max attempts
     */
    public function generateCode(): string {
        $maxAttempts = 10;
        $attempts = 0;

        do {
            $code = $this->generateRandomCode();
            $attempts++;

            if ($attempts >= $maxAttempts) {
                throw new Exception('Unable to generate unique ticket code after ' . $maxAttempts . ' attempts');
            }
        } while ($this->isCodeUsed($code));

        $this->usedCodes[] = $code;
        return $code;
    }

    /**
     * Generate a random code
     *
     * @return string The generated code including prefix
     */
    private function generateRandomCode(): string {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomPart = '';

        for ($i = 0; $i < $this->length; $i++) {
            $randomPart .= $characters[random_int(0, strlen($characters) - 1)];
        }

        // Insert hyphens every 4 characters for readability
        $formattedCode = implode('-', str_split($randomPart, 4));

        return $this->prefix ? $this->prefix . '-' . $formattedCode : $formattedCode;
    }

    /**
     * Check if a code has already been used
     *
     * @param string $code The code to check
     * @return bool True if code is already used
     */
    private function isCodeUsed(string $code): bool {
        return in_array($code, $this->usedCodes);
    }

    /**
     * Set a new prefix
     *
     * @param string $prefix The new prefix to use
     */
    public function setPrefix(string $prefix): void {
        $this->prefix = $prefix;
    }

    /**
     * Get array of all generated codes
     *
     * @return array Array of generated codes
     */
    public function getUsedCodes(): array {
        return $this->usedCodes;
    }
}