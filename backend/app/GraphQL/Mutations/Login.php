<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class Login
{
    /**
     * Handle the login mutation.
     *
     * @param  null  $_
     * @param  array{email: string, password: string}  $args
     * @return array{token: string, user: User}
     *
     * @throws ValidationException
     */
    public function __invoke(null $_, array $args): array
    {
        $user = User::where('email', $args['email'])->first();

        if (! $user || ! Hash::check($args['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke previous tokens to allow only one active session at a time
        // (optional: remove this line to allow multiple concurrent sessions)
        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }
}
