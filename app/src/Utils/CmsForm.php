<?php

declare(strict_types=1);

namespace App\Utils;

final class CmsForm
{
    public static function h(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public static function uploadFieldName(array $path): string
    {
        return 'upload_' . self::inputId($path);
    }

    /** @param array<int, array<string, mixed>> $sections */
    public static function renderSchema(array $sections, array $content): void
    {
        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            echo '<section class="rounded-xl border border-slate-200 p-4">';

            if (!empty($section['title'])) {
                echo '<div class="mb-4">';
                echo '<h2 class="text-lg font-semibold text-slate-900">' . self::h((string)$section['title']) . '</h2>';
                if (!empty($section['description'])) {
                    echo '<p class="mt-1 text-sm text-slate-600">' . self::h((string)$section['description']) . '</p>';
                }
                echo '</div>';
            }

            echo '<div class="grid grid-cols-1 gap-4">';
            foreach (($section['fields'] ?? []) as $field) {
                if (!is_array($field) || !isset($field['key'])) {
                    continue;
                }

                $fieldKey = (string)$field['key'];
                self::renderSchemaField($field, $content[$fieldKey] ?? null, [$fieldKey]);
            }
            echo '</div>';
            echo '</section>';
        }
    }

    public static function renderContent(array $node, array $path = []): void
    {
        if (self::isSectionsList($node)) {
            self::renderSectionsList($node, $path);
            return;
        }

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

    private static function isSectionsList(array $node): bool
    {
        if (!array_is_list($node) || $node === []) {
            return false;
        }

        foreach ($node as $item) {
            if (!is_array($item)) {
                return false;
            }

            if (!array_key_exists('sectionType', $item) || !array_key_exists('data', $item)) {
                return false;
            }
        }

        return true;
    }

    private static function renderSectionsList(array $sections, array $path): void
    {
        echo '<div class="grid grid-cols-1 gap-4">';

        foreach ($sections as $index => $section) {
            $sectionType = (string)($section['sectionType'] ?? '');
            $sectionData = is_array($section['data'] ?? null) ? $section['data'] : [];
            $sectionPath = [...$path, (string)$index];

            echo '<section class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/70">';
            echo '<div class="border-b border-slate-200 bg-white px-4 py-3">';
            echo '<p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Section</p>';
            echo '<h2 class="mt-1 text-lg font-semibold text-slate-900">' . self::h(self::label($sectionType !== '' ? $sectionType : 'Section')) . '</h2>';
            echo '</div>';
            echo '<div class="p-4">';

            self::renderHiddenScalar([...$sectionPath, 'sectionType'], $sectionType, 'string');

            if ($sectionData === []) {
                echo '<p class="text-sm text-slate-500">This section has no editable fields.</p>';
            } else {
                echo '<div class="grid grid-cols-1 gap-3">';
                self::renderContent($sectionData, [...$sectionPath, 'data']);
                echo '</div>';
            }

            echo '</div>';
            echo '</section>';
        }

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
        $key = (string)end($path);
        $label = self::label($key);
        $contentName = self::inputName('content', $path);
        $typeName = self::inputName('types', $path);
        $type = get_debug_type($value);
        $stringValue = is_scalar($value) ? (string)$value : '';
        $isHtmlField = self::isHtmlField($key, $stringValue);
        $effectiveType = ($isHtmlField && $type === 'null') ? 'string' : $type;

        echo '<div class="rounded-lg border border-slate-200 p-3">';
        echo '<label class="mb-1 block text-sm font-medium text-slate-700">' . self::h($label) . '</label>';
        echo '<input type="hidden" name="' . self::h($typeName) . '" value="' . self::h($effectiveType) . '">';

        if ($isHtmlField) {
            echo '<textarea name="' . self::h($contentName) . '" rows="8" class="js-wysiwyg w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">' . self::h($stringValue) . '</textarea>';
            echo '<p class="mt-1 text-xs text-slate-500">Rich text field (HTML enabled).</p>';
            echo '</div>';
            return;
        }

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
            $useTextarea = strlen($stringValue) > 120 || str_contains($stringValue, "\n");
            if ($useTextarea) {
                echo '<textarea name="' . self::h($contentName) . '" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">' . self::h($stringValue) . '</textarea>';
            } else {
                echo '<input name="' . self::h($contentName) . '" type="text" value="' . self::h($stringValue) . '" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">';
            }
        }

        echo '</div>';
    }

    private static function renderHiddenScalar(array $path, mixed $value, string $type): void
    {
        $contentName = self::inputName('content', $path);
        $typeName = self::inputName('types', $path);

        echo '<input type="hidden" name="' . self::h($contentName) . '" value="' . self::h((string)$value) . '">';
        echo '<input type="hidden" name="' . self::h($typeName) . '" value="' . self::h($type) . '">';
    }

    private static function isHtmlField(string $key, string $value): bool
    {
        $keyLower = strtolower($key);
        if (str_contains($keyLower, 'html') || str_contains($keyLower, 'wysiwyg')) {
            return true;
        }

        return preg_match('/<[^>]+>/', $value) === 1;
    }

    /** @param array<string, mixed> $field */
    private static function renderSchemaField(array $field, mixed $value, array $path): void
    {
        $type = (string)($field['type'] ?? 'text');

        if ($type === 'object') {
            self::renderSchemaObject($field, $value, $path);
            return;
        }

        if ($type === 'repeater') {
            self::renderSchemaRepeater($field, $value, $path);
            return;
        }

        if ($type === 'image') {
            self::renderSchemaImage($field, $value, $path);
            return;
        }

        self::renderSchemaScalar($field, $value, $path);
    }

    /** @param array<string, mixed> $field */
    private static function renderSchemaObject(array $field, mixed $value, array $path): void
    {
        $value = is_array($value) ? $value : [];
        $label = (string)($field['label'] ?? self::label((string)($field['key'] ?? '')));

        echo '<fieldset class="rounded-xl border border-slate-200 p-4">';
        echo '<legend class="px-1 text-sm font-semibold text-slate-800">' . self::h($label) . '</legend>';
        self::renderFieldHelp($field);
        echo '<div class="mt-3 grid grid-cols-1 gap-4">';

        foreach (($field['fields'] ?? []) as $childField) {
            if (!is_array($childField) || !isset($childField['key'])) {
                continue;
            }

            $childKey = (string)$childField['key'];
            self::renderSchemaField($childField, $value[$childKey] ?? null, [...$path, $childKey]);
        }

        echo '</div>';
        echo '</fieldset>';
    }

    /** @param array<string, mixed> $field */
    private static function renderSchemaRepeater(array $field, mixed $value, array $path): void
    {
        $items = is_array($value) ? array_values($value) : [];
        $nextIndex = count($items);
        $label = (string)($field['label'] ?? self::label((string)($field['key'] ?? '')));
        $addLabel = (string)($field['addLabel'] ?? 'Add item');
        $repeaterId = self::inputId($path) . '_repeater';

        echo '<div class="rounded-xl border border-slate-200 p-4" data-repeater data-next-index="' . self::h((string)$nextIndex) . '">';
        echo '<div class="flex items-center justify-between gap-3">';
        echo '<div>';
        echo '<label class="block text-sm font-semibold text-slate-800">' . self::h($label) . '</label>';
        self::renderFieldHelp($field);
        echo '</div>';
        echo '<button type="button" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200" data-repeater-add="#' . self::h($repeaterId) . '">' . self::h($addLabel) . '</button>';
        echo '</div>';
        echo '<div id="' . self::h($repeaterId) . '" class="mt-4 space-y-3" data-repeater-items>';

        foreach ($items as $index => $item) {
            self::renderRepeaterItem($field, $item, $path, (string)$index, false);
        }

        echo '</div>';

        $template = self::capture(function () use ($field, $path): void {
            self::renderRepeaterItem($field, null, $path, '__INDEX__', true);
        });

        echo '<template data-repeater-template>' . $template . '</template>';
        echo '</div>';
    }

    /** @param array<string, mixed> $field */
    private static function renderRepeaterItem(array $field, mixed $item, array $path, string $index, bool $template): void
    {
        $itemType = (string)($field['itemType'] ?? 'object');
        $itemPath = [...$path, $index];
        $itemLabel = $index === '__INDEX__' ? 'New Item' : ('Item ' . ((int)$index + 1));

        echo '<div class="rounded-lg border border-slate-200 bg-slate-50 p-4" data-repeater-item>'; 
        echo '<div class="mb-3 flex items-center justify-between gap-3">';
        echo '<p class="text-sm font-semibold text-slate-800">' . self::h($itemLabel) . '</p>';
        echo '<button type="button" class="rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-200" data-repeater-remove>Remove</button>';
        echo '</div>';

        if ($itemType === 'text') {
            $itemField = is_array($field['itemField'] ?? null) ? $field['itemField'] : ['type' => 'text', 'label' => 'Value'];
            self::renderSchemaScalar($itemField, $item, $itemPath);
            echo '</div>';
            return;
        }

        if ($itemType === 'image') {
            $itemField = is_array($field['itemField'] ?? null) ? $field['itemField'] : ['type' => 'image', 'storage' => 'string', 'label' => 'Image'];
            self::renderSchemaImage($itemField, $item, $itemPath);
            echo '</div>';
            return;
        }

        $itemArray = is_array($item) ? $item : [];
        echo '<div class="grid grid-cols-1 gap-4">';
        foreach (($field['fields'] ?? []) as $childField) {
            if (!is_array($childField) || !isset($childField['key'])) {
                continue;
            }

            $childKey = (string)$childField['key'];
            self::renderSchemaField($childField, $itemArray[$childKey] ?? null, [...$itemPath, $childKey]);
        }
        echo '</div>';
        echo '</div>';
    }

    /** @param array<string, mixed> $field */
    private static function renderSchemaScalar(array $field, mixed $value, array $path): void
    {
        $type = (string)($field['type'] ?? 'text');
        $label = (string)($field['label'] ?? self::label((string)end($path)));
        $name = self::inputName('content', $path);
        $id = self::inputId($path);
        $required = !empty($field['required']) ? ' required' : '';
        $placeholder = isset($field['placeholder']) ? ' placeholder="' . self::h((string)$field['placeholder']) . '"' : '';
        $stringValue = is_scalar($value) ? (string)$value : (string)($field['default'] ?? '');

        echo '<div class="rounded-lg border border-slate-200 p-3">';
        echo '<label for="' . self::h($id) . '" class="mb-1 block text-sm font-medium text-slate-700">' . self::h($label) . '</label>';
        self::renderFieldHelp($field);

        if ($type === 'textarea' || $type === 'wysiwyg') {
            $rows = (int)($field['rows'] ?? ($type === 'wysiwyg' ? 8 : 4));
            $extraClass = $type === 'wysiwyg' ? ' js-wysiwyg' : '';
            echo '<textarea id="' . self::h($id) . '" name="' . self::h($name) . '" rows="' . self::h((string)$rows) . '" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200' . $extraClass . '"' . $required . $placeholder . '>' . self::h($stringValue) . '</textarea>';
            echo '</div>';
            return;
        }

        if ($type === 'select') {
            echo '<select id="' . self::h($id) . '" name="' . self::h($name) . '" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"' . $required . '>';
            foreach (($field['options'] ?? []) as $option) {
                if (!is_array($option)) {
                    continue;
                }

                $optionValue = (string)($option['value'] ?? '');
                $optionLabel = (string)($option['label'] ?? $optionValue);
                $selected = $optionValue === $stringValue ? ' selected' : '';
                echo '<option value="' . self::h($optionValue) . '"' . $selected . '>' . self::h($optionLabel) . '</option>';
            }
            echo '</select>';
            echo '</div>';
            return;
        }

        $inputType = $type === 'number' ? 'number' : 'text';
        $step = $type === 'number' ? ' step="' . self::h((string)($field['step'] ?? 'any')) . '"' : '';
        echo '<input id="' . self::h($id) . '" name="' . self::h($name) . '" type="' . self::h($inputType) . '" value="' . self::h($stringValue) . '" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"' . $required . $placeholder . $step . '>';
        echo '</div>';
    }

    /** @param array<string, mixed> $field */
    private static function renderSchemaImage(array $field, mixed $value, array $path): void
    {
        $storage = (string)($field['storage'] ?? 'object');
        $label = (string)($field['label'] ?? self::label((string)end($path)));
        $currentSrc = '';
        $meta = [];

        if ($storage === 'string') {
            $currentSrc = is_scalar($value) ? trim((string)$value) : '';
        } elseif (is_array($value)) {
            $currentSrc = trim((string)($value['src'] ?? ''));
            $meta = $value;
        } elseif (is_scalar($value)) {
            $currentSrc = trim((string)$value);
        }

        $previewSrc = self::previewSrc($currentSrc);
        $uploadFieldName = self::uploadFieldName($path);
        $hiddenName = $storage === 'string'
            ? self::inputName('content', $path)
            : self::inputName('content', [...$path, 'src']);

        echo '<div class="rounded-lg border border-slate-200 p-3">';
        echo '<label class="mb-1 block text-sm font-medium text-slate-700">' . self::h($label) . '</label>';
        self::renderFieldHelp($field);

        echo '<input type="hidden" name="' . self::h($hiddenName) . '" value="' . self::h($currentSrc) . '">';

        if ($previewSrc !== '') {
            echo '<div class="mb-3 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">';
            echo '<img src="' . self::h($previewSrc) . '" alt="' . self::h($label) . '" class="h-48 w-full object-cover">';
            echo '</div>';
        } else {
            echo '<div class="mb-3 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500">No image uploaded yet.</div>';
        }

        echo '<input name="' . self::h($uploadFieldName) . '" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">';
        echo '<p class="mt-1 text-xs text-slate-500">Upload a new image to replace the current one.</p>';

        if ($storage === 'object') {
            echo '<div class="mt-4 grid grid-cols-1 gap-4">';
            foreach (($field['fields'] ?? []) as $childField) {
                if (!is_array($childField) || !isset($childField['key'])) {
                    continue;
                }

                $childKey = (string)$childField['key'];
                self::renderSchemaField($childField, $meta[$childKey] ?? null, [...$path, $childKey]);
            }
            echo '</div>';
        }

        echo '</div>';
    }

    /** @param array<string, mixed> $field */
    private static function renderFieldHelp(array $field): void
    {
        if (empty($field['help'])) {
            return;
        }

        echo '<p class="mb-2 text-xs text-slate-500">' . self::h((string)$field['help']) . '</p>';
    }

    private static function inputId(array $path): string
    {
        $parts = array_map(static function (mixed $part): string {
            if ((string)$part === '__INDEX__') {
                return '__INDEX__';
            }
            $part = strtolower((string)$part);
            return preg_replace('/[^a-z0-9_]+/', '_', $part) ?? 'field';
        }, $path);

        return 'content_' . implode('_', $parts);
    }

    private static function previewSrc(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (preg_match('~^https?://~i', $path) === 1 || str_starts_with($path, '/')) {
            return $path;
        }

        return '/' . ltrim($path, '/');
    }

    private static function capture(callable $callback): string
    {
        ob_start();
        $callback();
        return (string)ob_get_clean();
    }
}
