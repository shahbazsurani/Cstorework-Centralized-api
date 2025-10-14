<?php

namespace App\Reminders\Http\Controllers;

use App\Reminders\Models\ReminderStage;
use App\Reminders\Models\ReminderItem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StagesController extends Controller
{
    public function index()
    {
        return ReminderStage::query()->orderBy('name')->paginate()->through(fn ($s) => $this->resource($s));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'is_active' => ['sometimes','boolean'],
        ]);
        $stage = ReminderStage::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
        return response()->json($this->resource($stage), 201);
    }

    public function show(ReminderStage $stage)
    {
        return $this->resource($stage);
    }

    public function update(Request $request, ReminderStage $stage)
    {
        $data = $request->validate([
            'name' => ['sometimes','string','max:255'],
            'description' => ['nullable','string'],
            'is_active' => ['sometimes','boolean'],
        ]);
        $stage->fill($data)->save();
        return $this->resource($stage);
    }

    public function destroy(ReminderStage $stage)
    {
        $stage->update(['is_active' => false]);
        return response()->json(['status' => 'archived']);
    }

    public function attach(ReminderItem $item, ReminderStage $stage)
    {
        $item->stages()->syncWithoutDetaching([$stage->id]);
        return response()->json(['status' => 'attached']);
    }

    public function detach(ReminderItem $item, ReminderStage $stage)
    {
        $item->stages()->detach($stage->id);
        return response()->json(['status' => 'detached']);
    }

    protected function resource(ReminderStage $s): array
    {
        return [
            'hash' => $s->hash,
            'name' => $s->name,
            'description' => $s->description,
            'is_active' => (bool) $s->is_active,
            'created_at' => optional($s->created_at)?->toIso8601String(),
            'updated_at' => optional($s->updated_at)?->toIso8601String(),
        ];
    }
}
