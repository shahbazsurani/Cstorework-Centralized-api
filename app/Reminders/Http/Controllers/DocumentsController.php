<?php

namespace App\Reminders\Http\Controllers;

use App\Reminders\Models\ReminderDocument;
use App\Reminders\Models\ReminderItem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class DocumentsController extends Controller
{
    public function upload(Request $request)
    {
        $data = $request->validate([
            'file' => ['required','file'],
        ]);

        $file = $request->file('file');
        $disk = config('filesystems.default', 'local');
        $path = $file->store('reminders/documents', $disk);

        $doc = ReminderDocument::create([
            'storage_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by' => optional($request->user())->id,
            'is_active' => true,
        ]);

        return response()->json($this->resource($doc), 201);
    }

    public function attach(ReminderItem $item, ReminderDocument $document)
    {
        $item->documents()->syncWithoutDetaching([$document->id]);
        return response()->json(['status' => 'attached']);
    }

    public function detach(ReminderItem $item, ReminderDocument $document)
    {
        $item->documents()->detach($document->id);
        return response()->json(['status' => 'detached']);
    }

    protected function resource(ReminderDocument $d): array
    {
        return [
            'hash' => $d->hash,
            'storage_path' => $d->storage_path,
            'original_name' => $d->original_name,
            'mime_type' => $d->mime_type,
            'size_bytes' => $d->size_bytes,
            'uploaded_by' => $d->uploaded_by,
            'created_at' => optional($d->created_at)?->toIso8601String(),
            'updated_at' => optional($d->updated_at)?->toIso8601String(),
        ];
    }
}
