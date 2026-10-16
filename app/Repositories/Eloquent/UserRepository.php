<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->query()->where('email', $email)->first();
    }

    public function findByMobile(string $mobile): ?User
    {
        return $this->model->query()->where('mobile', $mobile)->first();
    }

    public function findByGoogleId(string $googleId): ?User
    {
        return $this->model->query()->where('google_id', $googleId)->first();
    }

    public function firstOrCreateFromGoogle(array $attributes): User
    {
        /** @var User $user */
        $user = $this->model->query()->updateOrCreate(
            ['email' => $attributes['email']],
            [
                'name' => $attributes['name'],
                'google_id' => $attributes['google_id'],
                'avatar' => $attributes['avatar'] ?? null,
                'email_verified_at' => now(),
            ],
        );

        return $user;
    }
}
