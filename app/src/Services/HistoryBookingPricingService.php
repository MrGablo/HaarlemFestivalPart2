<?php

declare(strict_types=1);

namespace App\Services;

class HistoryBookingPricingService
{
    private const DEFAULT_FAMILY_SIZE = 4;
    private const DEFAULT_FAMILY_PRICE = 60.0;

    public function maxTicketsPerOrder(): int
    {
        return self::DEFAULT_FAMILY_SIZE;
    }

    public function familyBundleSize(): int
    {
        return self::DEFAULT_FAMILY_SIZE;
    }

    public function defaultFamilyBundlePrice(): float
    {
        return self::DEFAULT_FAMILY_PRICE;
    }

    /** @return array{unit_price: float, total_price: float, applied_family_price: bool} */
    public function resolvePricing(float $baseUnitPrice, ?float $familyBundlePrice, int $quantity): array
    {
        $quantity = max(1, $quantity);
        if ($quantity === $this->familyBundleSize()) {
            $familyTotal = $this->normalizeMoney($familyBundlePrice, $this->defaultFamilyBundlePrice());

            return [
                'unit_price' => round($familyTotal / $quantity, 2),
                'total_price' => $familyTotal,
                'applied_family_price' => true,
            ];
        }

        return [
            'unit_price' => $baseUnitPrice,
            'total_price' => round($baseUnitPrice * $quantity, 2),
            'applied_family_price' => false,
        ];
    }

    private function normalizeMoney(mixed $value, float $fallback): float
    {
        if (is_numeric($value)) {
            $parsed = (float)$value;
            return $parsed > 0 ? $parsed : $fallback;
        }

        return $fallback;
    }
}