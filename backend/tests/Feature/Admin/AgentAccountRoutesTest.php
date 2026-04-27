<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AgentAccountRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_agent_account(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/agents', [
            'name' => 'Agent User',
            'email' => 'agent@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Agent account created.')
            ->assertJsonPath('data.agent.name', 'Agent User')
            ->assertJsonPath('data.agent.email', 'agent@example.com')
            ->assertJsonPath('data.agent.role', User::ROLE_AGENT);

        $this->assertDatabaseHas('users', [
            'name' => 'Agent User',
            'email' => 'agent@example.com',
            'role' => User::ROLE_AGENT,
        ]);
    }

    public function test_non_admin_users_cannot_create_agent_accounts(): void
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $agent = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        foreach ([$production, $agent, $client] as $user) {
            Sanctum::actingAs($user);

            $this->postJson('/api/admin/agents', [
                'name' => 'Created Agent',
                'email' => 'created-agent-'.$user->role.'@example.com',
                'password' => 'password123',
            ])->assertForbidden();
        }

        $this->assertDatabaseMissing('users', [
            'name' => 'Created Agent',
            'role' => User::ROLE_AGENT,
        ]);
    }

    private function createUser(string $name, string $email, string $role): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password123',
            'role' => $role,
        ]);
    }
}
