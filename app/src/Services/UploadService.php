<?php

declare(strict_types=1);

namespace App\Services;

final class UploadService
{
    /**
     * Store an uploaded image into /public/assets/img/<section>/<folder>/
     * Returns relative path: assets/img/<section>/<folder>/<random>.<ext>
     *
     * If $oldPathToDelete is provided, it will be deleted after successful upload
     * (only if it points inside the same section/folder).
     */
    public function storeImage(
        array $file,
        string $section = 'global',
        string $folder = 'uploads',
        ?string $desiredName = null,              // kept for compatibility, ignored by default now
        bool $failIfExists = false,               // kept for compatibility, ignored by default now
        ?string $oldPathToDelete = null           // repurposed: delete old image after upload
    ): string {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \Exception('Upload error.');
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new \Exception('Upload failed (invalid temp file).');
        }

        $originalName = (string)($file['name'] ?? 'image');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($extension, $allowed, true)) {
            throw new \Exception('Invalid image file type.');
        }

        $section = $this->slug($section) ?: 'global';
        $folder  = $this->slug($folder) ?: 'uploads';

        $targetDir = __DIR__ . '/../../public/assets/img/' . $section . '/' . $folder;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new \Exception('Unable to create upload directory.');
        }

        //  Always generate a unique random name
        $fileName = $this->randomName() . '.' . $extension;
        $targetPath = $targetDir . '/' . $fileName;

        // Extremely unlikely, but just in case
        $tries = 0;
        while (file_exists($targetPath)) {
            $tries++;
            if ($tries > 5) {
                throw new \Exception('Unable to choose a unique filename.');
            }
            $fileName = $this->randomName() . '.' . $extension;
            $targetPath = $targetDir . '/' . $fileName;
        }

        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new \Exception('Failed to move uploaded file.');
        }

        $relative = 'assets/img/' . $section . '/' . $folder . '/' . $fileName;

        // ✅ Delete previous image if provided (only if it is in the same section/folder)
        if ($oldPathToDelete) {
            $oldRel = ltrim($oldPathToDelete, '/');
            $prefix = 'assets/img/' . $section . '/' . $folder . '/';

            if (str_starts_with($oldRel, $prefix)) {
                $oldAbs = $targetDir . '/' . basename($oldRel);

                // Do not delete the new one by mistake
                if ($oldAbs !== $targetPath && file_exists($oldAbs)) {
                    @unlink($oldAbs);
                }
            }
        }

        return $relative;
    }

    public function deleteImage(?string $path, string $section = 'global', string $folder = 'uploads'): void
    {
        if ($path === null || trim($path) === '') {
            return;
        }

        $section = $this->slug($section) ?: 'global';
        $folder = $this->slug($folder) ?: 'uploads';
        $relative = ltrim($path, '/');
        $prefix = 'assets/img/' . $section . '/' . $folder . '/';

        if (!str_starts_with($relative, $prefix)) {
            return;
        }

        $absolute = __DIR__ . '/../../public/' . $relative;
        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }

    private function randomName(): string
    {
        // 16 bytes => 32 hex chars (good enough)
        return bin2hex(random_bytes(16));
    }

    private function slug(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('~[^a-z0-9_-]+~', '-', $s) ?? $s;
        $s = trim($s, '-');
        return $s;
    }
}