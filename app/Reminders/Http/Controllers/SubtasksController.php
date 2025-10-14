<?php

namespace App\Reminders\Http\Controllers;

use App\Reminders\Models\ReminderItem;
use App\Reminders\Models\ReminderSubtask;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;

class SubtasksController extends Controller
{
    public function index(ReminderItem $item)
    {
        return $item->subtasks()->paginate()->through(fn ($s) => $this->resource($s));
    }

    public function store(Request $request, ReminderItem $item)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
        ]);
        $subtask = $item->subtasks()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_completed' => false,
            'is_active' => true,
        ]);
        return response()->json($this->resource($subtask), 201);
    }

    public function show(ReminderSubtask $subtask)
    {
        return $this->resource($subtask);
    }

    public function update(Request $request, ReminderSubtask $subtask)
    {
        $data = $request->validate([
            'name' => ['sometimes','string','max:255'],
            'description' => ['nullable','string'],
            'is_active' => ['sometimes','boolean'],
        ]);
        $subtask->fill($data)->save();
        return $this->resource($subtask);
    }

    public function destroy(ReminderSubtask $subtask)
    {
        $subtask->delete();
        return response()->json(['status' => 'deleted']);
    }

    public function complete(Request $request, ReminderSubtask $subtask)
    {
        if ($subtask->is_completed) {
            return response()->json(['message' => 'Subtask already completed.'], 422);
        }
        $subtask->update([
            'is_completed' => true,
            'completed_at' => now('UTC'),
        ]);
        return $this->resource($subtask);
    }

    protected function resource(ReminderSubtask $s): array
    {
        return [
            'hash' => $s->hash,
            'item_hash' => optional($s->item)->hash,
            'name' => $s->name,
            'description' => $s->description,
            'is_completed' => (bool) $s->is_completed,
            'completed_at' => optional($s->completed_at)?->toIso8601String(),
            'created_at' => optional($s->created_at)?->toIso8601String(),
            'updated_at' => optional($s->updated_at)?->toIso8601String(),
        ];
    }
}
