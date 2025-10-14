<?php

namespace App\Reminders\Http\Controllers;

use App\Reminders\Models\ReminderItem;
use App\Reminders\Models\ReminderNotification;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now('UTC');
        return [
            'overdue_by_location' => ReminderItem::selectRaw('location_id, count(*) as cnt')
                ->where('is_completed', false)
                ->where('due_at', '<', $now)
                ->groupBy('location_id')
                ->orderByDesc('cnt')
                ->limit(10)
                ->get()
                ->map(fn ($r) => ['location_id' => $r->location_id, 'count' => (int) $r->cnt]),
            'due_soon' => [
                '30d' => ReminderItem::whereBetween('due_at', [$now, $now->copy()->addDays(30)])->count(),
                '60d' => ReminderItem::whereBetween('due_at', [$now, $now->copy()->addDays(60)])->count(),
                '90d' => ReminderItem::whereBetween('due_at', [$now, $now->copy()->addDays(90)])->count(),
            ],
            'notifications_pending' => ReminderNotification::where('status', 'pending')->count(),
        ];
    }
}
