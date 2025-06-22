<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users', function () {
        return response()->json([
            'users' => \App\Models\User::with('roles')
                ->select('id', 'firstName', 'lastName', 'email', 'user_type', 'departement', 'active', 'last_connection', 'specialization')
                ->get()
        ]);
    });
});

Route::middleware('auth:sanctum')->get('/test-permission', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'user' => $user->only(['id', 'firstName', 'lastName', 'email', 'user_type']),
        'roles' => $user->getRoleNames(),
        'permissions' => $user->getAllPermissions()->pluck('name'),
        'can_edit_tickets' => $user->can('edit tickets'),
        'can_assign_tickets' => $user->can('assign tickets'),
    ]);

});
require __DIR__.'/auth.php';