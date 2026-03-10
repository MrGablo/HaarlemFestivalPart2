<?php

namespace App\Controllers;

use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\PageRepository;
use App\Services\CmsContentService;
use App\Utils\AdminGuard;
use App\Utils\Flash;
use App\Utils\Session;

class CMSController
{
    private IPageRepository $pages;
    private CmsContentService $contentService;

    public function __construct()
    {
        $this->pages = new PageRepository();
        $this->contentService = new CmsContentService();

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

        $content = $this->pages->getPageContentById($id);
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

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
            $contentInput = $_POST['content'] ?? [];
            $typeMap = $_POST['types'] ?? [];

            if (!is_array($contentInput)) {
                throw new \RuntimeException('Invalid content payload.');
            }

            $normalized = $this->contentService->normalizeContent($contentInput, is_array($typeMap) ? $typeMap : []);
            $this->pages->savePageContentById($id, $normalized);

            Flash::setSuccess('Page content updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/page/' . $id, true, 302);
        exit;
    }
}
