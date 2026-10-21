<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show the student dashboard with complaint statistics and recent complaints.
     */
    public function index()
    {
        $user = auth()->user();

        // Get total count of complaints by this student
        $totalComplaints = Complaint::where('user_id', $user->id)->count();

        // Get counts by status for the summary cards
        $pendingCount = Complaint::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $inProgressCount = Complaint::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->count();

        $resolvedCount = Complaint::where('user_id', $user->id)
            ->where('status', 'resolved')
            ->count();

        // Get the 5 most recent complaints with their category loaded
        $recentComplaints = Complaint::where('user_id', $user->id)
            ->with([
                'category',
                'responses' => fn ($query) => $query->latest(),
            ])
            ->latest()
            ->limit(5)
            ->get();

        $lastDashboardViewedAt = session('student_dashboard_last_viewed_at');
        $statusUpdatedComplaintIds = $lastDashboardViewedAt
            ? $recentComplaints
                ->filter(fn (Complaint $complaint) => $complaint->status_updated_at
                    && $complaint->status_updated_at->greaterThanOrEqualTo(Carbon::parse($lastDashboardViewedAt)))
                ->pluck('id')
                ->all()
            : [];

        session()->put('student_dashboard_last_viewed_at', now()->toIso8601String());

        $responseNotifications = ComplaintResponse::whereHas('complaint', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with('complaint')
            ->latest()
            ->get();
        $unreadResponseCount = $responseNotifications->whereNull('read_at')->count();

        return view('dashboard', compact(
            'totalComplaints',
            'pendingCount',
            'inProgressCount',
            'resolvedCount',
            'recentComplaints',
            'statusUpdatedComplaintIds',
            'responseNotifications',
            'unreadResponseCount'
        ));
    }
}
