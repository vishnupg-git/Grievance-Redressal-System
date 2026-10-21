<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintResponse;
use App\Models\User;
use App\Notifications\ComplaintSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    /**
     * Show the form for submitting a complaint.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('complaints.create', compact('categories'));
    }

    /**
     * Store a newly submitted complaint for the authenticated student.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        $complaint = Complaint::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        $complaint->load('user');
        User::where('role', 'staff')->get()->each(
            fn (User $staff) => $staff->notify(new ComplaintSubmitted($complaint))
        );

        return redirect()->route('dashboard')
            ->with('success', 'Your complaint has been submitted successfully.');
    }

    /**
     * Show all complaints to authenticated staff members.
     */
    public function staffIndex(): View
    {
        $query = Complaint::with(['user', 'category']);
        $notifications = request()->user()->unreadNotifications()->latest()->get();

        if ($search = request('search')) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('priority')) {
            $query->where('priority', request('priority'));
        }

        if (request('verification') === 'verified') {
            $query->whereNotNull('verified_at');
        } elseif (request('verification') === 'unverified') {
            $query->whereNull('verified_at');
        }

        $complaints = $query->latest()->get();
        $stats = [
            'total' => Complaint::count(),
            'pending' => Complaint::where('status', 'pending')->count(),
            'in_progress' => Complaint::where('status', 'in_progress')->count(),
            'resolved' => Complaint::where('status', 'resolved')->count(),
            'rejected' => Complaint::where('status', 'rejected')->count(),
            'unverified' => Complaint::whereNull('verified_at')->count(),
        ];

        return view('staff.complaints.index', compact('complaints', 'stats', 'notifications'));
    }

    /**
     * Show a complaint to authenticated staff members.
     */
    public function staffShow(Complaint $complaint): View
    {
        $complaint->load(['user', 'category', 'responses.staff', 'verifier']);

        return view('staff.complaints.show', compact('complaint'));
    }

    /**
     * Show a complaint and its responses to its student owner.
     */
    public function studentShow(Complaint $complaint): View
    {
        abort_unless($complaint->user_id === auth()->id(), 403);

        $complaint->load(['category', 'responses.staff']);

        return view('complaints.show', compact('complaint'));
    }

    /**
     * Update a complaint's workflow status.
     */
    public function updateStatus(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,resolved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'min:5', 'max:2000', 'required_if:status,rejected'],
        ]);

        $complaint->update([
            'status' => $validated['status'],
            'status_updated_at' => $complaint->status !== $validated['status']
                ? now()
                : $complaint->status_updated_at,
            'rejection_reason' => $validated['status'] === 'rejected'
                ? $validated['rejection_reason']
                : null,
        ]);

        return redirect()->route('staff.complaints.show', $complaint)
            ->with('success', 'Complaint status updated successfully.');
    }

    public function verify(Request $request, Complaint $complaint): RedirectResponse
    {
        $complaint->update([
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        return redirect()->route('staff.complaints.show', $complaint)
            ->with('success', 'Complaint verified successfully.');
    }

    /**
     * Add a response from the authenticated staff member.
     */
    public function storeResponse(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        ComplaintResponse::create([
            'complaint_id' => $complaint->id,
            'staff_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('staff.complaints.show', $complaint)
            ->with('success', 'Response added successfully.');
    }

    /**
     * Mark a response notification as read for its complaint owner.
     */
    public function markResponseAsRead(Request $request, ComplaintResponse $response): RedirectResponse
    {
        $response->load('complaint');
        abort_unless($response->complaint->user_id === $request->user()->id, 403);

        if ($response->read_at === null) {
            $response->update(['read_at' => now()]);
        }

        return redirect()->route('dashboard');
    }

    
/**
 * Show complaints submitted by the authenticated student.
 */
public function index(Request $request): View
{
    $validated = $request->validate([
        'status' => ['nullable', 'in:pending,in_progress,resolved,rejected'],
        'search' => ['nullable', 'string', 'max:255'],
    ]);

    $complaints = Complaint::where('user_id', $request->user()->id)
        ->with('category')
        ->withCount('responses')
        ->when($validated['status'] ?? null, function ($query, string $status) {
            $query->where('status', $status);
        })
        ->when($validated['search'] ?? null, function ($query, string $search) {
            $query->where('title', 'like', '%' . $search . '%');
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view('complaints.index', compact('complaints'));
}

    /**
     * Delete an untouched complaint owned by the authenticated student.
     */
    public function destroy(Request $request, Complaint $complaint): RedirectResponse
    {
        abort_unless($complaint->user_id === $request->user()->id, 403);

        $canDelete = $complaint->status === 'pending'
            && $complaint->verified_at === null
            && ! $complaint->responses()->exists();

        if (! $canDelete) {
            return redirect()->route('complaints.index')
                ->with('error', 'This complaint cannot be deleted because staff have already acted on it.');
        }

        $complaint->delete();

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint deleted successfully.');
    }

}