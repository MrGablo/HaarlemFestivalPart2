<?php

declare(strict_types=1);

namespace App\Cms\Services;

use App\Repositories\EventRepository;
use App\Repositories\StoriesRepository;

final class CmsStoriesEventService
{
    private StoriesRepository $storiesRepository;
    private EventRepository $eventRepository;

    public function __construct(
        ?StoriesRepository $storiesRepository = null,
        ?EventRepository $eventRepository = null
    ) {
        $this->storiesRepository = $storiesRepository ?? new StoriesRepository();
        $this->eventRepository = $eventRepository ?? new EventRepository();
    }

    public function getAllStoriesEvents(): array
    {
        return $this->storiesRepository->getAllStoriesEvents();
    }

    public function getStoriesEventById(int $eventId): ?object
    {
        if ($eventId <= 0) {
            return null;
        }

        return $this->storiesRepository->getStoriesEventById($eventId);
    }

    public function updateStoriesEvent(int $eventId, array $post, array $files = []): void
    {
        $event = $this->getStoriesEventById($eventId);
        if ($event === null) {
            throw new \RuntimeException('Stories event not found.');
        }

        $parentEvent = $this->eventRepository->findEventById($eventId);
        if ($parentEvent === null) {
            throw new \RuntimeException('Parent event not found.');
        }

        $title = trim((string)($post['title'] ?? ''));
        $language = trim((string)($post['language'] ?? ''));
        $ageGroup = trim((string)($post['age_group'] ?? ''));
        $storyType = trim((string)($post['story_type'] ?? ''));
        $location = trim((string)($post['location'] ?? ''));
        $description = trim((string)($post['description'] ?? ''));
        $startDate = trim((string)($post['start_date'] ?? ''));
        $endDate = trim((string)($post['end_date'] ?? ''));
        $priceRaw = trim((string)($post['price'] ?? ''));

        if ($title === '') {
            throw new \RuntimeException('Title is required.');
        }

        if ($language === '') {
            throw new \RuntimeException('Language is required.');
        }

        $allowedLanguages = ['NL', 'ENG', 'NL/ENG'];
        if (!in_array($language, $allowedLanguages, true)) {
            throw new \RuntimeException('Invalid language selected.');
        }

        if ($ageGroup === '') {
            throw new \RuntimeException('Age group is required.');
        }

        if ($storyType === '') {
            throw new \RuntimeException('Story type is required.');
        }

        if ($location === '') {
            throw new \RuntimeException('Location is required.');
        }

        if ($startDate === '') {
            throw new \RuntimeException('Start date is required.');
        }

        if ($endDate === '') {
            throw new \RuntimeException('End date is required.');
        }

        $price = 0.0;
        if ($priceRaw !== '') {
            if (!is_numeric($priceRaw)) {
                throw new \RuntimeException('Price must be a valid number.');
            }

            $price = (float)$priceRaw;

            if ($price < 0) {
                throw new \RuntimeException('Price cannot be negative.');
            }
        }

        $imagePath = null;
        if (isset($files['img_background_file']) && is_array($files['img_background_file'])) {
            $imagePath = $this->handleImageUpload($files['img_background_file']);
        }

        $availability = (int)($parentEvent['availability'] ?? 0);

        $updatedParent = $this->eventRepository->updateEvent($eventId, $title, $availability);
        if (!$updatedParent) {
            $sameTitle = ((string)($parentEvent['title'] ?? '')) === $title;
            if (!$sameTitle) {
                throw new \RuntimeException('Could not update parent event.');
            }
        }

        $updatedStories = $this->storiesRepository->updateStoriesEventCms($eventId, [
            'language' => $language,
            'age_group' => $ageGroup,
            'story_type' => $storyType,
            'location' => $location,
            'description' => $description !== '' ? $description : null,
            'start_date' => $this->normalizeDatetimeForDatabase($startDate),
            'end_date' => $this->normalizeDatetimeForDatabase($endDate),
            'price' => $price,
            'img_background' => $imagePath,
        ]);

        if (!$updatedStories) {
            throw new \RuntimeException('Could not update stories event.');
        }
    }

    public function deleteStoriesEvent(int $eventId): bool
    {
        $event = $this->getStoriesEventById($eventId);

        if ($event === null) {
            return false;
        }

        return $this->storiesRepository->deleteStoriesEventById($eventId);
    }

    private function normalizeDatetimeForDatabase(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
            return str_replace('T', ' ', $value) . ':00';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
            return $value;
        }

        try {
            $dt = new \DateTime($value);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            throw new \RuntimeException('Invalid date/time format.');
        }
    }

    private function handleImageUpload(array $file): ?string
    {
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Image upload failed.');
        }

        $originalName = (string)($file['name'] ?? '');
        $tmpName = (string)($file['tmp_name'] ?? '');

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new \RuntimeException('Invalid uploaded file.');
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \RuntimeException('Invalid image type. Allowed: jpg, jpeg, png, webp, gif.');
        }

        $uploadDir = __DIR__ . '/../../../public/uploads/stories/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new \RuntimeException('Could not create upload directory.');
        }

        $filename = uniqid('story_', true) . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new \RuntimeException('Could not save uploaded image.');
        }

        return '/uploads/stories/' . $filename;
    }
}