<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\ComplaintSubmitted;
use Tests\TestCase;

class ComplaintSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_can_view_the_complaint_form(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        Category::create(['name' => 'Academic']);

        $response = $this->actingAs($user)->get(route('complaints.create'));

        $response->assertOk();
        $response->assertSee('Submit a complaint');
        $response->assertSee('Academic');
    }

    public function test_a_student_can_submit_a_complaint(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $staff = User::factory()->create(['role' => 'staff']);
        $category = Category::create(['name' => 'Academic']);

        $response = $this->actingAs($user)->post(route('complaints.store'), [
            'category_id' => $category->id,
            'title' => 'Issue with examination schedule',
            'description' => 'The published examination schedule contains a conflicting time slot.',
            'priority' => 'high',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('complaints', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Issue with examination schedule',
            'priority' => 'high',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $staff->id,
            'type' => ComplaintSubmitted::class,
        ]);

        $staffDashboard = $this->actingAs($staff)->get(route('staff.dashboard'));

        $staffDashboard->assertOk();
        $staffDashboard->assertSee('Issue with examination schedule');
        $staffDashboard->assertSee('The published examination schedule contains a conflicting time slot.');
        $staffDashboard->assertSee('High');
    }

    public function test_invalid_complaint_data_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)->post(route('complaints.store'), [
            'category_id' => 999,
            'title' => '',
            'description' => 'Too short',
            'priority' => 'urgent',
        ]);

        $response->assertSessionHasErrors(['category_id', 'title', 'description', 'priority']);
        $this->assertSame(0, Complaint::count());
    }

    public function test_a_student_can_view_all_of_their_complaints_with_filters(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $category = Category::create(['name' => 'Academic']);
        Complaint::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Resolved examination issue',
            'description' => 'The examination issue was resolved by the academic department.',
            'priority' => 'high',
            'status' => 'resolved',
        ]);
        Complaint::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Pending transport issue',
            'description' => 'The transport schedule needs to be reviewed by the department.',
            'priority' => 'medium',
            'status' => 'pending',
        ]);
        Complaint::create([
            'user_id' => User::factory()->create(['role' => 'student'])->id,
            'category_id' => $category->id,
            'title' => 'Another student complaint',
            'description' => 'This complaint belongs to a different student account.',
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('complaints.index', [
            'status' => 'resolved',
            'search' => 'examination',
        ]));

        $response->assertOk();
        $response->assertSee('Resolved examination issue');
        $response->assertDontSee('Pending transport issue');
        $response->assertDontSee('Another student complaint');
    }
}