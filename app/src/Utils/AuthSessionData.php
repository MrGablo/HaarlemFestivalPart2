<?php

namespace App\Utils;

use App\Config;
use App\Models\UserModel;

final class AuthSessionData
{
    private const SESSION_KEY = 'auth_user';

    public static function store(UserModel $user): void
    {
        $_SESSION[self::SESSION_KEY] = self::encode($user);
    }

    public static function read(): ?array
    {
        $raw = $_SESSION[self::SESSION_KEY] ?? null;

        if (!is_string($raw) || $raw === '') {
            return null;
        }

        return self::decode($raw);
    }

    public static function clear(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    public static function encode(UserModel $user): string
    {
        $payload = [
            'userId' => (int)$user->id,
            'userName' => (string)$user->userName,
            'userRole' => (string)$user->role->value,
            'profilePicturePath' => $user->profilePicturePath ?: Config::DEFAULT_USER_PROFILE_IMAGE_PATH,
        ];

        return json_encode($payload, JSON_THROW_ON_ERROR);
    }

    public static function decode(string $json): ?array
    {
        try {
            $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }

        if (!is_array($payload)) {
            return null;
        }

        if (!isset($payload['userId']) || !is_numeric($payload['userId'])) {
            return null;
        }

        return [
            'userId' => (int)$payload['userId'],
            'userName' => (string)($payload['userName'] ?? ''),
            'userRole' => (string)($payload['userRole'] ?? ''),
            'profilePicturePath' => (string)($payload['profilePicturePath'] ?? Config::DEFAULT_USER_PROFILE_IMAGE_PATH),
        ];
    }
}
