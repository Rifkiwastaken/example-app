<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use App\Models\PlantingLocation;
use App\Models\Planting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model Task
 * 
 * Test ini menguji semua method dan relasi yang ada di model Task
 */
class TaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat task baru dengan field sesuai input
     * 
     * Menguji bahwa task dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_task_with_all_fields(): void
    {
        // Menyiapkan data user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_satuan_tugas',
        ]);

        // Menyiapkan data task untuk diuji
        $taskData = [
            'title' => 'Task Test',
            'description' => 'Deskripsi task test',
            'new_status' => 'dalam_progress',
            'new_priority' => 'tinggi',
            'association' => 'penanaman',
            'assigned_to' => $user->user_id,
            'created_by' => $user->user_id,
            'start_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(7),
        ];

        // Membuat task baru
        $task = Task::create($taskData);

        // Memverifikasi bahwa task berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('tasks', [
            'task_id' => $task->task_id,
            'title' => 'Task Test',
            'new_status' => 'dalam_progress',
            'new_priority' => 'tinggi',
        ]);
    }

    /**
     * Test: Relasi assignedUser mengembalikan user yang ditugaskan
     * 
     * Menguji bahwa relasi belongs-to antara task dan user (assigned) berfungsi
     */
    public function test_assigned_user_relationship(): void
    {
        // Membuat user dan task
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_satuan_tugas',
        ]);

        $task = Task::create([
            'title' => 'Task Test',
            'assigned_to' => $user->user_id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar (bandingkan primary key user_id)
        $this->assertEquals($user->user_id, $task->assignedUser->user_id);
    }

    /**
     * Test: Relasi createdByUser mengembalikan user yang membuat task
     * 
     * Menguji bahwa relasi belongs-to antara task dan user (creator) berfungsi
     */
    public function test_created_by_user_relationship(): void
    {
        // Membuat user dan task
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_satuan_tugas',
        ]);

        $task = Task::create([
            'title' => 'Task Test',
            'created_by' => $user->user_id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar (bandingkan primary key user_id)
        $this->assertEquals($user->user_id, $task->createdByUser->user_id);
    }

    /**
     * Test: Relasi plantingLocation mengembalikan planting location yang terkait
     * 
     * Menguji bahwa relasi belongs-to antara task dan planting location berfungsi
     */
    public function test_planting_location_relationship(): void
    {
        // Membuat planting location dan task
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Test',
        ]);

        $task = Task::create([
            'title' => 'Task Test',
            'planting_location_id' => $plantingLocation->planting_location_id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar (bandingkan primary key planting_location_id)
        $this->assertEquals($plantingLocation->planting_location_id, $task->plantingLocation->planting_location_id);
    }

    /**
     * Test: Method getStatusLabel mengembalikan label status dalam bahasa Indonesia
     * 
     * Menguji bahwa setiap status memiliki label yang sesuai
     */
    public function test_get_status_label_returns_indonesian_label(): void
    {
        // Membuat task dengan status selesai
        $task1 = Task::create([
            'title' => 'Task Test',
            'new_status' => 'selesai',
        ]);
        $this->assertEquals('Telah dilakukan (Selesai)', $task1->status_label);

        // Membuat task dengan status dalam_progress
        $task2 = Task::create([
            'title' => 'Task Test',
            'new_status' => 'dalam_progress',
        ]);
        $this->assertEquals('Dalam progress/ akan dilakukan', $task2->status_label);

        // Membuat task dengan status tidak_selesai
        $task3 = Task::create([
            'title' => 'Task Test',
            'new_status' => 'tidak_selesai',
        ]);
        $this->assertEquals('Tidak selesai', $task3->status_label);
    }

    /**
     * Test: Method getPriorityLabel mengembalikan label prioritas dalam bahasa Indonesia
     * 
     * Menguji bahwa setiap prioritas memiliki label yang sesuai
     */
    public function test_get_priority_label_returns_indonesian_label(): void
    {
        // Membuat task dengan prioritas tertinggi
        $task1 = Task::create([
            'title' => 'Task Test',
            'new_priority' => 'tertinggi',
        ]);
        $this->assertEquals('Tertinggi', $task1->priority_label);

        // Membuat task dengan prioritas tinggi
        $task2 = Task::create([
            'title' => 'Task Test',
            'new_priority' => 'tinggi',
        ]);
        $this->assertEquals('Tinggi', $task2->priority_label);

        // Membuat task dengan prioritas medium
        $task3 = Task::create([
            'title' => 'Task Test',
            'new_priority' => 'medium',
        ]);
        $this->assertEquals('Medium', $task3->priority_label);
    }

    /**
     * Test: Method getAssociationLabel mengembalikan label asosiasi dalam bahasa Indonesia
     * 
     * Menguji bahwa setiap asosiasi memiliki label yang sesuai
     */
    public function test_get_association_label_returns_indonesian_label(): void
    {
        // Membuat task dengan asosiasi penanaman
        $task1 = Task::create([
            'title' => 'Task Test',
            'association' => 'penanaman',
        ]);
        $this->assertEquals('Penanaman', $task1->association_label);

        // Membuat task dengan asosiasi sertifikasi
        $task2 = Task::create([
            'title' => 'Task Test',
            'association' => 'sertifikasi',
        ]);
        $this->assertEquals('Sertifikasi', $task2->association_label);
    }

    /**
     * Test: Method getStatuses mengembalikan array semua status yang tersedia
     * 
     * Menguji bahwa method static getStatuses mengembalikan semua status dengan labelnya
     */
    public function test_get_statuses_returns_all_available_statuses(): void
    {
        // Memanggil method static getStatuses
        $statuses = Task::getStatuses();

        // Memverifikasi bahwa method mengembalikan array
        $this->assertIsArray($statuses);

        // Memverifikasi bahwa semua status yang diharapkan ada dalam array
        $this->assertArrayHasKey('dilakukan', $statuses);
        $this->assertArrayHasKey('dalam_progress', $statuses);
        $this->assertArrayHasKey('selesai', $statuses);
    }

    /**
     * Test: Method getPriorities mengembalikan array semua prioritas yang tersedia
     * 
     * Menguji bahwa method static getPriorities mengembalikan semua prioritas dengan labelnya
     */
    public function test_get_priorities_returns_all_available_priorities(): void
    {
        // Memanggil method static getPriorities
        $priorities = Task::getPriorities();

        // Memverifikasi bahwa method mengembalikan array
        $this->assertIsArray($priorities);

        // Memverifikasi bahwa semua prioritas yang diharapkan ada dalam array
        $this->assertArrayHasKey('tertinggi', $priorities);
        $this->assertArrayHasKey('tinggi', $priorities);
        $this->assertArrayHasKey('medium', $priorities);
    }

    /**
     * Test: Method getAssociations mengembalikan array semua asosiasi yang tersedia
     * 
     * Menguji bahwa method static getAssociations mengembalikan semua asosiasi dengan labelnya
     */
    public function test_get_associations_returns_all_available_associations(): void
    {
        // Memanggil method static getAssociations
        $associations = Task::getAssociations();

        // Memverifikasi bahwa method mengembalikan array
        $this->assertIsArray($associations);

        // Memverifikasi bahwa semua asosiasi yang diharapkan ada dalam array
        $this->assertArrayHasKey('penanaman', $associations);
        $this->assertArrayHasKey('sertifikasi', $associations);
        $this->assertArrayHasKey('gudang', $associations);
    }

    /**
     * Test: Scope assignedTo memfilter task berdasarkan user yang ditugaskan
     * 
     * Menguji bahwa scope dapat memfilter task dengan benar
     */
    public function test_scope_assigned_to_filters_tasks_by_assigned_user(): void
    {
        // Membuat user dan beberapa task
        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_satuan_tugas',
        ]);

        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_satuan_tugas',
        ]);

        $task1 = Task::create([
            'title' => 'Task 1',
            'assigned_to' => $user1->user_id,
        ]);

        $task2 = Task::create([
            'title' => 'Task 2',
            'assigned_to' => $user2->user_id,
        ]);

        // Memverifikasi bahwa scope berfungsi dengan benar (filter by user_id)
        $assignedTasks = Task::assignedTo($user1->user_id)->get();
        $this->assertTrue($assignedTasks->contains($task1));
        $this->assertFalse($assignedTasks->contains($task2));
    }

    /**
     * Test: Method isOverdue mengembalikan true jika task sudah melewati due date
     * 
     * Menguji bahwa method dapat memeriksa apakah task sudah melewati batas waktu
     */
    public function test_is_overdue_returns_true_when_task_is_overdue(): void
    {
        // Membuat task yang sudah melewati due date
        $overdueTask = Task::create([
            'title' => 'Overdue Task',
            'due_date' => Carbon::now()->subDays(5),
            'new_status' => 'dalam_progress',
        ]);

        // Memverifikasi bahwa task dianggap overdue
        $this->assertTrue($overdueTask->isOverdue());

        // Membuat task yang belum melewati due date
        $notOverdueTask = Task::create([
            'title' => 'Not Overdue Task',
            'due_date' => Carbon::now()->addDays(5),
            'new_status' => 'dalam_progress',
        ]);

        // Memverifikasi bahwa task tidak dianggap overdue
        $this->assertFalse($notOverdueTask->isOverdue());

        // Membuat task yang sudah selesai (tidak dianggap overdue)
        $completedTask = Task::create([
            'title' => 'Completed Task',
            'due_date' => Carbon::now()->subDays(5),
            'new_status' => 'selesai',
        ]);

        // Memverifikasi bahwa task yang sudah selesai tidak dianggap overdue
        $this->assertFalse($completedTask->isOverdue());
    }
}








