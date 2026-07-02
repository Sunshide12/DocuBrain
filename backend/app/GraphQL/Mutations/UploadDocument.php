<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class UploadDocument
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke(null $_, array $args): Document
    {
        /** @var UploadedFile $file */
        $file = $args['file'];

        $path = $file->store('documents');

        /** @var \App\Models\User $user */
        $user = auth('sanctum')->user();

        return Document::create([
            'user_id' => $user->id,
            'title' => $args['title'] ?? $file->getClientOriginalName(),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'status' => 'pending',
        ]);
    }
}
