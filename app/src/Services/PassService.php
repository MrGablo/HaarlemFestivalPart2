<?php

namespace App\Services;

use App\Models\PassEvent;
use App\Repositories\Interfaces\IPassRepository;

class PassService
{
    public function __construct(
        private IPassRepository $passRepo
    ) {}

    /**
     * @return array<int, array{event_id:int, label:string, pass_scope:string, base_price:float, requires_day_selection:bool, available_dates:array<int, string>}>
     */
    public function getPassButtonsForFestivalType(string $festivalType): array
    {
        $rows = $this->passRepo->getActivePassProductsByFestivalType($festivalType);
        $availableJazzDates = strtolower(trim($festivalType)) === 'jazz'
            ? $this->passRepo->getAvailableJazzPassDates()
            : [];

        $buttons = [];
        foreach ($rows as $pass) {
            if (!$pass instanceof PassEvent) {
                continue;
            }

            $eventId = (int)$pass->event_id;
            if ($eventId <= 0) {
                continue;
            }

            $title = trim((string)$pass->title);
            $price = (float)$pass->base_price;
            $priceLabel = rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.');
            $requiresDaySelection = $pass->requiresDaySelection();

            $buttons[] = [
                'event_id' => $eventId,
                'label' => $title . ': ' . $priceLabel . '€ p.p',
                'pass_scope' => (string)$pass->pass_scope,
                'base_price' => $price,
                'requires_day_selection' => $requiresDaySelection,
                'available_dates' => $requiresDaySelection ? $availableJazzDates : [],
            ];
        }

        return $buttons;
    }

    public function findActivePassProductByEventId(int $eventId): ?PassEvent
    {
        return $this->passRepo->findActivePassProductByEventId($eventId);
    }

    public function isValidJazzPassDate(string $isoDate): bool
    {
        $isoDate = trim($isoDate);
        if (!$this->isIsoDate($isoDate)) {
            return false;
        }

        $availableDates = $this->passRepo->getAvailableJazzPassDates();
        return in_array($isoDate, $availableDates, true);
    }

    private function isIsoDate(string $value): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        return $dt instanceof \DateTimeImmutable && $dt->format('Y-m-d') === $value;
    }
}
