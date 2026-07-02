<?php

namespace Tests\Feature\GraphQL;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    public function test_user_can_upload_document(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;
        $this->withHeaders([
            'Authorization' => "Bearer $token",
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $operations = [
            'query' => 'mutation UploadDocument($file: Upload!) { uploadDocument(file: $file, title: "My Test Document") { id title original_name mime_type size status file_path } }',
            'variables' => [
                'file' => null,
            ],
        ];

        $map = [
            '0' => ['variables.file'],
        ];

        $files = [
            '0' => $file,
        ];

        $response = $this->multipartGraphQL($operations, $map, $files);

        $response->assertJsonStructure([
            'data' => [
                'uploadDocument' => [
                    'id',
                    'title',
                    'original_name',
                    'mime_type',
                    'size',
                    'status',
                    'file_path',
                ],
            ],
        ]);

        $document = Document::first();
        $this->assertNotNull($document);
        $this->assertEquals('My Test Document', $document->title);
        $this->assertEquals('document.pdf', $document->original_name);
        Storage::assertExists($document->file_path);
    }

    public function test_user_can_query_documents(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Document::factory(3)->create(['user_id' => $user1->id]);
        Document::factory(2)->create(['user_id' => $user2->id]);

        $token = $user1->createToken('test-token')->plainTextToken;
        $this->withHeaders([
            'Authorization' => "Bearer $token",
        ]);

        $response = $this->graphQL(
            /** @lang GraphQL */
            '
            query {
                documents {
                    data {
                        id
                        title
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        '
        );

        $response->assertJsonPath('data.documents.paginatorInfo.total', 3);
    }
}
