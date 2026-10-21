@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
<div class="staff-shell">
@if($notifications->isNotEmpty())
    <div class="staff-notifications" role="status" aria-live="polite">
        @foreach($notifications as $notification)
            <div class="staff-notification" data-notification>
                <div>
                    <strong>New complaint submitted</strong>
                    <p style="margin: 0.25rem 0 0.5rem;">
                        <a href="{{ route('staff.complaints.show', $notification->data['complaint_id']) }}" style="color: inherit; font-weight: 600;">
                            {{ $notification->data['title'] }}
                        </a>
                    </p>
                    <p style="margin: 0 0 0.5rem; color: var(--text-muted); font-size: 0.85rem;">
                        {{ $notification->data['description'] }}
                    </p>
                    <div style="font-size: 0.85rem;">
                        Urgency:
                        <span class="priority-badge priority-{{ $notification->data['priority'] }}">
                            {{ ucfirst($notification->data['priority']) }}
                        </span>
                    </div>
                </div>
                <button type="button" class="staff-notification-close" data-dismiss-notification aria-label="Dismiss notification">&times;</button>
            </div>
        @endforeach
    </div>
@endif

<div style="display: flex; justify-content: space-between; align-items: end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
    <div>
        <p class="staff-kicker">Staff workspace</p>
        <h1 style="font-size: 1.8rem; font-weight: 700; margin: 0.25rem 0;">Complaint dashboard</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Complaint queue: verify, prioritize, and resolve student grievances from one place.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(145px, 1fr)); gap: 0.85rem; margin-bottom: 1.5rem;">
    @foreach(['total' => 'Total', 'unverified' => 'Unverified', 'pending' => 'Pending', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'rejected' => 'Rejected'] as $key => $label)
        <div class="staff-card staff-stat">
            <div class="staff-stat-label">{{ $label }}</div>
            <div class="staff-stat-value">{{ $stats[$key] }}</div>
        </div>
    @endforeach
</div>

<form method="GET" action="{{ route('staff.dashboard') }}" class="staff-card staff-filters" style="display: grid; grid-template-columns: 2fr repeat(3, 1fr) auto; gap: 0.75rem; padding: 1rem; margin-bottom: 1rem; align-items: end;">
    <div>
        <label for="search" class="form-label">Search</label>
        <input id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Title, student, or email">
    </div>
    <div>
        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" class="form-control">
            <option value="">All statuses</option>
            @foreach(['pending' => 'Pending', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'rejected' => 'Rejected'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="priority" class="form-label">Priority</label>
        <select id="priority" name="priority" class="form-control">
            <option value="">All priorities</option>
            @foreach(['high', 'medium', 'low'] as $value)
                <option value="{{ $value }}" @selected(request('priority') === $value)>{{ ucfirst($value) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="verification" class="form-label">Verification</label>
        <select id="verification" name="verification" class="form-control">
            <option value="">All complaints</option>
            <option value="verified" @selected(request('verification') === 'verified')>Verified</option>
            <option value="unverified" @selected(request('verification') === 'unverified')>Unverified</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
</form>

<div class="staff-card" style="overflow: hidden;">
    @if($complaints->isEmpty())
        <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted);">
            <p style="font-weight: 600; color: var(--text-main);">No complaints match these filters.</p>
        </div>
    @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 850px;">
                <thead>
                    <tr style="background: var(--background); text-align: left;">
                        <th style="padding: 0.9rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">Complaint</th>
                        <th style="padding: 0.9rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">Student</th>
                        <th style="padding: 0.9rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">Category</th>
                        <th style="padding: 0.9rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">Priority</th>
                        <th style="padding: 0.9rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">Status</th>
                        <th style="padding: 0.9rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">Verification</th>
                        <th style="padding: 0.9rem 1.25rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($complaints as $complaint)
                        <tr style="border-top: 1px solid var(--border);">
                            <td style="padding: 1rem 1.25rem;">
                                <a href="{{ route('staff.complaints.show', $complaint) }}" style="font-weight: 600; color: var(--text-main); text-decoration: none;">{{ $complaint->title }}</a>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">{{ $complaint->created_at->format('M d, Y') }}</div>
                            </td>
                            <td style="padding: 1rem 1.25rem; font-size: 0.9rem;">{{ $complaint->user->name }}</td>
                            <td style="padding: 1rem 1.25rem; font-size: 0.9rem;">{{ $complaint->category->name }}</td>
                            <td style="padding: 1rem 1.25rem;"><span class="priority-badge priority-{{ $complaint->priority }}">{{ $complaint->priority }}</span></td>
                            <td style="padding: 1rem 1.25rem;"><span class="status-badge status-{{ $complaint->status }}">{{ str_replace('_', ' ', $complaint->status) }}</span></td>
                            <td style="padding: 1rem 1.25rem;">
                                @if($complaint->verified_at)
                                    <span class="verification-badge">Verified</span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">Needs review</span>
                                @endif
                            </td>
                            <td style="padding: 1rem 1.25rem; text-align: right;"><a href="{{ route('staff.complaints.show', $complaint) }}" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem;">Review</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
</div>
@endsection