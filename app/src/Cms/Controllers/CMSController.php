<?php

namespace App\Cms\Controllers;

use App\Cms\PageBuilder\Builders\GenericPageBuilder;
use App\Cms\PageBuilder\PageBuilderRegistry;
use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\PageRepository;
use App\Cms\Services\CmsContentService;
use App\Services\UploadService;
use App\Utils\AdminGuard;
use App\Utils\CmsForm;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

class CMSController
{
    /** @var array<int, string> */
    private const CREATABLE_PAGE_TYPES = ['Jazz_Detail_Page'];

    private IPageRepository $pages;
    private CmsContentService $contentService;
    private PageBuilderRegistry $pageBuilders;
    private UploadService $uploads;

    public function __construct()
    {
        $this->pages = new PageRepository();
        $this->contentService = new CmsContentService();
        $this->pageBuilders = new PageBuilderRegistry();
        $this->uploads = new UploadService();

        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $pages = $this->pages->getAllPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../../Views/cms/index.php';
    }

    public function generalIndex(): void
    {
        AdminGuard::requireAdmin(true);
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../../Views/cms/generalIndex.php';
    }

    public function createType(): void
    {
        AdminGuard::requireAdmin(true);

        $pageTypes = $this->creatablePageTypes();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../../Views/cms/create_type.php';
    }

    public function createForm(string $type): void
    {
        AdminGuard::requireAdmin(true);

        $definition = $this->findCreatablePageType($type);
        if ($definition === null) {
            Flash::setErrors(['general' => 'Unsupported page type.']);
            header('Location: /cms/page/create', true, 302);
            exit;
        }

        $builder = $this->pageBuilders->resolveForPageType($definition['type']);
        if ($builder instanceof GenericPageBuilder) {
            Flash::setErrors(['general' => 'No schema builder is registered for this page type.']);
            header('Location: /cms/page/create', true, 302);
            exit;
        }

        $old = Flash::getOld();
        $oldContent = is_array($old['content'] ?? null) ? $old['content'] : [];
        $content = $oldContent !== [] ? $oldContent : $builder->normalizeInput([]);

        $pageType = $definition['type'];
        $pageTypeLabel = $definition['label'];
        $pageTitle = trim((string)($old['page_title'] ?? $definition['suggestedTitle']));
        $editorSchema = $builder->editorSchema();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/create.php';
    }

    public function create(string $type): void
    {
        AdminGuard::requireAdmin(true);

        $definition = $this->findCreatablePageType($type);
        if ($definition === null) {
            Flash::setErrors(['general' => 'Unsupported page type.']);
            header('Location: /cms/page/create', true, 302);
            exit;
        }

        try {
            Csrf::assertPost();

            $builder = $this->pageBuilders->resolveForPageType($definition['type']);
            if ($builder instanceof GenericPageBuilder) {
                throw new \RuntimeException('No schema builder is registered for this page type.');
            }

            $pageTitle = trim((string)($_POST['page_title'] ?? ''));
            if ($pageTitle === '') {
                throw new \RuntimeException('Page title is required.');
            }

            $contentInput = $_POST['content'] ?? [];
            if (!is_array($contentInput)) {
                throw new \RuntimeException('Invalid content payload.');
            }

            $contentInput = $this->applySchemaUploads($builder->editorSchema(), $contentInput, $definition['type']);
            $normalized = $builder->normalizeInput($contentInput);

            $newPageId = $this->pages->createPage($pageTitle, $definition['type'], $normalized);

            Flash::setSuccess('Page created successfully.');
            header('Location: /cms/page/' . $newPageId, true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            Flash::setOld([
                'page_title' => (string)($_POST['page_title'] ?? ''),
                'content' => is_array($_POST['content'] ?? null) ? $_POST['content'] : [],
            ]);
            header('Location: /cms/page/create/' . urlencode($definition['type']), true, 302);
            exit;
        }
    }

    // --- existing page edit/update ----

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $page = $this->pages->findPageById($id);
        if ($page === null) {
            http_response_code(404);
            echo 'Page not found.';
            return;
        }

        $pageType = (string)($page['Page_Type'] ?? '');
        $builder = $this->pageBuilders->resolveForPageType($pageType);
        $content = $this->pages->getPageContentById($id);
        $usesSchemaEditor = !$builder instanceof GenericPageBuilder;
        if ($usesSchemaEditor) {
            $content = $builder->normalizeInput($content);
        }

        $editorSchema = $builder->editorSchema();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $page = $this->pages->findPageById($id);
        if ($page === null) {
            Flash::setErrors(['general' => 'Page not found.']);
            header('Location: /cms', true, 302);
            exit;
        }
        //TODO move logic to service. streamine logic
        try {
            Csrf::assertPost();

            $contentInput = $_POST['content'] ?? [];
            if (!is_array($contentInput)) {
                throw new \RuntimeException('Invalid content payload.');
            }

            $pageType = (string)($page['Page_Type'] ?? '');
            $builder = $this->pageBuilders->resolveForPageType($pageType);

            if ($builder instanceof GenericPageBuilder) {
                $typeMap = $_POST['types'] ?? [];
                $normalized = $this->contentService->normalizeContent($contentInput, is_array($typeMap) ? $typeMap : []);
            } else {
                $contentInput = $this->applySchemaUploads($builder->editorSchema(), $contentInput, $pageType);
                $normalized = $builder->normalizeInput($contentInput);
            }

            $this->pages->savePageContentById($id, $normalized);

            Flash::setSuccess('Page content updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/page/' . $id, true, 302);
        exit;
    }

    /** @param array<int, array<string, mixed>> $schema */
    private function applySchemaUploads(array $schema, array $contentInput, string $pageType): array
    {
        foreach ($schema as $section) {
            foreach (($section['fields'] ?? []) as $field) {
                if (!is_array($field) || !isset($field['key'])) {
                    continue;
                }

                $key = (string)$field['key'];
                $contentInput[$key] = $this->applyFieldUploads($field, $contentInput[$key] ?? null, [$key], $pageType);
            }
        }

        return $contentInput;
    }

    private function applyFieldUploads(array $field, mixed $value, array $path, string $pageType): mixed
    {
        $type = (string)($field['type'] ?? 'text');

        if ($type === 'image') {
            return $this->applyImageUpload($field, $value, $path, $pageType);
        }

        if ($type === 'object') {
            $value = is_array($value) ? $value : [];
            foreach (($field['fields'] ?? []) as $childField) {
                if (!is_array($childField) || !isset($childField['key'])) {
                    continue;
                }

                $childKey = (string)$childField['key'];
                $value[$childKey] = $this->applyFieldUploads($childField, $value[$childKey] ?? null, [...$path, $childKey], $pageType);
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
                    $items[$index] = $this->applyFieldUploads($itemField, $item, $itemPath, $pageType);
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
                    $itemArray[$childKey] = $this->applyFieldUploads($childField, $itemArray[$childKey] ?? null, [...$itemPath, $childKey], $pageType);
                }
                $items[$index] = $itemArray;
            }

            return $items;
        }

        return $value;
    }

    private function applyImageUpload(array $field, mixed $value, array $path, string $pageType): mixed
    {
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
        if (isset($_FILES[$uploadField]) && is_array($_FILES[$uploadField]) && (int)($_FILES[$uploadField]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $folder = $this->slug($pageType);
            $storedPath = $this->uploads->storeImage($_FILES[$uploadField], 'page', $folder, null, false, $currentPath);
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

    /** @return array<int, array{type: string, label: string, suggestedTitle: string}> */
    private function creatablePageTypes(): array
    {
        $types = [];
        $builders = $this->pageBuilders->all();

        foreach (self::CREATABLE_PAGE_TYPES as $pageType) {
            $builder = $builders[$pageType] ?? null;
            if ($builder === null || $builder instanceof GenericPageBuilder) {
                continue;
            }

            $types[] = [
                'type' => $pageType,
                'label' => $this->pageTypeLabel($pageType),
                'suggestedTitle' => $this->pageTypeLabel($pageType),
            ];
        }

        return $types;
    }

    /** @return array{type: string, label: string, suggestedTitle: string}|null */
    private function findCreatablePageType(string $pageType): ?array
    {
        foreach ($this->creatablePageTypes() as $type) {
            if ($type['type'] === $pageType) {
                return $type;
            }
        }

        return null;
    }

    private function pageTypeLabel(string $pageType): string
    {
        return match ($pageType) {
            'Jazz_Detail_Page' => 'Jazz Artist Detail Page',
            default => ucwords(strtolower(str_replace(['_', '-'], ' ', $pageType))),
        };
    }
}
