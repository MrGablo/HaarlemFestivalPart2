<?php

namespace App\Services;

use App\Repositories\Interfaces\IPassRepository;

class PassService
{
    public function __construct(
        private IPassRepository $passRepo
    ) {}

    /**
     * @return array<int, array{event_id:int, label:string, pass_scope:string, base_price:float}>
     */
    public function getPassButtonsForFestivalType(string $festivalType): array
    {
        $rows = $this->passRepo->getActivePassProductsByFestivalType($festivalType);

        $buttons = [];
        foreach ($rows as $row) {
            $eventId = (int)($row['event_id'] ?? 0);
            if ($eventId <= 0) {
                continue;
            }

            $title = trim((string)($row['title'] ?? 'Pass'));
            $price = (float)($row['base_price'] ?? 0);
            $priceLabel = rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.');

            $buttons[] = [
                'event_id' => $eventId,
                'label' => $title . ': ' . $priceLabel . '€ p.p',
                'pass_scope' => (string)($row['pass_scope'] ?? ''),
                'base_price' => $price,
            ];
        }

        return $buttons;
    }
}
