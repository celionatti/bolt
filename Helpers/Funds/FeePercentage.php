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
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'local' => [
                'percentageFee' => 1.5,
                'flatFee' => 100,
                'capAmount' => 2000
            ],
            'international' => [
                'percentageFee' => 3.9,
                'flatFee' => 100,
                'capAmount' => null
            ]
        ], $config);
    }

    public function calculateCharges(float $amount, bool $isInternational = false): array
    {
        $type = $isInternational ? 'international' : 'local';
        $settings = $this->config[$type];

        $percentageFee = $amount * ($settings['percentageFee'] / 100);
        $flatFee = $settings['flatFee'];
        $capAmount = $settings['capAmount'] ?? PHP_INT_MAX;

        $totalFee = min($percentageFee + $flatFee, $capAmount);
        $finalAmount = $amount + $totalFee;

        return [
            'amount' => round($amount, 2),
            'percentageFee' => round($percentageFee, 2),
            'flatFee' => round($flatFee, 2),
            'totalFee' => round($totalFee, 2),
            'finalAmount' => round($finalAmount, 2)
        ];
    }

    public function reverseCalculateBaseAmount(float $total, bool $isInternational = false): float
    {
        $type = $isInternational ? 'international' : 'local';
        $settings = $this->config[$type];

        $percentage = $settings['percentageFee'] / 100;
        $flatFee = $settings['flatFee'];

        return round(($total - $flatFee) / (1 + $percentage), 2);
    }
}
