<?php

declare(strict_types=1);

namespace App\Services;

class EventValidationService
{
    public function requireText(array $input, string $key, string $label): string
    {
        $raw = trim((string)($input[$key] ?? ''));
        if ($raw === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        return $raw;
    }

    public function parsePrice(string $raw): float
    {
        $raw = trim($raw);
        if ($raw === '') {
            throw new \RuntimeException('Price is required.');
        }

        if (!is_numeric($raw)) {
            throw new \RuntimeException('Price must be numeric.');
        }

        $price = (float)$raw;
        if ($price < 0) {
            throw new \RuntimeException('Price cannot be negative.');
        }

        return $price;
    }

    public function parseOptionalPositiveInt(string $raw, string $label): ?int
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $value = (int)$raw;
        if ($value <= 0) {
            throw new \RuntimeException($label . ' must be a positive integer.');
        }

        return $value;
    }

    public function parseRequiredPositiveInt(string $raw, string $label): int
    {
        $raw = trim($raw);
        if ($raw === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        $value = (int)$raw;
        if ($value <= 0) {
            throw new \RuntimeException($label . ' must be a positive integer.');
        }

        return $value;
    }

    public function normalizeDateTime(string $input): string
    {
        $input = trim($input);

        if ($input === '') {
            throw new \RuntimeException('Date/time fields are required.');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $input)) {
            return str_replace('T', ' ', $input) . ':00';
        }

        try {
            $dt = new \DateTime($input);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            throw new \RuntimeException('Invalid date/time format.');
        }
    }
}
