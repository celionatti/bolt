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
    private array $localFees;
    private array $internationalFees;
    private float $flatFee;

    public function __construct(
        ?array $localFees = null,
        ?array $internationalFees = null,
        ?float $flatFee = null
    ) {
        $this->localFees = $localFees ?? [
            ['min' => 0, 'max' => 2500, 'rate' => 1.5],
            ['min' => 2500, 'max' => 50000, 'rate' => 1.5],
            ['min' => 50000, 'max' => 300000, 'rate' => 1.4],
            ['min' => 300000, 'max' => PHP_FLOAT_MAX, 'rate' => 1.2]
        ];

        $this->internationalFees = $internationalFees ?? [
            ['min' => 0, 'max' => PHP_FLOAT_MAX, 'rate' => 3.9]
        ];

        $this->flatFee = $flatFee ?? 100;
    }

    public function calculateCharges(float $amount, bool $isInternational = false): array
    {
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

        $fees = $isInternational ? $this->internationalFees : $this->localFees;

        $percentageRate = $this->findApplicableRate($amount, $fees);

        $percentageFee = $amount * ($percentageRate / 100);
        $flatFee = $this->flatFee;

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
        if ($total == 0) {
            return 0;
        }

        $fees = $isInternational ? $this->internationalFees : $this->localFees;
        $percentageRate = $this->findApplicableRate($total, $fees);
        $percentage = $percentageRate / 100;

        return round(($total - $this->flatFee) / (1 + $percentage), 2);
    }

    // Setter methods for dynamic configuration
    public function setLocalFees(array $localFees): self
    {
        $this->localFees = $localFees;
        return $this;
    }

    public function setInternationalFees(array $internationalFees): self
    {
        $this->internationalFees = $internationalFees;
        return $this;
    }

    public function setFlatFee(float $flatFee): self
    {
        $this->flatFee = $flatFee;
        return $this;
    }
}