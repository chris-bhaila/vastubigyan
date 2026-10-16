<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findByMobile(string $mobile): ?User;

    public function findByGoogleId(string $googleId): ?User;

    public function firstOrCreateFromGoogle(array $attributes): User;
}
