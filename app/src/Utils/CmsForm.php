<?php

declare(strict_types=1);

namespace App\Utils;

final class CmsForm
{
    public static function h(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public static function renderContent(array $node, array $path = []): void
    {
        foreach ($node as $key => $value) {
            $currentPath = [...$path, (string)$key];

            if (is_array($value)) {
                echo '<fieldset class="rounded-xl border border-slate-200 p-4">';
                echo '<legend class="px-1 text-sm font-semibold text-slate-800">' . self::h(self::label((string)$key)) . '</legend>';

                if ($value === []) {
                    echo '<p class="text-sm text-slate-500">Empty array/object.</p>';
                } else {
                    echo '<div class="mt-3 grid grid-cols-1 gap-3">';
                    self::renderContent($value, $currentPath);
                    echo '</div>';
                }

                echo '</fieldset>';
                continue;
            }

            self::renderScalar($currentPath, $value);
        }
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
        $key = (string)end($path);
        $label = self::label($key);
        $contentName = self::inputName('content', $path);
        $typeName = self::inputName('types', $path);
        $type = get_debug_type($value);

        echo '<div class="rounded-lg border border-slate-200 p-3">';
        echo '<label class="mb-1 block text-sm font-medium text-slate-700">' . self::h($label) . '</label>';
        echo '<input type="hidden" name="' . self::h($typeName) . '" value="' . self::h($type) . '">';

        if ($type === 'integer' || $type === 'double') {
            echo '<input name="' . self::h($contentName) . '" type="number" step="any" value="' . self::h((string)$value) . '" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">';
        } elseif ($type === 'bool' || $type === 'boolean') {
            $isTrue = (bool)$value;
            echo '<select name="' . self::h($contentName) . '" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">';
            echo '<option value="1"' . ($isTrue ? ' selected' : '') . '>True</option>';
            echo '<option value="0"' . (!$isTrue ? ' selected' : '') . '>False</option>';
            echo '</select>';
        } elseif ($type === 'null') {
            echo '<input name="' . self::h($contentName) . '" type="text" value="" placeholder="null" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">';
        } else {
            $stringValue = (string)$value;
            $useTextarea = strlen($stringValue) > 120 || str_contains($stringValue, "\n");
            if ($useTextarea) {
                echo '<textarea name="' . self::h($contentName) . '" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">' . self::h($stringValue) . '</textarea>';
            } else {
                echo '<input name="' . self::h($contentName) . '" type="text" value="' . self::h($stringValue) . '" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">';
            }
        }

        echo '</div>';
    }
}
