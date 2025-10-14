<?php

namespace App\Reminders\Http\Controllers;

use App\Reminders\Models\ReminderAccessGrant;
use App\Reminders\Models\ReminderItem;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SharingController extends Controller
{
    public function index(Request $request)
    {
        $userId = optional($request->user())->id;
        $q = ReminderAccessGrant::query()->with(['item','location','grantor','grantee'])
            ->where(function ($q) use ($userId) {
                $q->where('grantor_user_id', $userId)->orWhere('grantee_user_id', $userId);
            })
            ->orderByDesc('id');

        return $q->paginate()->through(fn ($g) => $this->resource($g));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_hash' => ['nullable','string'],
            'location_id' => ['nullable','integer','exists:locations,id'],
            'permission' => ['required','in:view,comment,complete,manage'],
            'expires_at' => ['nullable','date'],
            'grantee_user_id' => ['required','integer','exists:users,id'],
        ]);

        if (empty($data['item_hash']) && empty($data['location_id'])) {
            return response()->json(['message' => 'Provide either item_hash or location_id'], 422);
        }
        if (! empty($data['item_hash']) && ! empty($data['location_id'])) {
            return response()->json(['message' => 'Provide only one of item_hash or location_id'], 422);
        }

        $itemId = null;
        if (!empty($data['item_hash'])) {
            $item = ReminderItem::where('hash', $data['item_hash'])->first();
            if (! $item) return response()->json(['message' => 'Item not found'], 404);
            $itemId = $item->id;
        }

        $grant = ReminderAccessGrant::create([
            'grantor_user_id' => optional($request->user())->id,
            'grantee_user_id' => $data['grantee_user_id'],
            'item_id' => $itemId,
            'location_id' => $data['location_id'] ?? null,
            'permission' => $data['permission'],
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => true,
        ]);

        return response()->json($this->resource($grant->load(['item','location','grantor','grantee'])), 201);
    }

    public function destroy(ReminderAccessGrant $share)
    {
        $share->delete();
        return response()->json(['status' => 'deleted']);
    }

    protected function resource(ReminderAccessGrant $g): array
    {
        return [
            'hash' => $g->hash,
            'grantor_user_id' => $g->grantor_user_id,
            'grantee_user_id' => $g->grantee_user_id,
            'item_hash' => optional($g->item)->hash,
            'location_id' => $g->location_id,
            'permission' => $g->permission,
            'expires_at' => optional($g->expires_at)?->toIso8601String(),
            'created_at' => optional($g->created_at)?->toIso8601String(),
            'updated_at' => optional($g->updated_at)?->toIso8601String(),
        ];
    }
}
