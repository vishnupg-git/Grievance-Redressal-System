@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')

{{-- Page Header --}}
<div class="mobile-stack" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="mobile-heading" style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem;">
            Welcome back, {{ auth()->user()->name }}! 👋
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
            Here's an overview of your grievances
        </p>
    </div>
    <a href="{{ route('complaints.create') }}" class="btn btn-primary mobile-full-width" id="btn-submit-complaint">
        + Submit New Complaint
    </a>
</div>

{{-- Stats Cards --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem;">

    {{-- Total --}}
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow-sm);">
        <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">
            Total Complaints
        </div>
        <div style="font-size: 2.25rem; font-weight: 700; color: var(--text-main);">
            {{ $totalComplaints }}
        </div>
    </div>

    {{-- Pending --}}
    <div style="background: var(--surface); border: 1px solid #fed7aa; border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow-sm);">
        <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: #9a3412; margin-bottom: 0.5rem;">
            Pending
        </div>
        <div style="font-size: 2.25rem; font-weight: 700; color: #ea580c;">
            {{ $pendingCount }}
        </div>
    </div>

    {{-- In Progress --}}
    <div style="background: var(--surface); border: 1px solid #bfdbfe; border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow-sm);">
        <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: #1e3a5f; margin-bottom: 0.5rem;">
            In Progress
        </div>
        <div style="font-size: 2.25rem; font-weight: 700; color: #2563eb;">
            {{ $inProgressCount }}
        </div>
    </div>

    {{-- Resolved --}}
    <div style="background: var(--surface); border: 1px solid #a7f3d0; border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow-sm);">
        <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: #065f46; margin-bottom: 0.5rem;">
            Resolved
        </div>
        <div style="font-size: 2.25rem; font-weight: 700; color: #10b981;">
            {{ $resolvedCount }}
        </div>
    </div>

</div>

{{-- Staff Response Notifications --}}
<div style="background: var(--surface); border: 1px solid {{ $unreadResponseCount > 0 ? '#c7d2fe' : 'var(--border)' }}; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 1.5rem; overflow: hidden;">
    <div class="mobile-stack" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;">
        <h2 style="font-size: 1.05rem; font-weight: 700; margin: 0;">
            Staff Responses
            @if($unreadResponseCount > 0)
                <span aria-label="{{ $unreadResponseCount }} unread responses" style="display: inline-flex; align-items: center; justify-content: center; min-width: 1.4rem; height: 1.4rem; margin-left: 0.35rem; padding: 0 0.35rem; border-radius: 9999px; background: var(--primary); color: white; font-size: 0.75rem;">{{ $unreadResponseCount }}</span>
            @endif
        </h2>
    </div>
    @forelse($responseNotifications as $notification)
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); background: {{ $notification->read_at ? 'var(--surface)' : 'var(--primary-light)' }};">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                <div style="min-width: 0; flex: 1;">
                    <div style="font-weight: 700; color: var(--text-main);">
                        {{ $notification->complaint->title }}
                        @if($notification->read_at === null)
                            <span style="font-size: 0.7rem; color: var(--primary); margin-left: 0.35rem;">Unread</span>
                        @endif
                    </div>
                    <p style="margin: 0.35rem 0; color: var(--text-muted); font-size: 0.9rem;">
                        {{ \Illuminate\Support\Str::limit($notification->comment, 140) }}
                    </p>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">
                        {{ $notification->created_at->format('M d, Y g:i A') }}
                        &bull; Status: {{ ucfirst(str_replace('_', ' ', $notification->complaint->status)) }}
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                    <a href="{{ route('complaints.show', $notification->complaint) }}" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem;">View complaint</a>
                    @if($notification->read_at === null)
                        <form method="POST" action="{{ route('notifications.responses.read', $notification) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.8rem;">Mark as read</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div style="padding: 2rem 1.5rem; text-align: center; color: var(--text-muted);">
            No new staff responses.
        </div>
    @endforelse
</div>

{{-- Recent Complaints --}}
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow);">

    <div class="mobile-stack" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;">
        <h2 style="font-size: 1.05rem; font-weight: 700; margin: 0;">
            Recent Complaints
        </h2>
        <a href="{{ route('complaints.index') }}" style="font-size: 0.875rem; color: var(--primary); text-decoration: none; font-weight: 600;">
            View All →
        </a>
    </div>

    @if($recentComplaints->isEmpty())
        <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted);">
            <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">📭</div>
            <p style="font-weight: 600; font-size: 1rem; margin-bottom: 0.25rem; color: var(--text-main);">No complaints yet</p>
            <p style="font-size: 0.9rem;">You haven't submitted any grievances.</p>
            <a href="{{ route('complaints.create') }}" class="btn btn-primary" style="display: inline-flex; margin-top: 1rem;" id="btn-submit-first-complaint">
                Submit Your First Complaint
            </a>
        </div>
    @else
        <div>
            @foreach($recentComplaints as $complaint)
                <div class="mobile-complaint-row" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; gap: 1rem; transition: background 0.15s;" onmouseover="this.style.background='var(--background)'" onmouseout="this.style.background=''">
                    <div style="flex: 1; min-width: 0;">
                        <a href="{{ route('complaints.show', $complaint->id) }}" style="font-weight: 600; color: var(--text-main); text-decoration: none; font-size: 0.95rem; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" id="complaint-{{ $complaint->id }}">
                            {{ $complaint->title }}
                        </a>
                        <div style="font-size: 0.825rem; color: var(--text-muted); margin-top: 0.2rem;">
                            {{ $complaint->category->name }} &bull; {{ $complaint->created_at->format('M d, Y') }}
                        </div>
                    </div>
                    <div class="mobile-badges" style="display: flex; gap: 0.5rem; align-items: center; flex-shrink: 0;">
                        @if(in_array($complaint->id, $statusUpdatedComplaintIds, true))
                            <span style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 9999px;">Updated since last visit</span>
                        @endif
                        {{-- Priority Badge --}}
                        @php
                            $priorityColors = [
                                'low'    => ['bg' => '#f0fdf4', 'color' => '#15803d', 'border' => '#bbf7d0'],
                                'medium' => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a'],
                                'high'   => ['bg' => '#fef2f2', 'color' => '#b91c1c', 'border' => '#fecaca'],
                            ];
                            $pc = $priorityColors[$complaint->priority] ?? $priorityColors['medium'];
                        @endphp
                        <span style="background: {{ $pc['bg'] }}; color: {{ $pc['color'] }}; border: 1px solid {{ $pc['border'] }}; font-size: 0.75rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 9999px; text-transform: capitalize;">
                            {{ $complaint->priority }}
                        </span>

                        {{-- Status Badge --}}
                        @php
                            $statusColors = [
                                'pending'     => ['bg' => '#fff7ed', 'color' => '#c2410c', 'border' => '#fed7aa'],
                                'in_progress' => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'border' => '#bfdbfe'],
                                'resolved'    => ['bg' => '#f0fdf4', 'color' => '#15803d', 'border' => '#bbf7d0'],
                                'rejected'    => ['bg' => '#fef2f2', 'color' => '#b91c1c', 'border' => '#fecaca'],
                            ];
                            $sc = $statusColors[$complaint->status] ?? $statusColors['pending'];
                            $statusLabel = ucfirst(str_replace('_', ' ', $complaint->status));
                        @endphp
                        <span style="background: {{ $sc['bg'] }}; color: {{ $sc['color'] }}; border: 1px solid {{ $sc['border'] }}; font-size: 0.75rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 9999px;">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    @if($complaint->status_updated_at || $complaint->responses->isNotEmpty())
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.35rem;">
                            @if($complaint->status_updated_at)
                                Status updated {{ $complaint->status_updated_at->format('M d, Y g:i A') }}
                            @endif
                            @if($complaint->responses->isNotEmpty())
                                @if($complaint->status_updated_at) &bull; @endif
                                Latest staff response {{ $complaint->responses->first()->created_at->format('M d, Y g:i A') }}:
                                {{ \Illuminate\Support\Str::limit($complaint->responses->first()->comment, 120) }}
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if($totalComplaints > 5)
            <div style="padding: 1rem 1.5rem; text-align: center;">
                <a href="{{ route('complaints.index') }}" style="font-size: 0.875rem; color: var(--primary); text-decoration: none; font-weight: 600;">
                    View all {{ $totalComplaints }} complaints →
                </a>
            </div>
        @endif
    @endif
</div>

{{-- Account Info --}}
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); margin-top: 1.5rem; padding: 1.5rem;">
    <h2 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1rem;">Account Information</h2>
    <div class="mobile-single-column" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div>
            <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 0.2rem;">Name</span>
            <span style="font-size: 0.95rem; font-weight: 500;">{{ auth()->user()->name }}</span>
        </div>
        <div>
            <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 0.2rem;">Email</span>
            <span style="font-size: 0.95rem; font-weight: 500;">{{ auth()->user()->email }}</span>
        </div>
        <div>
            <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 0.2rem;">Role</span>
            <span style="background: #eef2ff; color: #4f46e5; font-size: 0.8rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 9999px; text-transform: uppercase;">
                {{ auth()->user()->role }}
            </span>
        </div>
        <div>
            <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 0.2rem;">Registered Since</span>
            <span style="font-size: 0.95rem; font-weight: 500;">{{ auth()->user()->created_at->format('M d, Y') }}</span>
        </div>
    </div>
</div>

@endsection
