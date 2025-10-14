<?php

namespace App\Reminders\Http\Controllers;

use App\Reminders\Models\ReminderItem;
use App\Reminders\Models\ReminderItemRecurrence;
use App\Reminders\Models\ReminderRecurringType;
use App\Reminders\Models\ReminderActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ItemsController extends Controller
{
    public function index(Request $request)
    {
        $query = ReminderItem::query();

        // Minimal filters (extendable)
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->integer('location_id'));
        }
        if ($request->filled('status')) {
            if ($request->status === 'open') {
                $query->where('is_completed', false);
            } elseif ($request->status === 'completed') {
                $query->where('is_completed', true);
            } elseif ($request->status === 'overdue') {
                $query->where('is_completed', false)->where('due_at', '<', now('UTC'));
            }
        }

        $sortBy = in_array($request->get('sort_by'), ['due_at','created_at']) ? $request->get('sort_by') : 'due_at';
        $sortDir = $request->get('sort_dir') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate(
            perPage: (int) ($request->get('per_page', 15)),
            pageName: 'page'
        )->through(fn ($item) => $this->resource($item));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'location_id' => ['required','integer','exists:locations,id'],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'due_at' => ['required','date'],
        ]);

        $item = ReminderItem::create([
            'location_id' => $data['location_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'due_at' => Carbon::parse($data['due_at'], 'UTC'),
            'is_completed' => false,
            'is_active' => true,
        ]);

        ReminderActivityLog::create([
            'location_id' => $item->location_id,
            'item_id' => $item->id,
            'actor_user_id' => optional($request->user())->id,
            'action' => 'item.created',
            'meta' => ['item_hash' => $item->hash],
            'created_at' => now('UTC'),
        ]);

        return response()->json($this->resource($item), 201);
    }

    public function show(ReminderItem $item)
    {
        return $this->resource($item->load(['tags','stages','documents']));
    }

    public function update(Request $request, ReminderItem $item)
    {
        $data = $request->validate([
            'name' => ['sometimes','string','max:255'],
            'description' => ['nullable','string'],
            'due_at' => ['sometimes','date'],
            'is_active' => ['sometimes','boolean'],
        ]);

        if (isset($data['due_at'])) {
            $data['due_at'] = Carbon::parse($data['due_at'], 'UTC');
        }

        $item->fill($data)->save();

        ReminderActivityLog::create([
            'location_id' => $item->location_id,
            'item_id' => $item->id,
            'actor_user_id' => optional(request()->user())->id,
            'action' => 'item.updated',
            'meta' => ['item_hash' => $item->hash],
            'created_at' => now('UTC'),
        ]);

        return $this->resource($item);
    }

    public function destroy(ReminderItem $item)
    {
        $item->delete();

        ReminderActivityLog::create([
            'location_id' => $item->location_id,
            'item_id' => $item->id,
            'actor_user_id' => optional(request()->user())->id,
            'action' => 'item.deleted',
            'meta' => ['item_hash' => $item->hash],
            'created_at' => now('UTC'),
        ]);

        return response()->json(['status' => 'deleted']);
    }

    public function complete(Request $request, ReminderItem $item)
    {
        if ($item->is_completed) {
            return response()->json(['message' => 'Item already completed.'], 422);
        }

        $now = now('UTC');

        DB::transaction(function () use ($item, $now, $request, &$spawned) {
            $item->update([
                'is_completed' => true,
                'completed_at' => $now,
            ]);

            ReminderActivityLog::create([
                'location_id' => $item->location_id,
                'item_id' => $item->id,
                'actor_user_id' => optional($request->user())->id,
                'action' => 'item.completed',
                'meta' => ['item_hash' => $item->hash],
                'created_at' => $now,
            ]);

            // Spawn next if recurrence
            $spawned = $this->spawnNextIfRecurring($item);
        });

        $response = $this->resource($item);
        if ($spawned) {
            $response['meta'] = ['previous_item_hash' => $item->hash];
        }
        return $response;
    }

    public function reopen(Request $request, ReminderItem $item)
    {
        if (! $item->is_completed) {
            return response()->json(['message' => 'Item is not completed.'], 422);
        }
        $item->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);

        ReminderActivityLog::create([
            'location_id' => $item->location_id,
            'item_id' => $item->id,
            'actor_user_id' => optional($request->user())->id,
            'action' => 'item.reopened',
            'meta' => ['item_hash' => $item->hash],
            'created_at' => now('UTC'),
        ]);

        return $this->resource($item);
    }

    protected function resource(ReminderItem $item): array
    {
        return [
            'hash' => $item->hash,
            'location_id' => $item->location_id,
            'name' => $item->name,
            'description' => $item->description,
            'due_at' => optional($item->due_at)?->toIso8601String(),
            'is_completed' => (bool) $item->is_completed,
            'completed_at' => optional($item->completed_at)?->toIso8601String(),
            'created_at' => optional($item->created_at)?->toIso8601String(),
            'updated_at' => optional($item->updated_at)?->toIso8601String(),
        ];
    }

    protected function spawnNextIfRecurring(ReminderItem $item): bool
    {
        /** @var ReminderItemRecurrence|null $rec */
        $rec = $item->recurrences()->with('type')->first();
        if (! $rec || ! $rec->is_active) {
            return false;
        }

        $interval = max(1, (int) $rec->interval_value);
        $currentDue = Carbon::parse($item->due_at, 'UTC');
        $isEom = $currentDue->isLastOfMonth();

        $type = optional($rec->type)->type ?? 'Custom';

        $nextDue = match ($type) {
            'Daily' => $currentDue->copy()->addDays($interval),
            'Weekly' => $currentDue->copy()->addWeeks($interval),
            'Monthly' => $this->addMonthsKeepingEom($currentDue, $interval, $isEom),
            'Yearly' => $this->addYearsKeepingEom($currentDue, $interval, $isEom),
            default => $currentDue->copy()->addDays($interval),
        };

        if ($rec->end_date && $nextDue->toDateString() > Carbon::parse($rec->end_date)->toDateString()) {
            return false; // do not spawn beyond end_date
        }

        $new = ReminderItem::create([
            'location_id' => $item->location_id,
            'name' => $item->name,
            'description' => $item->description,
            'due_at' => $nextDue->setTimezone('UTC'),
            'is_completed' => false,
            'is_active' => true,
        ]);

        // Carry tags forward
        $tagIds = $item->tags()->pluck('Reminder_Tags.id')->all();
        if ($tagIds) {
            $new->tags()->sync($tagIds);
        }

        // Carry recurrence forward
        ReminderItemRecurrence::create([
            'item_id' => $new->id,
            'recurring_type_id' => $rec->recurring_type_id,
            'interval_value' => $rec->interval_value,
            'anchor_date' => $rec->anchor_date,
            'end_date' => $rec->end_date,
            'timezone' => $rec->timezone ?? 'UTC',
            'is_active' => true,
        ]);

        ReminderActivityLog::create([
            'location_id' => $new->location_id,
            'item_id' => $new->id,
            'actor_user_id' => optional(request()->user())->id,
            'action' => 'item.spawned',
            'meta' => ['previous_item_hash' => $item->hash, 'item_hash' => $new->hash],
            'created_at' => now('UTC'),
        ]);

        return true;
    }

    private function addMonthsKeepingEom(Carbon $date, int $months, bool $isEom): Carbon
    {
        $d = $date->copy()->addMonthsNoOverflow($months);
        if ($isEom) {
            $d->endOfMonth();
        }
        return $d;
    }

    private function addYearsKeepingEom(Carbon $date, int $years, bool $isEom): Carbon
    {
        $d = $date->copy()->addYears($years);
        if ($isEom) {
            $d->endOfMonth();
        }
        return $d;
    }
}
