<?php

declare(strict_types=1);

/**
 * =========================================
 * Bolt - FeePercentage ====================
 * =========================================
 */

namespace celionatti\Bolt\Helpers\Funds;



class FeePercentage
{
    private $percentageFee;
    private $flatFee;
    private $capAmount;
    private $internationalFee;
    private $flatInternationalFee;
    private $internationalCap;

    public function __construct(array $config = [])
    {
        // Default settings
        $this->percentageFee = $config['percentageFee'] ?? 1.5; // Local percentage fee
        $this->flatFee = $config['flatFee'] ?? 100; // Local flat fee
        $this->capAmount = $config['capAmount'] ?? 2000; // Local maximum fee cap
        $this->internationalFee = $config['internationalFee'] ?? 3.9; // International percentage fee
        $this->flatInternationalFee = $config['flatInternationalFee'] ?? 100; // International flat fee
        $this->internationalCap = $config['internationalCap'] ?? null; // Optional cap for international
    }

    /**
     * Calculate Paystack charges.
     *
     * @param float $amount Transaction amount in smallest unit (e.g., kobo for NGN)
     * @param bool $isInternational Is it an international transaction?
     * @param string $currency Transaction currency (e.g., NGN, USD)
     * @return array Fee breakdown and final total amount
     */
    public function calculateCharges(float $amount, bool $isInternational = false, string $currency = 'NGN'): array
    {
        $percentage = $isInternational ? $this->internationalFee / 100 : $this->percentageFee / 100;
        $flatFee = $isInternational ? $this->flatInternationalFee : $this->flatFee;
        $cap = $isInternational ? ($this->internationalCap ?? PHP_INT_MAX) : $this->capAmount;

        // Calculate the fee
        $charge = ($amount * $percentage) + $flatFee;

        // Apply cap if applicable
        if ($charge > $cap) {
            $charge = $cap;
        }

        // Calculate final total amount
        $finalAmount = $amount + $charge;

        return [
            'currency' => $currency,
            'amount' => round($amount, 2),
            'percentageFee' => round($amount * $percentage, 2),
            'flatFee' => round($flatFee, 2),
            'charge' => round($charge, 2),
            'total' => round($finalAmount, 2),
        ];
    }

    /**
     * Reverse calculate the original amount from the total including fees.
     *
     * @param float $total Total amount including fees
     * @param bool $isInternational Is it an international transaction?
     * @return float Original amount before fees
     */
    public function reverseCalculateBaseAmount(float $total, bool $isInternational = false): float
    {
        $percentage = $isInternational ? $this->internationalFee / 100 : $this->percentageFee / 100;
        $flatFee = $isInternational ? $this->flatInternationalFee : $this->flatFee;

        // Reverse calculation
        return round(($total - $flatFee) / (1 + $percentage), 2);
    }
}
