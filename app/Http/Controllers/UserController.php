<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->users->paginate());
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->users->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $validated['password'] = bcrypt($validated['password']);

        return response()->json($this->users->create($validated), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,'.$id],
        ]);

        return response()->json($this->users->update($id, $validated));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->users->delete($id);

        return response()->json(status: 204);
    }
}
