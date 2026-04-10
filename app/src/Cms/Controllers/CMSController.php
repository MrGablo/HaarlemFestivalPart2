<?php

namespace App\Cms\Controllers;

use App\Cms\PageBuilder\Builders\GenericPageBuilder;
use App\Cms\PageBuilder\PageBuilderRegistry;
use App\Cms\Services\CmsContentService;
use App\Cms\Services\CmsNavigationService;
use App\Cms\Services\CmsPageEditorService;
use App\Services\ArtistService;
use App\Services\JazzEventService;
use App\Services\UploadService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

class CMSController
{
    /** @var array<int, string> */
    private const CREATABLE_PAGE_TYPES = ['Jazz_Detail_Page', 'Dance_Detail_Page', 'Yummy_Detail_Page'];
    

    private ArtistService $artists;
    private CmsContentService $contentService;
    private JazzEventService $jazzEvents;
    private CmsNavigationService $navigation;
    private CmsPageEditorService $pageEditor;
    private PageBuilderRegistry $pageBuilders;
    private UploadService $uploads;

    public function __construct()
    {
        $this->artists = new ArtistService();
        $this->contentService = new CmsContentService();
        $this->jazzEvents = new JazzEventService();
        $this->navigation = new CmsNavigationService();
        $this->pageEditor = new CmsPageEditorService();
        $this->pageBuilders = new PageBuilderRegistry();
        $this->uploads = new UploadService();

        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $pages = $this->pageEditor->allPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/index.php';
    }

    public function generalIndex(): void
    {
        AdminGuard::requireAdmin(true);
        $modules = $this->navigation->overviewItems();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../../Views/cms/generalIndex.php';
    }

    public function createType(): void
    {
        try {
            AdminGuard::requireAdmin(true);

            $pageTypes = $this->pageEditor->creatablePageTypes($this->pageBuilders, self::CREATABLE_PAGE_TYPES);
            $errors = Flash::getErrors();
            $flashSuccess = Flash::getSuccess();

            require __DIR__ . '/../../Views/cms/create_type.php';
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            header('Location: /cms/pages', true, 302);
            exit;
        }
    }

    public function createForm(string $type): void
    {
        try {
            AdminGuard::requireAdmin(true);

            $definition = $this->pageEditor->findCreatablePageType($type, $this->pageBuilders, self::CREATABLE_PAGE_TYPES);
            if ($definition === null) {
                throw new \RuntimeException('Unsupported page type.');
            }

            $builder = $this->pageBuilders->resolveForPageType($definition['type']);
            if ($builder instanceof GenericPageBuilder) {
                throw new \RuntimeException('No schema builder is registered for this page type.');
            }

            $old = Flash::getOld();
            $oldContent = is_array($old['content'] ?? null) ? $old['content'] : [];
            $selectedArtistId = (int)($old['selected_artist_id'] ?? 0);
            $selectedArtistName = $this->resolveSelectedArtistName($selectedArtistId);
            $content = $this->pageEditor->buildCreateContent($builder, $definition['type'], $oldContent, $selectedArtistName);

            $pageType = $definition['type'];
            $pageTypeLabel = $definition['label'];
            $pageTitle = trim((string)($old['page_title'] ?? ($selectedArtistName ?: $definition['suggestedTitle'])));
            $editorSchema = $builder->editorSchema();
            $artistOptions = $this->pageEditor->isJazzDetailPageType($pageType) ? $this->artists->allArtists() : [];
            $errors = Flash::getErrors();
            $flashSuccess = Flash::getSuccess();
            $csrfToken = Csrf::token();

            require __DIR__ . '/../../Views/cms/create.php';
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            header('Location: /cms/page/create', true, 302);
            exit;
        }
    }

    public function create(string $type): void
    {
        try {
            AdminGuard::requireAdmin(true);
            Csrf::assertPost();

            $definition = $this->pageEditor->findCreatablePageType($type, $this->pageBuilders, self::CREATABLE_PAGE_TYPES);
            if ($definition === null) {
                throw new \RuntimeException('Unsupported page type.');
            }

            $builder = $this->pageBuilders->resolveForPageType($definition['type']);
            if ($builder instanceof GenericPageBuilder) {
                throw new \RuntimeException('No schema builder is registered for this page type.');
            }

            $pageTitle = trim((string)($_POST['page_title'] ?? ''));
            if ($pageTitle === '') {
                throw new \RuntimeException('Page title is required.');
            }

            $selectedArtistId = (int)($_POST['selected_artist_id'] ?? 0);
            $selectedArtistName = $this->resolveSelectedArtistName($selectedArtistId);
            if ($this->pageEditor->isJazzDetailPageType($definition['type']) && $selectedArtistName === null) {
                throw new \RuntimeException('Please select an artist.');
            }

            $contentInput = $_POST['content'] ?? [];
            if (!is_array($contentInput)) {
                throw new \RuntimeException('Invalid content payload.');
            }

            $contentInput = $this->pageEditor->applyArtistSelection($definition['type'], $contentInput, $selectedArtistName);

            $normalized = $this->pageEditor->normalizeSchemaInput($builder, $definition['type'], $contentInput, $_FILES, $this->uploads);

            $newPageId = $this->pageEditor->createPage($pageTitle, $definition['type'], $normalized);

            if ($this->pageEditor->isJazzDetailPageType($definition['type']) && $selectedArtistId > 0) {
                $this->artists->assignPageToArtist($selectedArtistId, $newPageId);
                $this->jazzEvents->assignPageToArtistEvents($selectedArtistId, $newPageId);
            }

            Flash::setSuccess('Page created successfully.');
            header('Location: /cms/page/' . $newPageId, true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            Flash::setOld([
                'page_title' => (string)($_POST['page_title'] ?? ''),
                'selected_artist_id' => (string)($_POST['selected_artist_id'] ?? ''),
                'content' => is_array($_POST['content'] ?? null) ? $_POST['content'] : [],
            ]);
            header('Location: /cms/page/create/' . urlencode($type), true, 302);
            exit;
        }
    }

    // --- existing page edit/update ----

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $page = $this->pageEditor->findPageById($id);
        if ($page === null) {
            http_response_code(404);
            echo 'Page not found.';
            return;
        }

        $pageType = (string)($page['Page_Type'] ?? '');
        $builder = $this->pageBuilders->resolveForPageType($pageType);
        $content = $this->pageEditor->getPageContentById($id);
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

        $page = $this->pageEditor->findPageById($id);
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
                $normalized = $this->pageEditor->normalizeSchemaInput($builder, $pageType, $contentInput, $_FILES, $this->uploads);
            }

            $this->pageEditor->savePageContentById($id, $normalized);

            Flash::setSuccess('Page content updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/page/' . $id, true, 302);
        exit;
    }

    public function delete(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $deleted = $this->pageEditor->deletePageById($id);
            if (!$deleted) {
                Flash::setErrors(['general' => 'Page not found or could not be deleted.']);
            } else {
                Flash::setSuccess('Page deleted successfully.');
            }
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/pages', true, 302);
        exit;
    }

    private function resolveSelectedArtistName(int $artistId): ?string
    {
        if ($artistId <= 0) {
            return null;
        }

        $artist = $this->artists->findArtist($artistId);
        if ($artist === null) {
            return null;
        }

        $name = trim((string)$artist->name);
        return $name !== '' ? $name : null;
    }
}
