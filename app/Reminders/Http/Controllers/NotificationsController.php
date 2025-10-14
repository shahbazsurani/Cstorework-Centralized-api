<?php

namespace App\Reminders\Http\Controllers;

use App\Reminders\Models\ReminderItem;
use App\Reminders\Models\ReminderNotification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotificationsController extends Controller
{
    public function index(ReminderItem $item)
    {
        return $item->notifications()->orderBy('notify_at')->paginate()->through(fn ($n) => $this->resource($n));
    }

    public function store(Request $request, ReminderItem $item)
    {
        $data = $request->validate([
            'channel' => ['required','in:email,sms,webhook'],
            'notify_at' => ['required','date'],
            'payload' => ['nullable','array'],
        ]);

        $notification = $item->notifications()->create([
            'channel' => $data['channel'],
            'notify_at' => $data['notify_at'],
            'status' => 'pending',
            'payload' => $data['payload'] ?? null,
        ]);

        return response()->json($this->resource($notification), 201);
    }

    public function destroy(ReminderNotification $notification)
    {
        $notification->delete();
        return response()->json(['status' => 'deleted']);
    }

    protected function resource(ReminderNotification $n): array
    {
        return [
            'id' => $n->id,
            'item_hash' => optional($n->item)->hash,
            'channel' => $n->channel,
            'notify_at' => optional($n->notify_at)?->toIso8601String(),
            'sent_at' => optional($n->sent_at)?->toIso8601String(),
            'status' => $n->status,
            'payload' => $n->payload,
        ];
    }
}
