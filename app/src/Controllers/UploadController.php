<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UploadService;
use App\Utils\AdminGuard;

final class UploadController
{
    public function image(): void
    {
        // ✅ no admin panel needed; just a guard
        AdminGuard::requireAdmin();

        try {
            if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
                throw new \Exception('No file uploaded (field name must be "file").');
            }

            $section = (string)($_POST['section'] ?? ($_GET['section'] ?? 'global'));
            $folder  = (string)($_POST['folder'] ?? ($_GET['folder'] ?? 'uploads'));
            $desired = isset($_POST['desiredName']) ? (string)$_POST['desiredName'] : null;

            $service = new UploadService();
            $src = $service->storeImage($_FILES['file'], $section, $folder, $desired);

            header('Content-Type: application/json');
            echo json_encode(['src' => $src], JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}