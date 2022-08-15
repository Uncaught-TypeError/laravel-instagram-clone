<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAnAuthenticatedUserCanViewTheHomepage()
    {
        $this->withoutExceptionHandling();

        $this->withoutMiddleware([
            \App\Http\Middleware\PurchaseStatus::class,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
