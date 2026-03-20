<?php

namespace App\Controllers;

use App\Cms\PageBuilder\Builders\GenericPageBuilder;
use App\Cms\PageBuilder\PageBuilderRegistry;
use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\PageRepository;
use App\Services\CmsContentService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

class CMSController
{
    private IPageRepository $pages;
    private CmsContentService $contentService;
    private PageBuilderRegistry $pageBuilders;

    public function __construct()
    {
        $this->pages = new PageRepository();
        $this->contentService = new CmsContentService();
        $this->pageBuilders = new PageBuilderRegistry();

        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $pages = $this->pages->getAllPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../Views/cms/index.php';
    }

    public function generalIndex(): void
    {
        AdminGuard::requireAdmin(true);
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../Views/cms/generalIndex.php';
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

        require __DIR__ . '/../Views/cms/edit.php';
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
}
