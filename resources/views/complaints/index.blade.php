@extends('layouts.app')

@section('title', 'My Complaints')

@section('content')
<div style="max-width: 1000px; margin: 0 auto;">
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 1rem;">{{ session('error') }}</div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem;">My Complaints</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Track every grievance you have submitted.</p>
        </div>
        <a href="{{ route('complaints.create') }}" class="btn btn-primary">+ Submit New Complaint</a>
    </div>

    <form method="GET" action="{{ route('complaints.index') }}" style="display: grid; grid-template-columns: minmax(0, 1fr) 180px auto; gap: 0.75rem; align-items: end; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem; margin-bottom: 1.25rem;">
        <div>
            <label for="search" class="form-label">Search by title</label>
            <input id="search" name="search" type="search" class="form-control" value="{{ request('search') }}" placeholder="Search complaints">
        </div>
        <div>
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="">All statuses</option>
                @foreach(['pending' => 'Pending', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'rejected' => 'Rejected'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-outline">Filter</button>
    </form>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;">
        @forelse($complaints as $complaint)
            @php
                $statusLabel = ucfirst(str_replace('_', ' ', $complaint->status));
                $statusColors = [
                    'pending' => ['bg' => '#fff7ed', 'color' => '#c2410c', 'border' => '#fed7aa'],
                    'in_progress' => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'border' => '#bfdbfe'],
                    'resolved' => ['bg' => '#f0fdf4', 'color' => '#15803d', 'border' => '#bbf7d0'],
                    'rejected' => ['bg' => '#fef2f2', 'color' => '#b91c1c', 'border' => '#fecaca'],
                ];
                $sc = $statusColors[$complaint->status] ?? $statusColors['pending'];
            @endphp
            <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div style="min-width: 0; flex: 1;">
                    <a href="{{ route('complaints.show', $complaint) }}" style="font-weight: 600; color: var(--text-main); text-decoration: none; font-size: 1rem;">
                        {{ $complaint->title }}
                    </a>
                    <div style="font-size: 0.825rem; color: var(--text-muted); margin-top: 0.3rem;">
                        {{ $complaint->category->name }} &bull; {{ ucfirst($complaint->priority) }} priority &bull; {{ $complaint->created_at->format('M d, Y') }}
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; justify-content: flex-end;">
                    <span style="background: {{ $sc['bg'] }}; color: {{ $sc['color'] }}; border: 1px solid {{ $sc['border'] }}; font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 9999px;">
                        {{ $statusLabel }}
                    </span>
                    @if($complaint->status === 'pending' && is_null($complaint->verified_at) && $complaint->responses_count === 0)
                        <form method="POST" action="{{ route('complaints.destroy', $complaint) }}" onsubmit="return confirm('Delete this complaint? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; color: #b91c1c; border-color: #fecaca;">Delete</button>
                        </form>
                    @else
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Deletion unavailable</span>
                    @endif
                </div>
            </div>
        @empty
            <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted);">
                <p style="font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">No complaints found</p>
                <p style="font-size: 0.9rem;">Try changing your filters or submit a new complaint.</p>
            </div>
        @endforelse
    </div>

    @if($complaints->hasPages())
        <nav aria-label="Complaint pages" style="display: flex; justify-content: center; align-items: center; gap: 0.75rem; margin-top: 1.25rem; flex-wrap: wrap;">
            @if($complaints->onFirstPage())
                <span aria-disabled="true" style="display: inline-flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border: 1px solid var(--border); border-radius: 8px; color: #cbd5e1; background: #f8fafc; font-size: 1.15rem; line-height: 1;">&larr;</span>
            @else
                <a href="{{ $complaints->previousPageUrl() }}" rel="prev" aria-label="Go to previous page" style="display: inline-flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border: 1px solid var(--border); border-radius: 8px; color: var(--primary); background: var(--surface); font-size: 1.15rem; line-height: 1; text-decoration: none; transition: background-color 0.15s, border-color 0.15s;" onmouseover="this.style.background='var(--primary-light)'; this.style.borderColor='var(--primary)'" onmouseout="this.style.background='var(--surface)'; this.style.borderColor='var(--border)'">&larr;</a>
            @endif

            <span style="min-width: 6.5rem; text-align: center; color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">
                Page {{ $complaints->currentPage() }} of {{ $complaints->lastPage() }}
            </span>

            @if($complaints->hasMorePages())
                <a href="{{ $complaints->nextPageUrl() }}" rel="next" aria-label="Go to next page" style="display: inline-flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border: 1px solid var(--border); border-radius: 8px; color: var(--primary); background: var(--surface); font-size: 1.15rem; line-height: 1; text-decoration: none; transition: background-color 0.15s, border-color 0.15s;" onmouseover="this.style.background='var(--primary-light)'; this.style.borderColor='var(--primary)'" onmouseout="this.style.background='var(--surface)'; this.style.borderColor='var(--border)'">&rarr;</a>
            @else
                <span aria-disabled="true" style="display: inline-flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border: 1px solid var(--border); border-radius: 8px; color: #cbd5e1; background: #f8fafc; font-size: 1.15rem; line-height: 1;">&rarr;</span>
            @endif
        </nav>
        <p style="text-align: center; margin-top: 0.5rem; color: var(--text-muted); font-size: 0.75rem;">
            Showing {{ $complaints->firstItem() }}–{{ $complaints->lastItem() }} of {{ $complaints->total() }} complaints
        </p>
    @endif
</div>
@endsection
