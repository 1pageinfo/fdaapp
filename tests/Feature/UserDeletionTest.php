<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Group;
use App\Models\ChatMessage;
use App\Models\Chat;
use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_deleted_and_anonymized_safely()
    {
        // 1. Setup Admin and User
        $admin = User::factory()->create();
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'designation' => 'Worker',
        ]);

        // Assign Role, Permission, Group, and create data
        $role = Role::create(['name' => 'Member', 'slug' => 'member']);
        $user->roles()->attach($role);

        $group = Group::create(['name' => 'Work Group']);
        $user->groups()->attach($group);

        $chat = Chat::create(['group_id' => $group->id, 'name' => 'Main Chat', 'tab' => 'main']);
        
        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'body' => 'Hello team!',
        ]);

        $file = File::create([
            'name' => 'document.pdf',
            'path' => 'files/doc.pdf',
            'uploaded_by' => $user->id,
        ]);

        // Verify pre-conditions
        $this->assertCount(1, $user->roles);
        $this->assertCount(1, $user->groups);

        // 2. Perform deletion request by Admin
        $response = $this->actingAs($admin)->delete(route('admin.user_roles.destroy', $user));

        $response->assertStatus(302); // Redirect back
        
        // 3. Assert user is soft-deleted and anonymized
        $user->refresh();
        
        $this->assertTrue($user->trashed());
        $this->assertEquals('Deleted User', $user->name);
        $this->assertStringContainsString('deleted_' . $user->id . '_', $user->email);
        $this->assertNull($user->phone);
        $this->assertNull($user->address);
        $this->assertNull($user->designation);
        $this->assertNull($user->photo_path);

        // 4. Assert pivot associations detached
        $this->assertCount(0, $user->roles);
        $this->assertCount(0, $user->groups);

        // 5. Assert associated data (messages and files) remains intact
        $this->assertDatabaseHas('chat_messages', [
            'id' => $message->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('files', [
            'id' => $file->id,
            'uploaded_by' => $user->id,
        ]);

        // 6. Assert relations withTrashed work
        $this->assertEquals('Deleted User', $message->fresh()->user->name);
        $this->assertEquals('Deleted User', $file->fresh()->uploader->name);
    }

    public function test_user_cannot_delete_themselves()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.user_roles.destroy', $user));

        $response->assertStatus(302);
        $this->assertFalse($user->fresh()->trashed());
    }
}
