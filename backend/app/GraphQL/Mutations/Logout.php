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
    public function __invoke(null $_, array $args, array $context): array
    {
        /** @var Request $request */
        $request = $context['request'];

        // Revoke the token that was used to authenticate this request
        $request->user()->currentAccessToken()->delete();

        return [
            'message' => 'Logged out successfully.',
        ];
    }
}
