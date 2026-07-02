<?php

namespace Tests\Feature\GraphQL;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    /**
     * Test user registration.
     */
    public function test_user_can_register(): void
    {
        $response = $this->graphQL(
            /** @lang GraphQL */
            '
            mutation {
                register(input: {
                    name: "Test User"
                    email: "test@example.com"
                    password: "password123"
                    password_confirmation: "password123"
                }) {
                    token
                    user {
                        name
                        email
                    }
                }
            }
        '
        );

        $response->assertJsonStructure([
            'data' => [
                'register' => [
                    'token',
                    'user' => [
                        'name',
                        'email',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test user login.
     */
    public function test_user_can_login(): void
    {
        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->graphQL(
            /** @lang GraphQL */
            '
            mutation {
                login(input: {
                    email: "existing@example.com"
                    password: "password123"
                }) {
                    token
                    user {
                        name
                        email
                    }
                }
            }
        '
        );

        $response->assertJsonStructure([
            'data' => [
                'login' => [
                    'token',
                    'user' => [
                        'name',
                        'email',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test user me query
     */

    public function test_user_can_me(): void
    {
        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->graphQL(
            /** @lang GraphQL */
            '
            mutation {
                login(input: {
                    email: "existing@example.com"
                    password: "password123"
                }) {
                    token
                    user {
                        name
                        email
                    }
                }
            }
        '
        );

        $token = $response->json('data.login.token');
        $this->withHeaders([
            'Authorization' => "Bearer $token",
        ]);

        $response = $this->graphQL(
            /** @lang GraphQL */
            '
            query {
                me {
                id
                    name
                    email
                    created_at
                }
            }
        '
        );
        $response->assertJsonStructure([
            'data' => [
                'me' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
            ],
        ]);
    }
}



// para probar rutas protegidas dentro de un test se puede usar una funcion helper 
// 1. Haces el login y guardas la respuesta
// $response = $this->graphQL(' mutation { login(...) { token } } ');
// 2. Extraes el token del JSON de respuesta
// $token = $response->json('data.login.token');

// 3. Le dices a tu test que las siguientes peticiones lleven la cabecera de Autorización
//$this->withHeaders([
//    'Authorization' => "Bearer $token",
//]);

// 4. Ahora puedes hacer consultas protegidas y pasarán el guard de Sanctum
//$perfilResponse = $this->graphQL(' query { me { name } } ');
