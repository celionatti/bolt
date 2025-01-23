<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - NumberGenerator =========
 * ================================
 */

namespace celionatti\Bolt\Illuminate\Utils;

class NumberGenerator
{
    private array $generatedNumbers = [];
    private string $salt;

    public function __construct(?string $salt = null)
    {
        $this->salt = $salt ?? bin2hex(random_bytes(16));
    }

    /**
     * Generate a cryptographically secure unique ticket number
     *
     * @param string $prefix Ticket prefix
     * @param int $length Total length
     * @return string Unique ticket number
     * @throws \Exception
     */
    public function generateSecureTicketNumber(string $prefix = 'TKT', int $length = 12): string
    {
        if ($length <= strlen($prefix)) {
            throw new \InvalidArgumentException('The length must be greater than the prefix length.');
        }

        do {
            $timestamp = time();
            $random = bin2hex(random_bytes(4));
            $hash = hash('sha256', $timestamp . $this->salt . $random);
            $uniqueCode = substr($hash, 0, $length - strlen($prefix));
            $ticketNumber = $prefix . strtoupper($uniqueCode);
        } while (isset($this->generatedNumbers[$ticketNumber]));

        $this->generatedNumbers[$ticketNumber] = true;

        return $ticketNumber;
    }

    /**
     * Generate a standardized company registration number
     *
     * @param string $stringCode Code identifier
     * @param int|null $year Year of registration
     * @return string Company registration number
     * @throws \Exception
     */
    public function generateCompanyRegNumber(string $stringCode = 'COBO', ?int $year = null): string
    {
        $year = $year ?? (int)date('Y');
        $randomDigits = str_pad((string)random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        $checkDigit = $this->calculateCheckDigit($stringCode . $year . $randomDigits);

        return sprintf(
            '%s-%04d-%05d-%1d',
            strtoupper($stringCode),
            $year,
            $randomDigits,
            $checkDigit
        );
    }

    /**
     * Generate a complex invoice number with industry-standard formatting
     *
     * @param string $businessCode Business identifier
     * @param string $invoiceType Type of invoice
     * @return string Formatted invoice number
     * @throws \Exception
     */
    public function generateInvoiceNumber(string $businessCode = 'ACME', string $invoiceType = 'STD'): string
    {
        $timestamp = date('Ymd');
        $randomComponent = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $checksum = $this->calculateLuhnChecksum($timestamp . $randomComponent);

        return sprintf(
            '%s-%s-%s-%s',
            strtoupper($businessCode),
            $invoiceType,
            $timestamp,
            $randomComponent . $checksum
        );
    }

    /**
     * Generate a serial number with multiple validation components
     *
     * @param string $productLine Product line code
     * @param string $manufacturingLocation Location code
     * @return string Unique serial number
     * @throws \Exception
     */
    public function generateSerialNumber(string $productLine = 'PROD', string $manufacturingLocation = 'US'): string
    {
        $year = date('y');
        $julianDate = (int)date('z');
        $randomComponent = bin2hex(random_bytes(3));
        $checkDigit = $this->calculateCheckDigit($productLine . $year . $julianDate . $randomComponent);

        return sprintf(
            '%s-%s-%03d-%s-%1d',
            strtoupper($productLine),
            strtoupper($manufacturingLocation),
            $julianDate,
            strtoupper($randomComponent),
            $checkDigit
        );
    }

    /**
     * Calculate Luhn algorithm check digit
     *
     * @param string $number Input number
     * @return int Check digit
     */
    private function calculateLuhnChecksum(string $number): int
    {
        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int)$number[$i];

            if (($i % 2) === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Calculate check digit using weighted algorithm
     *
     * @param string $input Input string
     * @return int Check digit
     */
    private function calculateCheckDigit(string $input): int
    {
        $weights = [7, 3, 1];
        $sum = 0;

        foreach (str_split($input) as $index => $char) {
            $value = is_numeric($char) ? (int)$char : ord(strtoupper($char)) - 55;
            $sum += $value * $weights[$index % 3];
        }

        return $sum % 10;
    }
}
