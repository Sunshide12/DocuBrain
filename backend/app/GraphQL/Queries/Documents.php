<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;

final class Documents
{
    /**
     * Return a query builder for the documents belonging to the authenticated user.
     *
     * @param  Builder  $builder
     * @param  mixed  $value
     * @param  mixed  $root
     * @param  array{}  $args
     * @return Builder
     */
    public function __invoke(Builder $builder, mixed $value, mixed $root, array $args): Builder
    {
        return $builder->where('user_id', auth('sanctum')->id());
    }
}
