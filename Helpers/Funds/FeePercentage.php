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
                'tiers' => [
                    ['min' => 0,     'max' => 2000,  'fee' => 100],
                    ['min' => 2000,  'max' => 5000,  'fee' => 100],
                    ['min' => 5000,  'max' => 10000, 'fee' => 150],
                    ['min' => 10000, 'max' => 20000, 'fee' => 200],
                    ['min' => 20000, 'max' => 30000, 'fee' => 250],
                    ['min' => 30000, 'max' => null,  'fee' => 300],
                ]
            ],
            'international' => [
                'percentageFee' => 3.9,
                'tiers' => [
                    ['min' => 0,     'max' => 2000,  'fee' => 100],
                    ['min' => 2000,  'max' => 5000,  'fee' => 150],
                    ['min' => 5000,  'max' => 10000, 'fee' => 200],
                    ['min' => 10000, 'max' => null,  'fee' => 300],
                ]
            ]
        ], $config);

        // Sort tiers by min amount for both local and international
        foreach (['local', 'international'] as $type) {
            usort($this->config[$type]['tiers'], function ($a, $b) {
                return $a['min'] <=> $b['min'];
            });
        }
    }

    /**
     * Calculate the total amount the customer should pay including tiered fees
     */
    public function calculateTotalAmount(float $netAmount, bool $isInternational = false): array
    {
        $type = $isInternational ? 'international' : 'local';
        $settings = $this->config[$type];
        $percentage = $settings['percentageFee'] / 100;

        $applicableTier = $this->findApplicableTier($netAmount, $percentage, $settings['tiers']);

        return $this->formatResult(
            $netAmount,
            $applicableTier['totalAmount'],
            $applicableTier['feeDetails'],
            $settings['percentageFee'],
            $applicableTier['tier']
        );
    }

    private function findApplicableTier(float $netAmount, float $percentage, array $tiers): array
    {
        foreach ($tiers as $tier) {
            // Calculate tentative total with this tier's fee
            $tentativeTotal = ($netAmount + $tier['fee']) / (1 - $percentage);

            // Check if within tier range
            $min = $tier['min'];
            $max = $tier['max'] ?? PHP_FLOAT_MAX;

            if ($tentativeTotal >= $min && $tentativeTotal <= $max) {
                $totalFee = $tentativeTotal * $percentage + $tier['fee'];
                return [
                    'totalAmount' => $tentativeTotal,
                    'feeDetails' => [
                        'percentageAmount' => $tentativeTotal * $percentage,
                        'flatFee' => $tier['fee'],
                        'totalFee' => $totalFee
                    ],
                    'tier' => $tier
                ];
            }
        }

        // Fallback to last tier if none found (shouldn't happen with proper configuration)
        $lastTier = end($tiers);
        $tentativeTotal = ($netAmount + $lastTier['fee']) / (1 - $percentage);
        $totalFee = $tentativeTotal * $percentage + $lastTier['fee'];

        return [
            'totalAmount' => $tentativeTotal,
            'feeDetails' => [
                'percentageAmount' => $tentativeTotal * $percentage,
                'flatFee' => $lastTier['fee'],
                'totalFee' => $totalFee
            ],
            'tier' => $lastTier
        ];
    }

    /**
     * Calculate net amount after deducting tiered fees
     */
    public function calculateNetAmount(float $totalAmount, bool $isInternational = false): float
    {
        $type = $isInternational ? 'international' : 'local';
        $settings = $this->config[$type];
        $percentage = $settings['percentageFee'] / 100;

        $applicableFee = $this->getFeeForTotalAmount($totalAmount, $settings['tiers']);

        $totalFee = $totalAmount * $percentage + $applicableFee;
        return round($totalAmount - $totalFee, 2);
    }

    private function getFeeForTotalAmount(float $totalAmount, array $tiers): float
    {
        foreach ($tiers as $tier) {
            $min = $tier['min'];
            $max = $tier['max'] ?? PHP_FLOAT_MAX;

            if ($totalAmount >= $min && $totalAmount <= $max) {
                return $tier['fee'];
            }
        }

        return end($tiers)['fee']; // Fallback to last tier
    }

    private function formatResult(
        float $netAmount,
        float $totalAmount,
        array $feeDetails,
        float $percentageRate,
        array $tier
    ): array {
        return [
            'initialNetAmount' => round($netAmount, 2),
            'totalAmount' => round($totalAmount, 2),
            'feeBreakdown' => [
                'percentageRate' => $percentageRate,
                'percentageAmount' => round($feeDetails['percentageAmount'], 2),
                'flatFee' => round($feeDetails['flatFee'], 2),
                'totalFees' => round($feeDetails['totalFee'], 2),
            ],
            'appliedTier' => [
                'min' => $tier['min'],
                'max' => $tier['max'] ?? 'No limit',
                'fee' => $tier['fee']
            ],
            'merchantReceives' => round($netAmount, 2),
            'customerPays' => round($totalAmount, 2)
        ];
    }

    /**
     * Calculate with profit margin including tiered fees
     */
    public function calculateWithProfitMargin(float $ticketPrice, float $profitMargin, bool $isInternational = false): array
    {
        $netAmount = $ticketPrice * (1 + $profitMargin);
        $result = $this->calculateTotalAmount($netAmount, $isInternational);
        $result['amount'] = round($ticketPrice, 2);
        $result['profitMargin'] = $profitMargin;
        $result['profit'] = round($ticketPrice * $profitMargin, 2);
        return $result;
    }

    /**
     * Calculate with flat profit including tiered fees
     */
    public function calculateWithFlatProfit(float $ticketPrice, float $flatProfit, bool $isInternational = false): array
    {
        $netAmount = $ticketPrice + $flatProfit;
        $result = $this->calculateTotalAmount($netAmount, $isInternational);
        $result['amount'] = round($ticketPrice, 2);
        $result['profit'] = round($flatProfit, 2);
        return $result;
    }
}
