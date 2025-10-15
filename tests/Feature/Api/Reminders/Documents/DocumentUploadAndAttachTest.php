<?php

namespace Tests\Feature\Api\Reminders\Documents;

use App\Enums\Role;
use App\Reminders\Models\ReminderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ActsAs;
use Tests\TestCase;

class DocumentUploadAndAttachTest extends TestCase
{
    use RefreshDatabase, ActsAs;

    public function test_upload_and_attach_document_to_item(): void
    {
        Storage::fake('local');
        $this->loginAsRole(Role::ReadWrite->value);

        $item = ReminderItem::factory()->create();

        // Upload
        $file = UploadedFile::fake()->create('manual.txt', 12, 'text/plain');
        $upload = $this->postJson('/api/reminders/documents/upload', [
            'file' => $file,
        ])->assertStatus(201)->json();

        $docHash = $upload['hash'];
        $this->assertNotEmpty($docHash);

        // Attach
        $this->postJson("/api/reminders/items/{$item->hash}/documents/{$docHash}")
            ->assertOk();

        $this->assertDatabaseHas('Reminder_ItemDocument', [
            'item_id' => $item->id,
        ]);
    }
}
