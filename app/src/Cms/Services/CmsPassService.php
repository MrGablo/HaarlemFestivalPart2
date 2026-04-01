<?php

declare(strict_types=1);

namespace App\Cms\Services;

use App\Repositories\PassRepository;

final class CmsPassService
{
    public function __construct(private PassRepository $passes = new PassRepository()) {}

    /** @return array<int, array<string, mixed>> */
    public function allPassProducts(): array
    {
        return $this->passes->getAllPassProducts();
    }

    /** @return array<string, mixed>|null */
    public function findPassProduct(int $eventId): ?array
    {
        if ($eventId <= 0) {
            return null;
        }

        return $this->passes->findPassProductByEventId($eventId);
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
        $title = $this->requireTitle((string)($input['title'] ?? ''));
        $festivalType = $this->requireFestivalType((string)($input['festival_type'] ?? ''));
        $passScope = $this->requirePassScope((string)($input['pass_scope'] ?? ''));
        $basePrice = $this->requireBasePrice((string)($input['base_price'] ?? ''));
        $active = $this->parseActive($input);

        return $this->passes->createPassProduct($title, $festivalType, $passScope, $basePrice, $active);
    }

    public function updatePassProductFromInput(int $eventId, array $input): void
    {
        if ($eventId <= 0) {
            throw new \RuntimeException('Invalid pass id.');
        }

        if ($this->findPassProduct($eventId) === null) {
            throw new \RuntimeException('Pass not found.');
        }

        $title = $this->requireTitle((string)($input['title'] ?? ''));
        $festivalType = $this->requireFestivalType((string)($input['festival_type'] ?? ''));
        $passScope = $this->requirePassScope((string)($input['pass_scope'] ?? ''));
        $basePrice = $this->requireBasePrice((string)($input['base_price'] ?? ''));
        $active = $this->parseActive($input);

        $this->passes->updatePassProduct($eventId, $title, $festivalType, $passScope, $basePrice, $active);
    }

    public function deletePassProduct(int $eventId): bool
    {
        if ($eventId <= 0) {
            throw new \RuntimeException('Invalid pass id.');
        }

        if ($this->findPassProduct($eventId) === null) {
            return false;
        }

        return $this->passes->deletePassProductByEventId($eventId);
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
