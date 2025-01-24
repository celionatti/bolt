<?php

declare(strict_types=1);

/**
 * =========================================
 * Bolt - PaystackPercentage ===============
 * =========================================
 */

namespace celionatti\Bolt\Helpers\Funds;



class PaystackPercentage
{
    private const LOCAL_PERCENTAGE_FEES = [
        ['min' => 0, 'max' => 2500, 'rate' => 1.5],
        ['min' => 2500, 'max' => 50000, 'rate' => 1.5],
        ['min' => 50000, 'max' => 300000, 'rate' => 1.4],
        ['min' => 300000, 'max' => PHP_FLOAT_MAX, 'rate' => 1.2]
    ];

    private const INTERNATIONAL_PERCENTAGE_FEES = [
        ['min' => 0, 'max' => PHP_FLOAT_MAX, 'rate' => 3.9],
    ];

    private const FLAT_FEE = 100; // NGN fixed fee per transaction

    public function calculateCharges(float $amount, bool $isInternational = false): array
    {
        // Check if amount is zero
        if ($amount == 0) {
            return [
                'amount' => 0,
                'percentageFee' => 0,
                'flatFee' => 0,
                'percentageRate' => 0,
                'totalFee' => 0,
                'finalAmount' => 0
            ];
        }

        $fees = $isInternational ? self::INTERNATIONAL_PERCENTAGE_FEES : self::LOCAL_PERCENTAGE_FEES;

        $percentageRate = $this->findApplicableRate($amount, $fees);

        $percentageFee = $amount * ($percentageRate / 100);
        $flatFee = self::FLAT_FEE;

        $totalFee = $percentageFee + $flatFee;
        $finalAmount = $amount + $totalFee;

        return [
            'amount' => round($amount, 2),
            'percentageFee' => round($percentageFee, 2),
            'flatFee' => round($flatFee, 2),
            'percentageRate' => $percentageRate,
            'totalFee' => round($totalFee, 2),
            'finalAmount' => round($finalAmount, 2)
        ];
    }

    private function findApplicableRate(float $amount, array $feeStructure): float
    {
        foreach ($feeStructure as $tier) {
            if ($amount >= $tier['min'] && $amount < $tier['max']) {
                return $tier['rate'];
            }
        }

        return end($feeStructure)['rate'];
    }

    public function reverseCalculateBaseAmount(float $total, bool $isInternational = false): float
    {
        // Check if total is zero
        if ($total == 0) {
            return 0;
        }

        $fees = $isInternational ? self::INTERNATIONAL_PERCENTAGE_FEES : self::LOCAL_PERCENTAGE_FEES;
        $percentageRate = $this->findApplicableRate($total, $fees);
        $percentage = $percentageRate / 100;

        return round(($total - self::FLAT_FEE) / (1 + $percentage), 2);
    }
}
