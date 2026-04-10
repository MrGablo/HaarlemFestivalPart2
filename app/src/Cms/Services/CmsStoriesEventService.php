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

    /**
     * Creates a new event in the parent table and then the details in the stories table.
     */
    public function createStoriesEvent(array $post, array $files = []): int
    {
        // 1. Prepare and validate common fields
        $data = $this->prepareAndValidateData($post);

        // 2. Handle Image Upload
        if (isset($files['img_background_file'])) {
            $data['img_background'] = $this->handleImageUpload($files['img_background_file']);
        }

        // 3. Create Parent Event
        // We assume 'stories' type and 0 initial availability (or adjust as needed)
        $eventId = (int)$this->eventRepository->createEvent($data['title'], 'stories', 0);

        if ($eventId <= 0) {
            throw new \RuntimeException('Could not create parent event.');
        }

        // 4. Create Stories Detail Record
        $data['event_id'] = $eventId;
        $success = $this->storiesRepository->createStoriesEventCms($data);

        if (!$success) {
            throw new \RuntimeException('Could not create stories event details.');
        }

        return $eventId;
    }

    /**
     * Updates existing event and stories details.
     */
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

        // 1. Prepare and validate common fields
        $data = $this->prepareAndValidateData($post);

        // 2. Handle Image Upload (only if a new file is provided)
        $imagePath = $this->handleImageUpload($files['img_background_file'] ?? []);
        if ($imagePath !== null) {
            $data['img_background'] = $imagePath;
        } else {
            // Keep the existing image if no new one is uploaded
            $data['img_background'] = $event->img_background ?? null;
        }

        // 3. Update Parent Event
        $availability = (int)($parentEvent['availability'] ?? 0);
        $updatedParent = $this->eventRepository->updateEvent($eventId, $data['title'], $availability);
        
        if (!$updatedParent) {
            $sameTitle = ((string)($parentEvent['title'] ?? '')) === $data['title'];
            if (!$sameTitle) {
                throw new \RuntimeException('Could not update parent event.');
            }
        }

        // 4. Update Stories Details
        $updatedStories = $this->storiesRepository->updateStoriesEventCms($eventId, $data);

        if (!$updatedStories) {
            throw new \RuntimeException('Could not update stories event details.');
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

    /**
     * Shared validation and data formatting logic to keep code DRY.
     */
    private function prepareAndValidateData(array $post): array
    {
        $title = trim((string)($post['title'] ?? ''));
        $language = trim((string)($post['language'] ?? ''));
        $ageGroup = trim((string)($post['age_group'] ?? ''));
        $storyType = trim((string)($post['story_type'] ?? ''));
        $location = trim((string)($post['location'] ?? ''));
        $description = trim((string)($post['description'] ?? ''));
        $startDate = trim((string)($post['start_date'] ?? ''));
        $endDate = trim((string)($post['end_date'] ?? ''));
        $priceRaw = trim((string)($post['price'] ?? ''));

        // Basic Validations
        if ($title === '') throw new \RuntimeException('Title is required.');
        if ($language === '') throw new \RuntimeException('Language is required.');
        if ($ageGroup === '') throw new \RuntimeException('Age group is required.');
        if ($storyType === '') throw new \RuntimeException('Story type is required.');
        if ($location === '') throw new \RuntimeException('Location is required.');
        if ($startDate === '') throw new \RuntimeException('Start date is required.');
        if ($endDate === '') throw new \RuntimeException('End date is required.');

        $allowedLanguages = ['NL', 'ENG', 'NL/ENG'];
        if (!in_array($language, $allowedLanguages, true)) {
            throw new \RuntimeException('Invalid language selected.');
        }

        $price = 0.0;
        if ($priceRaw !== '') {
            if (!is_numeric($priceRaw)) throw new \RuntimeException('Price must be a valid number.');
            $price = (float)$priceRaw;
            if ($price < 0) throw new \RuntimeException('Price cannot be negative.');
        }

        return [
            'title'          => $title,
            'language'       => $language,
            'age_group'      => $ageGroup,
            'story_type'     => $storyType,
            'location'       => $location,
            'description'    => $description !== '' ? $description : null,
            'start_date'     => $this->normalizeDatetimeForDatabase($startDate),
            'end_date'       => $this->normalizeDatetimeForDatabase($endDate),
            'price'          => $price,
            'img_background' => null, // Default, handled by caller
        ];
    }

    private function normalizeDatetimeForDatabase(string $value): string
    {
        $value = trim($value);
        if ($value === '') return '';

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

        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new \RuntimeException('Invalid uploaded file.');
        }

        $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
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