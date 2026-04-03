<?php

declare(strict_types=1);

namespace App\Cms\Services;

use App\Services\PassService;

final class CmsPassService
{
    public function __construct(private PassService $service = new PassService()) {}

    /** @return array<int, array<string, mixed>> */
    public function allPassProducts(): array
    {
        return $this->service->allPassProducts();
    }

    /** @return array<string, mixed>|null */
    public function findPassProduct(int $eventId): ?array
    {
        return $this->service->findPassProduct($eventId);
    }

    /** @return array<int, string> */
    public function getFestivalTypes(): array
    {
        return $this->service->getFestivalTypes();
    }

    /** @return array<int, string> */
    public function getPassScopes(): array
    {
        return $this->service->getPassScopes();
    }

    public function createPassProductFromInput(array $input): int
    {
        return $this->service->createPassProductFromInput($input);
    }

    public function updatePassProductFromInput(int $eventId, array $input): void
    {
        $this->service->updatePassProductFromInput($eventId, $input);
    }

    public function deletePassProduct(int $eventId): bool
    {
        return $this->service->deletePassProduct($eventId);
    }
}
