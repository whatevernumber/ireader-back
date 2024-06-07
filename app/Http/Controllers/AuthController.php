<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthController extends Controller
{

    /**
     * @throws AccessDeniedHttpException
     * @throws ValidationException
     */
    public function login(LoginRequest $request): UserResource
    {
        if ($request->bearerToken() && Auth::guard('sanctum')->user()) {
            throw new AccessDeniedHttpException('Пользователь уже авторизирован', null, 403);
        }

        $data = $request->validated();
        $user = User::where(['email' => $data['email']])->first();

        if (!Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['password' => 'Неверный пароль']);
        }

        if ($user->is_admin) {
            $token = $user->createToken('admintoken', ['update']);
        } else {
            $token = $user->createToken('usertoken', ['see']);
        }

        $user->token = $token->plainTextToken;

        return new UserResource($user);
    }

    public function logout(Request $request): Response
    {

        $request->user()->tokens()->delete();

        return response('', 200);
    }
}
