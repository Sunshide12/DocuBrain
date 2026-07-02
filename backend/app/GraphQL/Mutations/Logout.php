<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Illuminate\Http\Request;

final class Logout
{
    /**
     * Handle the logout mutation.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     * @return array{message: string}
     */
    public function __invoke(null $_, array $args, $context): array
    {
        /** @var \Illuminate\Http\Request $request */
        $request = $context->request();

        // Revoke the token that was used to authenticate this request
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return [
            'message' => 'Logged out successfully.',
        ];
    }
}
