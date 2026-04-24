<?php

namespace Tests\Feature\Chat;

use App\Events\AssignmentChatMessageCreated;
use App\Models\AssignedClient;
use App\Models\AssignmentChatThread;
use App\Models\ClientRequest;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssignmentChatRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_creation_creates_an_active_chat_thread_visible_to_client_and_production(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $this->createClientRequest($client, $production);

        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ])->assertCreated();

        $thread = AssignmentChatThread::query()->firstOrFail();

        $this->assertSame(AssignmentChatThread::STATUS_ACTIVE, $thread->status);
        $this->assertSame($client->user_id, $thread->client_id);
        $this->assertSame($production->user_id, $thread->production_id);

        Sanctum::actingAs($client);
        $this->getJson('/api/chat/thread')
            ->assertOk()
            ->assertJsonPath('data.thread.thread_id', $thread->thread_id)
            ->assertJsonPath('data.thread.status', AssignmentChatThread::STATUS_ACTIVE);

        Sanctum::actingAs($production);
        $this->getJson('/api/chat/threads')
            ->assertOk()
            ->assertJsonCount(1, 'data.threads')
            ->assertJsonPath('data.threads.0.thread_id', $thread->thread_id);
    }

    public function test_client_and_production_can_exchange_messages_and_mark_threads_as_read(): void
    {
        Event::fake([AssignmentChatMessageCreated::class]);

        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $this->createClientRequest($client, $production);

        Sanctum::actingAs($admin);
        $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_IN_PROGRESS,
        ])->assertCreated();

        $thread = AssignmentChatThread::query()->firstOrFail();

        Sanctum::actingAs($client);
        $this->postJson("/api/chat/threads/{$thread->thread_id}/messages", [
            'body' => 'Can you share the first draft today?',
        ])->assertCreated()
            ->assertJsonPath('data.message.thread_id', $thread->thread_id);

        Event::assertDispatched(AssignmentChatMessageCreated::class);

        Sanctum::actingAs($production);
        $this->getJson("/api/chat/threads/{$thread->thread_id}")
            ->assertOk()
            ->assertJsonPath('data.thread.unread_count', 1)
            ->assertJsonPath('data.thread.messages.0.body', 'Can you share the first draft today?');

        $this->postJson("/api/chat/threads/{$thread->thread_id}/read")
            ->assertOk()
            ->assertJsonPath('data.thread.unread_count', 0);
    }

    public function test_admin_and_agent_cannot_access_chat_threads(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $agent = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);

        Sanctum::actingAs($admin);
        $this->getJson('/api/chat/threads')->assertForbidden();

        Sanctum::actingAs($agent);
        $this->getJson('/api/chat/threads')->assertForbidden();
    }

    public function test_archived_threads_are_read_only_after_assignment_is_marked_done(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $this->createClientRequest($client, $production);

        Sanctum::actingAs($admin);
        $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ])->assertCreated();

        $thread = AssignmentChatThread::query()->firstOrFail();

        $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_DONE,
        ])->assertOk();

        $thread->refresh();

        $this->assertSame(AssignmentChatThread::STATUS_ARCHIVED, $thread->status);
        $this->assertNotNull($thread->closed_at);

        Sanctum::actingAs($client);
        $this->postJson("/api/chat/threads/{$thread->thread_id}/messages", [
            'body' => 'Following up after close.',
        ])->assertStatus(422);
    }

    public function test_reopening_the_same_client_and_production_pair_creates_a_new_thread(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $this->createClientRequest($client, $production);

        Sanctum::actingAs($admin);
        $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ])->assertCreated();

        $firstThread = AssignmentChatThread::query()->firstOrFail();

        $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_DONE,
        ])->assertOk();

        $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ])->assertOk();

        $threads = AssignmentChatThread::query()->orderBy('started_at')->get();

        $this->assertCount(2, $threads);
        $this->assertSame(AssignmentChatThread::STATUS_ARCHIVED, $threads[0]->status);
        $this->assertSame(AssignmentChatThread::STATUS_ACTIVE, $threads[1]->status);
        $this->assertNotSame($firstThread->thread_id, $threads[1]->thread_id);
    }

    public function test_broadcast_channel_authorization_rejects_unrelated_users(): void
    {
        config(['broadcasting.default' => 'reverb']);

        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $otherClient = $this->createUser('Other Client', 'other-client@example.com', User::ROLE_CLIENT);
        $this->createClientRequest($client, $production);

        Sanctum::actingAs($admin);
        $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ])->assertCreated();

        $thread = AssignmentChatThread::query()->firstOrFail();

        Sanctum::actingAs($otherClient);
        $this->postJson('/api/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-assignment-chat.'.$thread->thread_id,
        ])->assertForbidden();
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

    private function createClientRequest(User $client, User $production): void
    {
        $folder = Folder::query()->create([
            'folder_name' => $client->name.' Folder',
            'client_id' => $client->user_id,
            'created_by' => $production->user_id,
        ]);

        $client->forceFill([
            'assigned_folder_id' => $folder->folder_id,
        ])->save();

        ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Chat request seed',
            'description' => 'Seed request for assignment chat tests.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);
    }
}
