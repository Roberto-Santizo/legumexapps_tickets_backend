<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::all();

            return ResponseHandler::success(UserResource::collection($users),'Usuarios obtenidos correctamente',200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(UserRequest $request)
    {
        try {
            $data = $request->validated();

            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            return ResponseHandler::success($user, 'Usuario Creado Correctamente',201);
            } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function show(string $id)
    {
        try {
            $user = $this->findUserOrFail($id);

            return ResponseHandler::success($user, 'Usuarios Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function update(UserRequest $request, string $id)
    {
        try {
            $user = $this->findUserOrFail($id);

            $data = $request->validated();

            $data['password'] = Hash::make($data['password']);

            $user->update($data);

            return ResponseHandler::success($user, 'Usuario Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    private function findUserOrFail(string $id): User
    {
        $user = User::find($id);

        if (! $user) {
            throw new NotFoundError('Usuario no encontrado');
        }

        return $user;
    }
}
