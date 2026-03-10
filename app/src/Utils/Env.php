<?php

declare(strict_types=1);

namespace App\Utils;

final class Env
{
    private static bool $loaded = false;

    public static function load(?string $appRoot = null): void
    {
        if (self::$loaded) {
            return;
        }

        $appRoot = $appRoot ?? dirname(__DIR__, 2);
        $paths = [
            $appRoot . '/.env',
            dirname($appRoot) . '/.env',
        ];

        $uniquePaths = array_values(array_unique($paths));

        foreach ($uniquePaths as $path) {
            if (is_file($path) && is_readable($path)) {
                self::loadFile($path);
            }
        }

        self::$loaded = true;
    }

    private static function loadFile(string $path): void
    {
        $lines = @file($path, FILE_IGNORE_NEW_LINES);
        if (!is_array($lines)) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_starts_with($line, 'export ')) {
                $line = trim(substr($line, 7));
            }

            $separatorPos = strpos($line, '=');
            if ($separatorPos === false || $separatorPos === 0) {
                continue;
            }

            $name = trim(substr($line, 0, $separatorPos));
            if (!preg_match('/^[A-Z0-9_]+$/i', $name)) {
                continue;
            }

            $rawValue = trim(substr($line, $separatorPos + 1));
            $value = self::parseValue($rawValue);
            self::setValue($name, $value);
        }
    }

    private static function parseValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $first = $value[0];
        $last = $value[strlen($value) - 1];

        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            $value = substr($value, 1, -1);
        } else {
            $hashPos = strpos($value, ' #');
            if ($hashPos !== false) {
                $value = substr($value, 0, $hashPos);
            }
            $value = trim($value);
        }

        return $value;
    }

    private static function setValue(string $name, string $value): void
    {
        $existing = self::getExistingValue($name);
        $hasExisting = $existing !== null;
        $existingIsNonEmpty = $hasExisting && $existing !== '';
        $newIsNonEmpty = $value !== '';

        if ($existingIsNonEmpty) {
            return;
        }

        if ($hasExisting && !$newIsNonEmpty) {
            return;
        }

        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        putenv($name . '=' . $value);
    }

    private static function getExistingValue(string $name): ?string
    {
        if (array_key_exists($name, $_ENV)) {
            return (string)$_ENV[$name];
        }

        if (array_key_exists($name, $_SERVER)) {
            return (string)$_SERVER[$name];
        }

        $processValue = getenv($name);
        if ($processValue !== false) {
            return (string)$processValue;
        }

        return null;
    }
}
