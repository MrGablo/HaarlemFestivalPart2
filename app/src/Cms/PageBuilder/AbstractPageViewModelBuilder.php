<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder;

use App\Utils\Wysiwyg;

abstract class AbstractPageViewModelBuilder implements PageViewModelBuilderInterface
{
    public function normalizeInput(array $input): array
    {
        return $this->normalizeSections($this->editorSchema(), $input);
    }

    /** @param array<int, array<string, mixed>> $sections */
    protected function normalizeSections(array $sections, array $input): array
    {
        $normalized = [];

        foreach ($sections as $section) {
            foreach (($section['fields'] ?? []) as $field) {
                if (!is_array($field) || !isset($field['key'])) {
                    continue;
                }

                $key = (string)$field['key'];
                $normalized[$key] = $this->normalizeField($field, $input[$key] ?? null);
            }
        }

        return $normalized;
    }

    /** @param array<string, mixed> $field */
    protected function normalizeField(array $field, mixed $value): mixed
    {
        $type = (string)($field['type'] ?? 'text');

        return match ($type) {
            'object' => $this->normalizeObject($field, $value),
            'repeater' => $this->normalizeRepeater($field, $value),
            'textarea' => $this->normalizeString($value, true, (string)($field['default'] ?? '')),
            'wysiwyg' => Wysiwyg::render(is_scalar($value) ? (string)$value : (string)($field['default'] ?? '')),
            'number' => $this->normalizeNumber($field, $value),
            'select' => $this->normalizeSelect($field, $value),
            default => $this->normalizeString($value, true, (string)($field['default'] ?? '')),
        };
    }

    /** @param array<string, mixed> $field */
    private function normalizeObject(array $field, mixed $value): array
    {
        if (!is_array($value)) {
            $stringKey = (string)($field['coerceStringKey'] ?? '');
            if ($stringKey !== '' && is_scalar($value) && trim((string)$value) !== '') {
                $value = [$stringKey => (string)$value];
            } else {
                $value = [];
            }
        }

        $normalized = [];
        foreach (($field['fields'] ?? []) as $childField) {
            if (!is_array($childField) || !isset($childField['key'])) {
                continue;
            }

            $childKey = (string)$childField['key'];
            $normalized[$childKey] = $this->normalizeField($childField, $value[$childKey] ?? null);
        }

        return $normalized;
    }

    /** @param array<string, mixed> $field */
    private function normalizeRepeater(array $field, mixed $value): array
    {
        $items = is_array($value) ? array_values($value) : [];
        $itemType = (string)($field['itemType'] ?? 'object');
        $normalized = [];

        foreach ($items as $item) {
            if ($itemType === 'text') {
                $itemField = is_array($field['itemField'] ?? null) ? $field['itemField'] : ['type' => 'text', 'default' => ''];
                $normalizedItem = $this->normalizeField($itemField, $item);
                if (!$this->isEmptyValue($normalizedItem)) {
                    $normalized[] = $normalizedItem;
                }
                continue;
            }

            $itemArray = is_array($item) ? $item : [];
            $normalizedItem = [];
            foreach (($field['fields'] ?? []) as $childField) {
                if (!is_array($childField) || !isset($childField['key'])) {
                    continue;
                }

                $childKey = (string)$childField['key'];
                $normalizedItem[$childKey] = $this->normalizeField($childField, $itemArray[$childKey] ?? null);
            }

            if (!$this->isEmptyValue($normalizedItem)) {
                $normalized[] = $normalizedItem;
            }
        }

        return $normalized;
    }

    /** @param array<string, mixed> $field */
    private function normalizeNumber(array $field, mixed $value): int|float|string|null
    {
        if (!is_scalar($value)) {
            return $field['default'] ?? null;
        }

        $raw = trim((string)$value);
        if ($raw === '') {
            return $field['default'] ?? null;
        }

        $mode = (string)($field['mode'] ?? 'float');
        return $mode === 'int' ? (int)$raw : (float)$raw;
    }

    /** @param array<string, mixed> $field */
    private function normalizeSelect(array $field, mixed $value): string
    {
        $raw = $this->normalizeString($value, true, (string)($field['default'] ?? ''));
        $options = is_array($field['options'] ?? null) ? $field['options'] : [];

        foreach ($options as $option) {
            if (is_array($option) && (string)($option['value'] ?? '') === $raw) {
                return $raw;
            }
        }

        return (string)($field['default'] ?? '');
    }

    protected function normalizeString(mixed $value, bool $trim, string $default = ''): string
    {
        if (!is_scalar($value)) {
            return $default;
        }

        $string = (string)$value;
        return $trim ? trim($string) : $string;
    }

    protected function isEmptyValue(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $child) {
                if (!$this->isEmptyValue($child)) {
                    return false;
                }
            }
            return true;
        }

        if ($value === null) {
            return true;
        }

        return is_string($value) ? trim($value) === '' : false;
    }
}