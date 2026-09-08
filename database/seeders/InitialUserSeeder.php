<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Crea el usuario administrador inicial para poder hacer login en un
     * despliegue recien levantado. Es idempotente: si el usuario ya existe no
     * se toca, para no pisar una contrasena que ya haya cambiado el equipo.
     */
    public function run(): void
    {
        $email = config('app.initial_admin.email');

        if (User::where('email', $email)->exists()) {
            $this->command?->info("Usuario inicial '{$email}' ya existe, no se modifica.");

            return;
        }

        $password = config('app.initial_admin.password');

        User::create([
            'name' => config('app.initial_admin.name'),
            'email' => $email,
            'role' => 'admin',
            'password' => Hash::make($password),
        ]);

        $this->command?->warn("Usuario inicial '{$email}' creado. Cambia su contrasena cuanto antes.");
    }
}
