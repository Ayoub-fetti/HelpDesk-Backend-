<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'lastName' => ['required', 'string', 'max:255'],
            'firstName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Check if this is the first user
        $isFirstUser = User::count() === 0;

        $userType = $isFirstUser ? 'administrator' : 'final_user';
        $role = $isFirstUser ? 'administrator' : 'final_user';

        $user = User::create([
            'lastName' => $request->lastName,
            'firstName' => $request->firstName,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            'user_type' => $userType,
        ]);

        $user->assignRole($role);

        event(new Registered($user));

        Auth::login($user);

        return response("Register successfully", 201)
            ->header('Content-Type', 'application/json')
            ->setContent(json_encode([
                'message' => 'Register successfully',
                'user' => $user,
            ]));
    }
}
