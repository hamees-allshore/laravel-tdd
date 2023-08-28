<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_list(): void
    {
        $response = $this->getJson('/api/users');

        $response
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) =>
                $json->has('users', null, fn(AssertableJson $json) =>
                    $json->has('id')
                        ->has('name')
                        ->has('email')
                        ->missing('password')
                        ->etc()
                )
            );
    }

    public function test_show(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson('/api/users/' . $user->id);

        $response
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($user) {
                return $json->has('user')
                    ->has('user', function (AssertableJson $json) use ($user) {
                        return $json->where('id', $user->id)
                            ->where('name', $user->name)
                            ->where('email', $user->email)
                            ->missing('password')
                            ->etc();
                    });
            });
    }
}
