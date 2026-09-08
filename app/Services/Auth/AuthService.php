<?php

namespace App\Services\Auth;

use App\Errors\BadRequestError;
use App\Interfaces\Auth\AuthServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService implements AuthServiceInterface
{
    /**
     * @param  array{email: string, password: string}  $data
     */
    public function login(array $data)
    {
        $token = JWTAuth::attempt($data);

        if (! $token) {
            throw new BadRequestError('Credenciales inválidas');
        }

        $user = auth()->user();

        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'token' => $token,
        ];
    }

    /**
     * Crea un usuario nuevo. Sólo lo invoca un administrador autenticado.
     *
     * @param  array{name: string, email: string, password: string, role: string}  $data
     */
    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);
    }
}
