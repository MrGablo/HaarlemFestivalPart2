<?php

namespace App\Services;

use App\Models\PassEvent;
use App\Repositories\PassRepository;
use App\Repositories\Interfaces\IPassRepository;

class PassService
{
    public function __construct(
        private IPassRepository $passRepo = new PassRepository()
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function allPassProducts(): array
    {
        if (!$this->passRepo instanceof PassRepository) {
            return [];
        }

        return $this->passRepo->getAllPassProducts();
    }

    /** @return array<string, mixed>|null */
    public function findPassProduct(int $eventId): ?array
    {
        if ($eventId <= 0 || !$this->passRepo instanceof PassRepository) {
            return null;
        }

        return $this->passRepo->findPassProductByEventId($eventId);
    }

    /** @return array<int, string> */
    public function getFestivalTypes(): array
    {
        return ['jazz', 'dance'];
    }

    /** @return array<int, string> */
    public function getPassScopes(): array
    {
        return ['day', 'all_days'];
    }

    public function createPassProductFromInput(array $input): int
    {
        if (!$this->passRepo instanceof PassRepository) {
            throw new \RuntimeException('Pass management requires PassRepository.');
        }

        $title = $this->requireTitle((string)($input['title'] ?? ''));
        $festivalType = $this->requireFestivalType((string)($input['festival_type'] ?? ''));
        $passScope = $this->requirePassScope((string)($input['pass_scope'] ?? ''));
        $basePrice = $this->requireBasePrice((string)($input['base_price'] ?? ''));
        $active = $this->parseActive($input);

        return $this->passRepo->createPassProduct($title, $festivalType, $passScope, $basePrice, $active);
    }

    public function updatePassProductFromInput(int $eventId, array $input): void
    {
        if ($eventId <= 0) {
            throw new \RuntimeException('Invalid pass id.');
        }

        if (!$this->passRepo instanceof PassRepository) {
            throw new \RuntimeException('Pass management requires PassRepository.');
        }

        if ($this->findPassProduct($eventId) === null) {
            throw new \RuntimeException('Pass not found.');
        }

        $title = $this->requireTitle((string)($input['title'] ?? ''));
        $festivalType = $this->requireFestivalType((string)($input['festival_type'] ?? ''));
        $passScope = $this->requirePassScope((string)($input['pass_scope'] ?? ''));
        $basePrice = $this->requireBasePrice((string)($input['base_price'] ?? ''));
        $active = $this->parseActive($input);

        $this->passRepo->updatePassProduct($eventId, $title, $festivalType, $passScope, $basePrice, $active);
    }

    public function deletePassProduct(int $eventId): bool
    {
        if ($eventId <= 0) {
            throw new \RuntimeException('Invalid pass id.');
        }

        if (!$this->passRepo instanceof PassRepository) {
            throw new \RuntimeException('Pass management requires PassRepository.');
        }

        if ($this->findPassProduct($eventId) === null) {
            return false;
        }

        return $this->passRepo->deletePassProductByEventId($eventId);
    }

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

    private function requireTitle(string $title): string
    {
        $title = trim($title);
        if ($title === '') {
            throw new \RuntimeException('Title is required.');
        }

        if (mb_strlen($title) > 255) {
            throw new \RuntimeException('Title must be 255 characters or fewer.');
        }

        return $title;
    }

    private function requireFestivalType(string $festivalType): string
    {
        $festivalType = strtolower(trim($festivalType));
        if (!in_array($festivalType, $this->getFestivalTypes(), true)) {
            throw new \RuntimeException('Festival type is invalid.');
        }

        return $festivalType;
    }

    private function requirePassScope(string $passScope): string
    {
        $passScope = strtolower(trim($passScope));
        if (!in_array($passScope, $this->getPassScopes(), true)) {
            throw new \RuntimeException('Pass scope is invalid.');
        }

        return $passScope;
    }

    private function requireBasePrice(string $basePrice): float
    {
        $basePrice = trim($basePrice);
        if ($basePrice === '' || !is_numeric($basePrice)) {
            throw new \RuntimeException('Base price must be numeric.');
        }

        $value = (float)$basePrice;
        if ($value < 0) {
            throw new \RuntimeException('Base price cannot be negative.');
        }

        return $value;
    }

    private function parseActive(array $input): bool
    {
        return isset($input['active']) && (string)$input['active'] === '1';
    }
}
