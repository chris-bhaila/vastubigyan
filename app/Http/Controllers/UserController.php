<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        return response()->json($this->users->paginate());
    }

    public function show(int $id): JsonResponse
    {
        /** @var User $target */
        $target = $this->users->findOrFail($id);

        Gate::authorize('view', $target);

        return response()->json($target);
    }

    /**
     * Public registration endpoint: intentionally unauthenticated.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $validated['password'] = bcrypt($validated['password']);

        /** @var User $user */
        $user = $this->users->create($validated);

        Mail::to($user)->send(new WelcomeMail($user));

        return response()->json($user, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        /** @var User $target */
        $target = $this->users->findOrFail($id);

        Gate::authorize('update', $target);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,'.$id],
        ]);

        return response()->json($this->users->update($id, $validated));
    }

    public function destroy(int $id): JsonResponse
    {
        /** @var User $target */
        $target = $this->users->findOrFail($id);

        Gate::authorize('delete', $target);

        $this->users->delete($id);

        return response()->json(status: 204);
    }
}
