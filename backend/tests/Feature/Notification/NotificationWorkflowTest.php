<?php

namespace Tests\Feature\Notification;

use App\Models\AssignedClient;
use App\Models\ClientRequest;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_request_creates_an_admin_notification(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $this->assignFolderToClient($client, $production);

        Sanctum::actingAs($client);

        $this->postJson('/api/requests', [
            'title' => 'New brochure',
            'description' => 'Create a tri-fold brochure for the April campaign.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
        ])->assertCreated();

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->user_id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $client->user_id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $production->user_id,
        ]);
    }

    public function test_assignment_creation_notifies_the_assigned_production_user_only(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $otherProduction = $this->createUser('Other Production', 'other-production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $folder = $this->assignFolderToClient($client, $production);

        ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Client request',
            'description' => 'Request ready for assignment.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ])->assertCreated();

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $production->user_id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $admin->user_id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $otherProduction->user_id,
        ]);
    }

    public function test_due_date_updates_create_a_notification_for_the_target_client_only(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $otherClient = $this->createUser('Other Client', 'other-client@example.com', User::ROLE_CLIENT);
        $folder = $this->assignFolderToClient($client, $production);
        $this->assignFolderToClient($otherClient, $production);

        $clientRequest = ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Due date request',
            'description' => 'Request for due date updates.',
            'request_type' => ClientRequest::TYPE_UPDATE_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/requests/{$clientRequest->request_id}", [
            'due_date' => '2026-05-15 09:00:00',
        ])->assertOk();

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $client->user_id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $admin->user_id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $otherClient->user_id,
        ]);
    }

    public function test_production_status_updates_create_a_notification_for_the_target_client_only(): void
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $otherClient = $this->createUser('Other Client', 'other-client@example.com', User::ROLE_CLIENT);
        $folder = $this->assignFolderToClient($client, $production);
        $this->assignFolderToClient($otherClient, $production);

        $clientRequest = ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Status request',
            'description' => 'Request for production status changes.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        AssignedClient::query()->create([
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_IN_PROGRESS,
        ]);

        Sanctum::actingAs($production);

        $this->patchJson("/api/production/requests/{$clientRequest->request_id}", [
            'status' => ClientRequest::STATUS_IN_PROGRESS,
        ])->assertOk();

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $client->user_id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $production->user_id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $otherClient->user_id,
        ]);
    }

    public function test_authenticated_user_can_list_only_their_own_notifications(): void
    {
        $user = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $otherUser = $this->createUser('Other User', 'other@example.com', User::ROLE_PRODUCTION);

        $ownNotificationId = $this->createNotification($user, 'Own notification');
        $otherNotificationId = $this->createNotification($otherUser, 'Other notification');

        Sanctum::actingAs($user);

        $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data.notifications')
            ->assertJsonPath('data.notifications.0.id', $ownNotificationId)
            ->assertJsonMissing([
                'id' => $otherNotificationId,
            ]);
    }

    public function test_authenticated_user_can_mark_only_their_own_notification_as_read(): void
    {
        $user = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $otherUser = $this->createUser('Other User', 'other@example.com', User::ROLE_PRODUCTION);

        $ownNotificationId = $this->createNotification($user, 'Own notification');
        $otherNotificationId = $this->createNotification($otherUser, 'Other notification');

        Sanctum::actingAs($user);

        $this->patchJson("/api/notifications/{$otherNotificationId}/read")
            ->assertForbidden();

        $this->patchJson("/api/notifications/{$ownNotificationId}/read")
            ->assertOk();

        $this->assertNotNull(DB::table('notifications')->where('id', $ownNotificationId)->value('read_at'));
        $this->assertNull(DB::table('notifications')->where('id', $otherNotificationId)->value('read_at'));
    }

    public function test_authenticated_user_can_mark_all_notifications_as_read(): void
    {
        $user = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $otherUser = $this->createUser('Other User', 'other@example.com', User::ROLE_PRODUCTION);

        $firstNotificationId = $this->createNotification($user, 'First notification');
        $secondNotificationId = $this->createNotification($user, 'Second notification');
        $otherNotificationId = $this->createNotification($otherUser, 'Other notification');

        Sanctum::actingAs($user);

        $this->patchJson('/api/notifications/read-all')
            ->assertOk();

        $this->assertNotNull(DB::table('notifications')->where('id', $firstNotificationId)->value('read_at'));
        $this->assertNotNull(DB::table('notifications')->where('id', $secondNotificationId)->value('read_at'));
        $this->assertNull(DB::table('notifications')->where('id', $otherNotificationId)->value('read_at'));
    }

    public function test_broadcast_channel_authorization_rejects_other_users_from_subscribing_to_another_users_private_channel(): void
    {
        $targetUser = $this->createUser('Target User', 'target@example.com', User::ROLE_CLIENT);
        $otherUser = $this->createUser('Other User', 'other@example.com', User::ROLE_PRODUCTION);

        Sanctum::actingAs($otherUser);

        $this->postJson('/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-App.Models.User.'.$targetUser->user_id,
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

    private function assignFolderToClient(User $client, User $creator): Folder
    {
        $folder = Folder::query()->create([
            'folder_name' => $client->name.' Folder',
            'client_id' => $client->user_id,
            'created_by' => $creator->user_id,
        ]);

        $client->forceFill([
            'assigned_folder_id' => $folder->folder_id,
        ])->save();

        return $folder;
    }

    private function createNotification(User $user, string $message): string
    {
        $id = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'test.notification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->user_id,
            'data' => json_encode([
                'message' => $message,
            ], JSON_THROW_ON_ERROR),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}
