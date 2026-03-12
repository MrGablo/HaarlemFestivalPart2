<?php

declare(strict_types=1);

namespace App\Utils;

final class CmsForm
{
    public static function escapeHtml(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public static function renderContent(array $node, array $path = []): void
    {
        foreach ($node as $key => $value) {
            $currentPath = [...$path, (string)$key];

            if (is_array($value)) {
                self::renderArrayNode((string)$key, $value, $currentPath);
                continue;
            }

            self::renderScalar($currentPath, $value);
        }
    }

    private static function renderArrayNode(string $key, array $value, array $currentPath): void
    {
        self::renderFieldsetStart(self::label($key));

        if ($value === []) {
            self::renderEmptyNode();
            echo '</fieldset>';
            return;
        }

        self::renderNestedContent($value, $currentPath);
        echo '</fieldset>';
    }

    private static function renderFieldsetStart(string $label): void
    {
        echo '<fieldset class="rounded-xl border border-slate-200 p-4">';
        echo '<legend class="px-1 text-sm font-semibold text-slate-800">' . self::escapeHtml($label) . '</legend>';
    }

    private static function renderEmptyNode(): void
    {
        echo '<p class="text-sm text-slate-500">Empty array/object.</p>';
    }

    private static function renderNestedContent(array $value, array $currentPath): void
    {
        echo '<div class="mt-3 grid grid-cols-1 gap-3">';
        self::renderContent($value, $currentPath);
        echo '</div>';
    }

    private static function inputName(string $root, array $path): string
    {
        $name = $root;
        foreach ($path as $segment) {
            $name .= '[' . (string)$segment . ']';
        }
        return $name;
    }

    private static function label(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }

    private static function renderScalar(array $path, mixed $value): void
    {
        $field = self::buildFieldMeta($path, $value);

        self::renderFieldStart($field['label'], $field['typeName'], $field['effectiveType']);

        if ($field['isHtmlField']) {
            self::renderHtmlEditor($field['contentName'], $field['stringValue']);
            echo '</div>';
            return;
        }

        if ($field['type'] === 'integer' || $field['type'] === 'double') {
            self::renderNumberInput($field['contentName'], $field['stringValue']);
        } elseif ($field['type'] === 'bool' || $field['type'] === 'boolean') {
            self::renderBooleanSelect($field['contentName'], (bool)$value);
        } else {
            self::renderTextLikeInput($field['contentName'], $field['stringValue'], $field['type']);
        }

        echo '</div>';
    }

    private static function buildFieldMeta(array $path, mixed $value): array
    {
        $key = (string)end($path);
        $type = get_debug_type($value);
        $stringValue = is_scalar($value) ? (string)$value : '';
        $isHtmlField = self::isHtmlField($key, $stringValue);

        return [
            'label' => self::label($key),
            'contentName' => self::inputName('content', $path),
            'typeName' => self::inputName('types', $path),
            'type' => $type,
            'stringValue' => $stringValue,
            'isHtmlField' => $isHtmlField,
            'effectiveType' => ($isHtmlField && $type === 'null') ? 'string' : $type,
        ];
    }

    private static function renderFieldStart(string $label, string $typeName, string $effectiveType): void
    {
        echo '<div class="rounded-lg border border-slate-200 p-3">';
        echo '<label class="mb-1 block text-sm font-medium text-slate-700">' . self::escapeHtml($label) . '</label>';
        echo '<input type="hidden" name="' . self::escapeHtml($typeName) . '" value="' . self::escapeHtml($effectiveType) . '">';
    }

    private static function renderHtmlEditor(string $contentName, string $value): void
    {
        echo '<textarea name="' . self::escapeHtml($contentName) . '" rows="8" class="js-wysiwyg w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">' . self::escapeHtml($value) . '</textarea>';
        echo '<p class="mt-1 text-xs text-slate-500">Rich text field (HTML enabled).</p>';
    }

    private static function renderNumberInput(string $contentName, string $value): void
    {
        echo '<input name="' . self::escapeHtml($contentName) . '" type="number" step="any" value="' . self::escapeHtml($value) . '" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">';
    }

    private static function renderBooleanSelect(string $contentName, bool $isTrue): void
    {
        echo '<select name="' . self::escapeHtml($contentName) . '" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">';
        echo '<option value="1"' . ($isTrue ? ' selected' : '') . '>True</option>';
        echo '<option value="0"' . (!$isTrue ? ' selected' : '') . '>False</option>';
        echo '</select>';
    }

    private static function renderTextLikeInput(string $contentName, string $value, string $type): void
    {
        if ($type === 'null') {
            echo '<input name="' . self::escapeHtml($contentName) . '" type="text" value="" placeholder="null" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">';
            return;
        }

        $useTextarea = strlen($value) > 120 || str_contains($value, "\n");

        if ($useTextarea) {
            echo '<textarea name="' . self::escapeHtml($contentName) . '" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">' . self::escapeHtml($value) . '</textarea>';
            return;
        }

        echo '<input name="' . self::escapeHtml($contentName) . '" type="text" value="' . self::escapeHtml($value) . '" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">';
    }

    private static function isHtmlField(string $key, string $value): bool
    {
        $keyLower = strtolower($key);
        if (str_contains($keyLower, 'html') || str_contains($keyLower, 'wysiwyg')) {
            return true;
        }

        return preg_match('/<[^>]+>/', $value) === 1;
    }
}
