<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Stage12ComplaintTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_can_delete_their_untouched_pending_complaint(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $category = Category::create(['name' => 'Academic']);
        $complaint = Complaint::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'title' => 'Pending complaint',
            'description' => 'This complaint has not been reviewed by staff yet.',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($student)->delete(route('complaints.destroy', $complaint));

        $response->assertRedirect(route('complaints.index'));
        $response->assertSessionHas('success', 'Complaint deleted successfully.');
        $this->assertDatabaseMissing('complaints', ['id' => $complaint->id]);
    }

    public function test_a_student_cannot_delete_another_students_complaint(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $owner = User::factory()->create(['role' => 'student']);
        $category = Category::create(['name' => 'Academic']);
        $complaint = Complaint::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Private complaint',
            'description' => 'This complaint belongs to another student.',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $this->actingAs($student)
            ->delete(route('complaints.destroy', $complaint))
            ->assertForbidden();

        $this->assertDatabaseHas('complaints', ['id' => $complaint->id]);
    }

    public function test_students_cannot_delete_reviewed_or_responded_complaints(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $staff = User::factory()->create(['role' => 'staff']);
        $category = Category::create(['name' => 'Academic']);
        $complaint = Complaint::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'title' => 'Reviewed complaint',
            'description' => 'This complaint has already been reviewed by staff.',
            'priority' => 'high',
            'status' => 'in_progress',
        ]);
        ComplaintResponse::create([
            'complaint_id' => $complaint->id,
            'staff_id' => $staff->id,
            'comment' => 'Staff have started reviewing this complaint.',
        ]);

        $response = $this->actingAs($student)
            ->delete(route('complaints.destroy', $complaint));

        $response->assertRedirect(route('complaints.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('complaints', ['id' => $complaint->id]);
    }

    public function test_staff_cannot_use_student_deletion_endpoint(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $staff = User::factory()->create(['role' => 'staff']);
        $category = Category::create(['name' => 'Academic']);
        $complaint = Complaint::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'title' => 'Protected complaint',
            'description' => 'This complaint should remain protected.',
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $this->actingAs($staff)->delete(route('complaints.destroy', $complaint))->assertForbidden();
        $this->assertDatabaseHas('complaints', ['id' => $complaint->id]);
    }

    public function test_guests_cannot_use_student_deletion_endpoint(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $category = Category::create(['name' => 'Academic']);
        $complaint = Complaint::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'title' => 'Protected complaint',
            'description' => 'This complaint should remain protected.',
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $this->delete(route('complaints.destroy', $complaint))->assertRedirect(route('login'));
        $this->assertDatabaseHas('complaints', ['id' => $complaint->id]);
    }

    public function test_staff_status_changes_appear_on_the_owners_dashboard_but_not_another_students_dashboard(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $staff = User::factory()->create(['role' => 'staff']);
        $category = Category::create(['name' => 'Academic']);
        $complaint = Complaint::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'title' => 'Status update complaint',
            'description' => 'This complaint will receive a staff status update.',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $this->actingAs($student)->get(route('dashboard'));
        $this->actingAs($staff)->patch(route('staff.complaints.status', $complaint), ['status' => 'in_progress']);
        $studentDashboard = $this->actingAs($student)->get(route('dashboard'));

        $studentDashboard->assertSee('Updated since last visit');
        $studentDashboard->assertSee('In progress');

        $this->actingAs($otherStudent)->get(route('dashboard'))->assertDontSee($complaint->title);
    }

    public function test_staff_responses_are_shown_separately_from_status_updates_on_the_dashboard(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $staff = User::factory()->create(['role' => 'staff']);
        $category = Category::create(['name' => 'Academic']);
        $complaint = Complaint::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'title' => 'Response complaint',
            'description' => 'This complaint will receive a staff response.',
            'priority' => 'medium',
            'status' => 'pending',
        ]);
        ComplaintResponse::create([
            'complaint_id' => $complaint->id,
            'staff_id' => $staff->id,
            'comment' => 'We received your complaint and will review it.',
        ]);

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSee('Latest staff response');
        $response->assertSee('We received your complaint and will review it.');
        $response->assertDontSee('Updated since last visit');
    }

    public function test_staff_response_notifications_are_visible_only_to_the_complaint_owner(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $staff = User::factory()->create(['role' => 'staff']);
        $category = Category::create(['name' => 'Academic']);
        $complaint = Complaint::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'title' => 'Notification complaint',
            'description' => 'This complaint has a staff response notification.',
            'priority' => 'medium',
            'status' => 'in_progress',
        ]);
        ComplaintResponse::create([
            'complaint_id' => $complaint->id,
            'staff_id' => $staff->id,
            'comment' => 'Your complaint has been reviewed by staff.',
        ]);

        $studentDashboard = $this->actingAs($student)->get(route('dashboard'));

        $studentDashboard->assertSee('Staff Responses');
        $studentDashboard->assertSee('Notification complaint');
        $studentDashboard->assertSee('Your complaint has been reviewed by staff.');
        $studentDashboard->assertSee('1', false);
        $this->actingAs($otherStudent)->get(route('dashboard'))
            ->assertDontSee('Notification complaint')
            ->assertDontSee('Your complaint has been reviewed by staff.');
    }

    public function test_reading_a_response_notification_updates_its_read_state_without_changing_status(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $staff = User::factory()->create(['role' => 'staff']);
        $category = Category::create(['name' => 'Academic']);
        $complaint = Complaint::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'title' => 'Readable notification',
            'description' => 'This complaint has a notification that can be acknowledged.',
            'priority' => 'medium',
            'status' => 'in_progress',
        ]);
        $response = ComplaintResponse::create([
            'complaint_id' => $complaint->id,
            'staff_id' => $staff->id,
            'comment' => 'Please review this response.',
        ]);

        $this->actingAs($student)
            ->post(route('notifications.responses.read', $response))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('complaint_responses', [
            'id' => $response->id,
            'read_at' => $response->fresh()->read_at,
        ]);
        $this->assertNotNull($response->fresh()->read_at);
        $this->assertSame('in_progress', $complaint->fresh()->status);
    }

    public function test_repeated_dashboard_visits_do_not_create_duplicate_response_notifications(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $staff = User::factory()->create(['role' => 'staff']);
        $category = Category::create(['name' => 'Academic']);
        $complaint = Complaint::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'title' => 'Single notification',
            'description' => 'This response should appear as one notification.',
            'priority' => 'low',
            'status' => 'pending',
        ]);
        ComplaintResponse::create([
            'complaint_id' => $complaint->id,
            'staff_id' => $staff->id,
            'comment' => 'This response should not be duplicated.',
        ]);

        $first = $this->actingAs($student)->get(route('dashboard'));
        $second = $this->actingAs($student)->get(route('dashboard'));

        $this->assertSame(
            substr_count($first->getContent(), 'This response should not be duplicated.'),
            substr_count($second->getContent(), 'This response should not be duplicated.')
        );
        $this->assertSame(1, ComplaintResponse::count());
        $this->assertNull(ComplaintResponse::first()->fresh()->read_at);
    }

    public function test_status_changes_without_responses_do_not_create_response_notifications(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $staff = User::factory()->create(['role' => 'staff']);
        $category = Category::create(['name' => 'Academic']);
        $complaint = Complaint::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'title' => 'Status only complaint',
            'description' => 'This complaint has a status update but no response.',
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $this->actingAs($staff)->patch(route('staff.complaints.status', $complaint), [
            'status' => 'in_progress',
        ]);

        $this->actingAs($student)->get(route('dashboard'))
            ->assertSee('Staff Responses')
            ->assertSee('No new staff responses.')
            ->assertDontSee('Latest staff response');
        $this->assertSame(0, ComplaintResponse::count());
    }
}
