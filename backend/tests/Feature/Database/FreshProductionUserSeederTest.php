<?php

namespace Tests\Feature\Database;

use App\Models\AssignedClient;
use App\Models\User;
use Database\Seeders\FreshProductionUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreshProductionUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_a_fresh_unassigned_production_user(): void
    {
        $this->seed(FreshProductionUserSeeder::class);

        $user = User::query()
            ->where('email', 'fresh-production@example.com')
            ->where('role', User::ROLE_PRODUCTION)
            ->firstOrFail();

        $this->assertSame('Fresh Production User', $user->name);
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'email' => 'fresh-production@example.com',
            'role' => User::ROLE_PRODUCTION,
        ]);
        $this->assertDatabaseMissing('assigned_clients', [
            'production_id' => $user->user_id,
        ]);
        $this->assertSame(0, AssignedClient::query()->where('production_id', $user->user_id)->count());
    }
}
