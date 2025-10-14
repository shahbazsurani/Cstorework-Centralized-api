<?php

namespace App\Reminders\Http\Controllers;

use App\Reminders\Models\ReminderItem;
use App\Reminders\Models\ReminderItemRecurrence;
use App\Reminders\Models\ReminderRecurringType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RecurrencesController extends Controller
{
    public function index(ReminderItem $item)
    {
        return $item->recurrences()->with('type')->paginate()->through(fn ($r) => $this->resource($r));
    }

    public function store(Request $request, ReminderItem $item)
    {
        $data = $request->validate([
            'recurring_type' => ['required','string'],
            'interval_value' => ['required','integer','min:1'],
            'anchor_date' => ['required','date'],
            'end_date' => ['nullable','date'],
            'timezone' => ['nullable','string'],
            'is_active' => ['sometimes','boolean'],
        ]);

        $type = ReminderRecurringType::firstOrCreate(['type' => $data['recurring_type']], ['is_active' => true]);

        $rec = ReminderItemRecurrence::create([
            'item_id' => $item->id,
            'recurring_type_id' => $type->id,
            'interval_value' => $data['interval_value'],
            'anchor_date' => $data['anchor_date'],
            'end_date' => $data['end_date'] ?? null,
            'timezone' => $data['timezone'] ?? 'UTC',
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json($this->resource($rec->load('type')), 201);
    }

    public function update(Request $request, ReminderItemRecurrence $recurrence)
    {
        $data = $request->validate([
            'recurring_type' => ['sometimes','string'],
            'interval_value' => ['sometimes','integer','min:1'],
            'anchor_date' => ['sometimes','date'],
            'end_date' => ['nullable','date'],
            'timezone' => ['nullable','string'],
            'is_active' => ['sometimes','boolean'],
        ]);

        if (isset($data['recurring_type'])) {
            $type = ReminderRecurringType::firstOrCreate(['type' => $data['recurring_type']], ['is_active' => true]);
            $recurrence->recurring_type_id = $type->id;
        }

        $recurrence->fill(collect($data)->except('recurring_type')->all())->save();

        return $this->resource($recurrence->load('type'));
    }

    public function destroy(ReminderItemRecurrence $recurrence)
    {
        $recurrence->delete();
        return response()->json(['status' => 'deleted']);
    }

    protected function resource(ReminderItemRecurrence $r): array
    {
        return [
            'id' => $r->id,
            'item_hash' => optional($r->item)->hash,
            'recurring_type' => optional($r->type)->type,
            'interval_value' => (int) $r->interval_value,
            'anchor_date' => optional($r->anchor_date)?->toDateString(),
            'end_date' => optional($r->end_date)?->toDateString(),
            'timezone' => $r->timezone,
            'is_active' => (bool) $r->is_active,
        ];
    }
}
