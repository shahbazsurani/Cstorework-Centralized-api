<?php

namespace App\Reminders\Http\Controllers;

use App\Reminders\Models\ReminderItem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = ReminderItem::query();

        if ($v = $request->get('location_id')) {
            $q->where('location_id', (int) $v);
        }
        if ($v = $request->get('status')) {
            if ($v === 'open') $q->where('is_completed', false);
            if ($v === 'completed') $q->where('is_completed', true);
            if ($v === 'overdue') $q->where('is_completed', false)->where('due_at', '<', now('UTC'));
        }
        if ($from = $request->get('due_from')) {
            $q->where('due_at', '>=', $from);
        }
        if ($to = $request->get('due_to')) {
            $q->where('due_at', '<=', $to);
        }

        $sortBy = in_array($request->get('sort_by'), ['due_at','created_at']) ? $request->get('sort_by') : 'due_at';
        $sortDir = $request->get('sort_dir') === 'desc' ? 'desc' : 'asc';
        $q->orderBy($sortBy, $sortDir);

        return $q->paginate(
            perPage: (int) ($request->get('per_page', 15)),
            pageName: 'page'
        )->through(function ($item) {
            return [
                'hash' => $item->hash,
                'name' => $item->name,
                'due_at' => optional($item->due_at)?->toIso8601String(),
                'is_completed' => (bool) $item->is_completed,
            ];
        });
    }
}
