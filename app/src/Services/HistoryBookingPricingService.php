<?php

declare(strict_types=1);

namespace App\Services;

use App\Cms\PageBuilder\Builders\HistoryHomePageBuilder;
use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\PageRepository;

class HistoryBookingPricingService
{
    private const DEFAULT_FAMILY_SIZE = 4;
    private const DEFAULT_FAMILY_PRICE = 60.0;

    private IPageRepository $pageRepository;
    private HistoryHomePageBuilder $builder;
    private ?array $bookingConfig = null;

    public function __construct(?IPageRepository $pageRepository = null, ?HistoryHomePageBuilder $builder = null)
    {
        $this->pageRepository = $pageRepository ?? new PageRepository();
        $this->builder = $builder ?? new HistoryHomePageBuilder();
    }

    public function maxTicketsPerOrder(): int
    {
        return self::DEFAULT_FAMILY_SIZE;
    }

    public function familyBundleSize(): int
    {
        return self::DEFAULT_FAMILY_SIZE;
    }

    public function familyBundlePrice(): float
    {
        $booking = $this->bookingConfig();
        return $this->parseMoney($booking['family_price_value'] ?? null, self::DEFAULT_FAMILY_PRICE);
    }

    /** @return array{unit_price: float, total_price: float, applied_family_price: bool} */
    public function resolvePricing(float $baseUnitPrice, int $quantity): array
    {
        $quantity = max(1, $quantity);
        if ($quantity === $this->familyBundleSize()) {
            $familyTotal = $this->familyBundlePrice();

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

    private function bookingConfig(): array
    {
        if ($this->bookingConfig !== null) {
            return $this->bookingConfig;
        }

        $content = $this->pageRepository->getPageContentByType($this->builder->pageType()) ?? [];
        $viewModel = $this->builder->buildViewModel(is_array($content) ? $content : []);

        $booking = property_exists($viewModel, 'booking') && is_array($viewModel->booking)
            ? $viewModel->booking
            : [];

        $this->bookingConfig = $booking;
        return $this->bookingConfig;
    }

    private function parseMoney(mixed $value, float $fallback): float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }

        if (!is_string($value)) {
            return $fallback;
        }

        $normalized = preg_replace('/[^0-9,\.]/', '', $value);
        if (!is_string($normalized) || $normalized === '') {
            return $fallback;
        }

        if (str_contains($normalized, ',') && !str_contains($normalized, '.')) {
            $normalized = str_replace(',', '.', $normalized);
        } else {
            $normalized = str_replace(',', '', $normalized);
        }

        return is_numeric($normalized) ? (float)$normalized : $fallback;
    }
}