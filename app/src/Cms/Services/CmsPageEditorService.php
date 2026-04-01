<?php

declare(strict_types=1);

namespace App\Cms\Services;

use App\Cms\PageBuilder\Builders\GenericPageBuilder;
use App\Cms\PageBuilder\PageBuilderRegistry;
use App\Cms\PageBuilder\PageViewModelBuilderInterface;
use App\Services\UploadService;
use App\Utils\CmsForm;

final class CmsPageEditorService
{
    /**
     * @param array<int, string> $allowedTypes
     * @return array<int, array{type: string, label: string, suggestedTitle: string}>
     */
    public function creatablePageTypes(PageBuilderRegistry $registry, array $allowedTypes): array
    {
        $types = [];
        $builders = $registry->all();

        foreach ($allowedTypes as $pageType) {
            $builder = $builders[$pageType] ?? null;
            if ($builder === null || $builder instanceof GenericPageBuilder) {
                continue;
            }

            $label = $this->pageTypeLabel($pageType);
            $types[] = [
                'type' => $pageType,
                'label' => $label,
                'suggestedTitle' => $label,
            ];
        }

        return $types;
    }

    /**
     * @param array<int, string> $allowedTypes
     * @return array{type: string, label: string, suggestedTitle: string}|null
     */
    public function findCreatablePageType(string $pageType, PageBuilderRegistry $registry, array $allowedTypes): ?array
    {
        foreach ($this->creatablePageTypes($registry, $allowedTypes) as $type) {
            if ($type['type'] === $pageType) {
                return $type;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $contentInput
     * @param array<string, mixed> $files
     * @return array<string, mixed>
     */
    public function normalizeSchemaInput(
        PageViewModelBuilderInterface $builder,
        string $pageType,
        array $contentInput,
        array $files,
        UploadService $uploads
    ): array {
        $contentInput = $this->applySchemaUploads($builder->editorSchema(), $contentInput, $pageType, $files, $uploads);
        return $builder->normalizeInput($contentInput);
    }

    /**
     * @param array<int, array<string, mixed>> $schema
     * @param array<string, mixed> $contentInput
     * @param array<string, mixed> $files
     * @return array<string, mixed>
     */
    private function applySchemaUploads(
        array $schema,
        array $contentInput,
        string $pageType,
        array $files,
        UploadService $uploads
    ): array {
        foreach ($schema as $section) {
            foreach (($section['fields'] ?? []) as $field) {
                if (!is_array($field) || !isset($field['key'])) {
                    continue;
                }

                $key = (string)$field['key'];
                $contentInput[$key] = $this->applyFieldUploads($field, $contentInput[$key] ?? null, [$key], $pageType, $files, $uploads);
            }
        }

        return $contentInput;
    }

    /**
     * @param array<string, mixed> $field
     * @param array<int, string> $path
     * @param array<string, mixed> $files
     */
    private function applyFieldUploads(
        array $field,
        mixed $value,
        array $path,
        string $pageType,
        array $files,
        UploadService $uploads
    ): mixed {
        $type = (string)($field['type'] ?? 'text');

        if ($type === 'image') {
            return $this->applyImageUpload($field, $value, $path, $pageType, $files, $uploads);
        }

        if ($type === 'object') {
            $value = is_array($value) ? $value : [];
            foreach (($field['fields'] ?? []) as $childField) {
                if (!is_array($childField) || !isset($childField['key'])) {
                    continue;
                }

                $childKey = (string)$childField['key'];
                $value[$childKey] = $this->applyFieldUploads($childField, $value[$childKey] ?? null, [...$path, $childKey], $pageType, $files, $uploads);
            }
            return $value;
        }

        if ($type === 'repeater') {
            $items = is_array($value) ? array_values($value) : [];
            $itemType = (string)($field['itemType'] ?? 'object');

            foreach ($items as $index => $item) {
                $itemPath = [...$path, (string)$index];

                if ($itemType === 'image') {
                    $itemField = is_array($field['itemField'] ?? null) ? $field['itemField'] : ['type' => 'image', 'storage' => 'string'];
                    $items[$index] = $this->applyFieldUploads($itemField, $item, $itemPath, $pageType, $files, $uploads);
                    continue;
                }

                if ($itemType === 'text') {
                    continue;
                }

                $itemArray = is_array($item) ? $item : [];
                foreach (($field['fields'] ?? []) as $childField) {
                    if (!is_array($childField) || !isset($childField['key'])) {
                        continue;
                    }

                    $childKey = (string)$childField['key'];
                    $itemArray[$childKey] = $this->applyFieldUploads($childField, $itemArray[$childKey] ?? null, [...$itemPath, $childKey], $pageType, $files, $uploads);
                }
                $items[$index] = $itemArray;
            }

            return $items;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $field
     * @param array<int, string> $path
     * @param array<string, mixed> $files
     */
    private function applyImageUpload(
        array $field,
        mixed $value,
        array $path,
        string $pageType,
        array $files,
        UploadService $uploads
    ): mixed {
        $storage = (string)($field['storage'] ?? 'object');
        $currentPath = '';
        $meta = [];

        if ($storage === 'string') {
            $currentPath = is_scalar($value) ? trim((string)$value) : '';
        } elseif (is_array($value)) {
            $meta = $value;
            $currentPath = trim((string)($value['src'] ?? ''));
        } elseif (is_scalar($value)) {
            $currentPath = trim((string)$value);
        }

        $uploadField = CmsForm::uploadFieldName($path);
        if (
            isset($files[$uploadField]) &&
            is_array($files[$uploadField]) &&
            (int)($files[$uploadField]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
        ) {
            $folder = $this->slug($pageType);
            $storedPath = $uploads->storeImage($files[$uploadField], 'page', $folder, null, false, $currentPath);
            $currentPath = $storedPath;
        }

        if ($storage === 'string') {
            return $currentPath;
        }

        $meta['src'] = $currentPath;
        return $meta;
    }

    private function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('~[^a-z0-9_-]+~', '-', $value) ?? $value;
        return trim($value, '-') ?: 'page';
    }

    private function pageTypeLabel(string $pageType): string
    {
        return match ($pageType) {
            'Jazz_Detail_Page' => 'Jazz Artist Detail Page',
            default => ucwords(strtolower(str_replace(['_', '-'], ' ', $pageType))),
        };
    }
}
