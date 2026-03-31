<?php

namespace Tests\Feature\App;

use App\Imports\AppContactsImport;
use App\Imports\AppNotesImport;
use App\Imports\AppTasksImport;
use App\Models\Contact;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Tests\TestCase;

class AppDataTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_notes_tasks_and_contacts_exports_are_downloadable_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        foreach (['app.notes.export' => 'notes-', 'app.tasks.export' => 'tasks-', 'app.contacts.export' => 'contacts-'] as $route => $prefix) {
            $response = $this->get(route($route));

            $response->assertOk();
            $this->assertStringContainsString($prefix, (string) $response->headers->get('content-disposition'));
            $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));
        }
    }

    public function test_task_modal_updates_an_existing_task(): void
    {
        $user = User::factory()->create();
        $assignee = User::factory()->create();

        $task = Task::create([
            'title' => 'Original title',
            'description' => 'Original description',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => '2026-03-31',
            'assigned_to' => null,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('app.task-modal')
            ->call('openTask', $task->id)
            ->set('taskTitle', 'Updated title')
            ->set('taskDesc', 'Updated description')
            ->set('taskStatus', 'completed')
            ->set('taskPriority', 'high')
            ->set('taskDueDate', '2026-04-02')
            ->set('taskAssigned', $assignee->id)
            ->call('saveTask')
            ->assertHasNoErrors();

        $task->refresh();

        $this->assertSame('Updated title', $task->title);
        $this->assertSame('Updated description', $task->description);
        $this->assertSame('completed', $task->status);
        $this->assertSame('high', $task->priority);
        $this->assertSame($assignee->id, $task->assigned_to);
        $this->assertSame('2026-04-02', $task->due_date?->format('Y-m-d'));
    }

    public function test_contact_modal_updates_an_existing_contact(): void
    {
        $user = User::factory()->create();

        $contact = Contact::create([
            'name' => 'Original Contact',
            'company' => 'Old Co',
            'email' => 'old@example.com',
            'phone' => '111',
            'status' => 'lead',
            'notes' => 'Old notes',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('app.contact-modal')
            ->call('openContact', $contact->id)
            ->set('name', 'Updated Contact')
            ->set('company', 'New Co')
            ->set('email', 'new@example.com')
            ->set('phone', '222')
            ->set('status', 'customer')
            ->set('notes', 'Updated notes')
            ->call('saveContact')
            ->assertHasNoErrors();

        $contact->refresh();

        $this->assertSame('Updated Contact', $contact->name);
        $this->assertSame('New Co', $contact->company);
        $this->assertSame('new@example.com', $contact->email);
        $this->assertSame('222', $contact->phone);
        $this->assertSame('customer', $contact->status);
        $this->assertSame('Updated notes', $contact->notes);
    }

    public function test_notes_import_updates_existing_rows_and_creates_new_ones(): void
    {
        $user = User::factory()->create();

        $note = Note::create([
            'title' => 'Original note',
            'content' => 'Old content',
            'color' => '#6366f1',
            'important_date' => '2026-03-31',
            'user_id' => $user->id,
        ]);

        $import = new AppNotesImport($user->id);

        $import->collection(new Collection([
            ['id' => $note->id, 'title' => 'Updated note', 'content' => 'Updated content', 'color' => '#22c55e', 'important_date' => '2026-04-05'],
            ['id' => null, 'title' => 'Created note', 'content' => 'New content', 'color' => '#ef4444', 'important_date' => '2026-04-06'],
        ]));

        $note->refresh();

        $this->assertSame(1, $import->updated);
        $this->assertSame(1, $import->created);
        $this->assertSame('Updated note', $note->title);
        $this->assertSame('Updated content', $note->content);
        $this->assertSame('#22c55e', $note->color);
        $this->assertSame('2026-04-05', $note->important_date?->format('Y-m-d'));
        $this->assertSame(2, Note::where('user_id', $user->id)->count());
    }

    public function test_tasks_import_updates_existing_rows_and_creates_new_ones(): void
    {
        $user = User::factory()->create();
        $assignee = User::factory()->create(['email' => 'assignee@example.com']);

        $task = Task::create([
            'title' => 'Original task',
            'description' => 'Old task description',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => '2026-03-31',
            'assigned_to' => null,
            'user_id' => $user->id,
        ]);

        $import = new AppTasksImport($user->id);

        $import->collection(new Collection([
            ['id' => $task->id, 'title' => 'Updated task', 'description' => 'Updated description', 'status' => 'completed', 'priority' => 'urgent', 'due_date' => '2026-04-07', 'assigned_email' => 'assignee@example.com'],
            ['id' => null, 'title' => 'Created task', 'description' => 'Created description', 'status' => 'pending', 'priority' => 'low', 'due_date' => '2026-04-08', 'assigned_email' => ''],
        ]));

        $task->refresh();

        $this->assertSame(1, $import->updated);
        $this->assertSame(1, $import->created);
        $this->assertSame('Updated task', $task->title);
        $this->assertSame('Updated description', $task->description);
        $this->assertSame('completed', $task->status);
        $this->assertSame('urgent', $task->priority);
        $this->assertSame($assignee->id, $task->assigned_to);
        $this->assertSame('2026-04-07', $task->due_date?->format('Y-m-d'));
        $this->assertSame(2, Task::where('user_id', $user->id)->count());
    }

    public function test_contacts_import_updates_existing_rows_and_creates_new_ones(): void
    {
        $user = User::factory()->create();

        $contact = Contact::create([
            'name' => 'Original contact',
            'company' => 'Old Company',
            'email' => 'old-contact@example.com',
            'phone' => '111',
            'status' => 'lead',
            'notes' => 'Old notes',
            'user_id' => $user->id,
        ]);

        $import = new AppContactsImport($user->id);

        $import->collection(new Collection([
            ['id' => $contact->id, 'name' => 'Updated contact', 'company' => 'New Company', 'email' => 'new-contact@example.com', 'phone' => '222', 'status' => 'customer', 'notes' => 'Updated notes'],
            ['id' => null, 'name' => 'Created contact', 'company' => 'Created Company', 'email' => 'created@example.com', 'phone' => '333', 'status' => 'prospect', 'notes' => 'Created notes'],
        ]));

        $contact->refresh();

        $this->assertSame(1, $import->updated);
        $this->assertSame(1, $import->created);
        $this->assertSame('Updated contact', $contact->name);
        $this->assertSame('New Company', $contact->company);
        $this->assertSame('new-contact@example.com', $contact->email);
        $this->assertSame('222', $contact->phone);
        $this->assertSame('customer', $contact->status);
        $this->assertSame('Updated notes', $contact->notes);
        $this->assertSame(2, Contact::where('user_id', $user->id)->count());
    }
}