<?php

declare(strict_types=1);

namespace App\Services;

final class UploadService
{
    /**
     * Store an uploaded image into /public/assets/img/<section>/<folder>/
     * Returns relative path: assets/img/<section>/<folder>/<file>.<ext>
     *
     * @param array $file The $_FILES['file'] array
     * @param string $section e.g. 'jazz', 'history', 'dance'
     * @param string $folder e.g. 'album', 'detail', 'events', 'uploads'
     * @param string|null $desiredName Form-controlled base name (without extension)
     */
    public function storeImage(
        array $file,
        string $section = 'global',
        string $folder = 'uploads',
        ?string $desiredName = null
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

        // Decide filename
        $base = $this->slug((string)$desiredName);
        if ($base === '') {
            // fallback: original filename without extension
            $base = $this->slug(pathinfo($originalName, PATHINFO_FILENAME));
        }
        if ($base === '') {
            // final fallback
            $base = 'image';
        }

        // Ensure unique filename (image.webp, image-2.webp, image-3.webp...)
        $fileName = $this->uniqueFileName($targetDir, $base, $extension);

        $targetPath = $targetDir . '/' . $fileName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new \Exception('Failed to move uploaded file.');
        }

        return 'assets/img/' . $section . '/' . $folder . '/' . $fileName;
    }

    private function uniqueFileName(string $dir, string $base, string $ext): string
    {
        $candidate = $base . '.' . $ext;
        if (!file_exists($dir . '/' . $candidate)) {
            return $candidate;
        }

        $i = 2;
        while (true) {
            $candidate = $base . '-' . $i . '.' . $ext;
            if (!file_exists($dir . '/' . $candidate)) {
                return $candidate;
            }
            $i++;
            if ($i > 9999) {
                // extremely unlikely unless dir is full of collisions
                throw new \Exception('Unable to choose a unique filename.');
            }
        }
    }

    private function slug(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('~[^a-z0-9_-]+~', '-', $s) ?? $s;
        $s = trim($s, '-');
        return $s;
    }
}