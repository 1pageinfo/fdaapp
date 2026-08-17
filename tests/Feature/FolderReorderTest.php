<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolderReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_reorder_folders()
    {
        $user = User::factory()->create();

        $folder1 = Folder::create(['name' => 'Folder 1', 'sort_order' => 1]);
        $folder2 = Folder::create(['name' => 'Folder 2', 'sort_order' => 2]);

        $response = $this->actingAs($user)->postJson(route('folders.reorder'), [
            'order' => [$folder2->id, $folder1->id]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $this->assertEquals(1, $folder2->fresh()->sort_order);
        $this->assertEquals(2, $folder1->fresh()->sort_order);
    }
}
