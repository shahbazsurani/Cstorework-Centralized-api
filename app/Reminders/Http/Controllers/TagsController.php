<?php

namespace App\Reminders\Http\Controllers;

use App\Reminders\Models\ReminderTag;
use App\Reminders\Models\ReminderItem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TagsController extends Controller
{
    public function index()
    {
        return ReminderTag::query()->orderBy('name')->paginate()->through(fn ($t) => $this->resource($t));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'parent_hash' => ['nullable','string'],
            'is_active' => ['sometimes','boolean'],
        ]);

        $parentId = null;
        if (!empty($data['parent_hash'])) {
            $parent = ReminderTag::where('hash', $data['parent_hash'])->first();
            $parentId = $parent?->id;
        }

        $tag = ReminderTag::create([
            'name' => $data['name'],
            'parent_id' => $parentId,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json($this->resource($tag), 201);
    }

    public function show(ReminderTag $tag)
    {
        return $this->resource($tag->load('children'));
    }

    public function update(Request $request, ReminderTag $tag)
    {
        $data = $request->validate([
            'name' => ['sometimes','string','max:255'],
            'parent_hash' => ['nullable','string'],
            'is_active' => ['sometimes','boolean'],
        ]);

        if (array_key_exists('parent_hash', $data)) {
            $tag->parent_id = null;
            if (! empty($data['parent_hash'])) {
                $parent = ReminderTag::where('hash', $data['parent_hash'])->first();
                $tag->parent_id = $parent?->id;
            }
        }

        if (isset($data['name'])) $tag->name = $data['name'];
        if (isset($data['is_active'])) $tag->is_active = (bool) $data['is_active'];
        $tag->save();

        return $this->resource($tag);
    }

    public function destroy(ReminderTag $tag)
    {
        $tag->delete();
        return response()->json(['status' => 'deleted']);
    }

    public function attach(ReminderItem $item, ReminderTag $tag)
    {
        $item->tags()->syncWithoutDetaching([$tag->id]);
        return response()->json(['status' => 'attached']);
    }

    public function detach(ReminderItem $item, ReminderTag $tag)
    {
        $item->tags()->detach($tag->id);
        return response()->json(['status' => 'detached']);
    }

    protected function resource(ReminderTag $t): array
    {
        return [
            'hash' => $t->hash,
            'name' => $t->name,
            'parent_hash' => optional($t->parent)->hash,
            'is_active' => (bool) $t->is_active,
            'created_at' => optional($t->created_at)?->toIso8601String(),
            'updated_at' => optional($t->updated_at)?->toIso8601String(),
            'children' => $t->relationLoaded('children') ? $t->children->map(fn ($c) => $this->resource($c))->all() : null,
        ];
    }
}
