<?php

namespace App\Controllers;

use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\PageRepository;
use App\Utils\AuthSessionData;
use App\Utils\Flash;
use App\Utils\Session;
use App\Utils\Wysiwyg;

class CMSController
{
    private IPageRepository $pages;

    public function __construct()
    {
        $this->pages = new PageRepository();
        Session::ensureStarted();
    }

    public function index(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $pages = $this->pages->getAllPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../Views/cms/index.php';
    }

    public function edit(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

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
        if (!$this->requireAdmin()) {
            return;
        }

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

            $normalized = $this->normalizeContent($contentInput, is_array($typeMap) ? $typeMap : []);
            $this->pages->savePageContentById($id, $normalized);

            Flash::setSuccess('Page content updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/page/' . $id, true, 302);
        exit;
    }

    private function requireAdmin(): bool
    {
        $auth = AuthSessionData::read();
        if ($auth === null || empty($auth['userId'])) {
            header('Location: /login', true, 302);
            exit;
        }

        $role = strtolower((string)($auth['userRole'] ?? ''));
        if ($role !== 'admin') {
            http_response_code(403);
            echo 'Forbidden';
            return false;
        }

        return true;
    }

    private function normalizeContent(mixed $value, mixed $typeMeta): mixed
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
