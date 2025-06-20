<?php

namespace App\Infrastructure\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Create a new user
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'user_type' => 'required|string',
        ]);

        $user = User::create([
            'firstName' => $validated['firstName'],
            'lastName' => $validated['lastName'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => $validated['user_type'],
        ]);

        return response()->json(['user' => $user], 201);
    }

    // Update an existing user
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'firstName' => 'sometimes|string|max:255',
            'lastName' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'user_type' => 'sometimes|string',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json(['user' => $user]);
    }

    // Delete a user
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
    public function getAllPermissions(): JsonResponse
    {
        $permissions = Permission::all()->pluck('name');
        return response()->json(['permissions' => $permissions]);
    }

    // Assign roles and permissions to a user
    public function assignRolesPermissions(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'boolean',
        ]);

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }
        if (isset($validated['permissions'])) {
            $permissionsToAssign = [];
            foreach ($validated['permissions'] as $permission => $value) {
                if ($value) {
                    $permissionsToAssign[] = $permission;
                }
            }
            $user->syncPermissions($permissionsToAssign);
        }

        return response()->json([
            'user' => $user->load('roles', 'permissions'),
            'message' => 'Roles and permissions updated'
        ]);
    }
}

