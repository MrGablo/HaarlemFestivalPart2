<?php

namespace App\Cms\Services;

use App\Utils\Wysiwyg;

class CmsContentService
{
    public function normalizeContent(mixed $value, mixed $typeMeta): mixed
    {
        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $childType = is_array($typeMeta) && array_key_exists((string)$key, $typeMeta)
                    ? $typeMeta[(string)$key]
                    : null;
                $normalized[$key] = $this->normalizeContent($item, $childType);
            }
            return $normalized;
        }

        if (!is_string($typeMeta)) {
            return $value;
        }

        return $this->castScalar($value, $typeMeta);
    }

    private function castScalar(mixed $value, string $type): mixed
    {
        $raw = is_scalar($value) ? (string)$value : '';

        return match ($type) {
            'integer' => $raw === '' ? 0 : (int)$raw,
            'double' => $raw === '' ? 0.0 : (float)$raw,
            'bool', 'boolean' => in_array(strtolower($raw), ['1', 'true', 'yes', 'on'], true),
            'null' => null,
            default => $this->sanitizeIfHtml($raw),
        };
    }

    private function sanitizeIfHtml(string $value): string
    {
        if (preg_match('/<[^>]+>/', $value) !== 1) {
            return $value;
        }

        return Wysiwyg::render($value);
    }
}
