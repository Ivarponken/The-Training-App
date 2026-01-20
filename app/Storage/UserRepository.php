<?php

namespace App\Storage;

use App\Models\User;
use DateTimeInterface;

interface UserRepository
{
    public function add(User $user);

    public function getUserbyEmail(string $email): ?User;

    public function saveRefreshToken(string $user_id, string $refreshToken, string $expiresAt): void;

    public function getUserByRefreshToken(string $refreshToken): ?User;

    public function deleteRefreshToken(string $refreshToken);

    public function deleteAllRefreshTokens(User $user);
}
