<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\TaskSeries;
use App\Models\TaskTemplate;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model TaskSeries
 *
 * Menguji semua method dan relasi model TaskSeries:
 * - Primary key: task_series_id (HasCustomId)
 * - Relasi: template(), tasks()
 * - Scope: active()
 */
class TaskSeriesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: TaskSeries dapat dibuat dengan field fillable
     *
     * Memastikan name, description, template_id, series_tasks, is_active tersimpan
     */
    public function test_can_create_task_series_with_fillable_fields(): void
    {
        $template = TaskTemplate::create([
            'name' => 'Template',
            'association' => 'penanaman',
            'is_active' => true,
        ]);

        $series = TaskSeries::create([
            'name' => 'Series Mingguan',
            'description' => 'Task berulang mingguan',
            'template_id' => $template->task_template_id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('task_series', [
            'task_series_id' => $series->task_series_id,
            'name' => 'Series Mingguan',
            'template_id' => $template->task_template_id,
            'is_active' => true,
        ]);
    }

    /**
     * Test: Relasi template() mengembalikan TaskTemplate yang dipakai
     *
     * Memastikan belongsTo(TaskTemplate::class, 'template_id', 'task_template_id') berfungsi
     */
    public function test_template_relationship_returns_task_template(): void
    {
        $template = TaskTemplate::create([
            'name' => 'T',
            'association' => 'penanaman',
            'is_active' => true,
        ]);
        $series = TaskSeries::create([
            'name' => 'S',
            'template_id' => $template->task_template_id,
            'is_active' => true,
        ]);

        $this->assertNotNull($series->template);
        $this->assertEquals($template->task_template_id, $series->template->task_template_id);
    }

    /**
     * Test: Relasi tasks() mengembalikan task yang punya series_id ini
     *
     * Memastikan hasMany(Task::class, 'series_id', 'task_series_id') berfungsi
     */
    public function test_tasks_relationship_returns_tasks_in_this_series(): void
    {
        $template = TaskTemplate::create([
            'name' => 'T',
            'association' => 'penanaman',
            'is_active' => true,
        ]);
        $series = TaskSeries::create([
            'name' => 'S',
            'template_id' => $template->task_template_id,
            'is_active' => true,
        ]);

        $task1 = Task::create([
            'title' => 'Task 1',
            'series_id' => $series->task_series_id,
        ]);
        $task2 = Task::create([
            'title' => 'Task 2',
            'series_id' => $series->task_series_id,
        ]);

        $tasks = $series->tasks;

        $this->assertCount(2, $tasks);
        $this->assertTrue($tasks->contains($task1));
        $this->assertTrue($tasks->contains($task2));
    }

    /**
     * Test: Scope active() hanya mengembalikan series dengan is_active = true
     *
     * Memastikan scopeActive memfilter dengan where('is_active', true)
     */
    public function test_scope_active_filters_only_active_series(): void
    {
        $template = TaskTemplate::create([
            'name' => 'T',
            'association' => 'penanaman',
            'is_active' => true,
        ]);

        TaskSeries::create([
            'name' => 'Aktif',
            'template_id' => $template->task_template_id,
            'is_active' => true,
        ]);
        TaskSeries::create([
            'name' => 'Nonaktif',
            'template_id' => $template->task_template_id,
            'is_active' => false,
        ]);

        $active = TaskSeries::active()->get();

        $this->assertCount(1, $active);
        $this->assertSame('Aktif', $active->first()->name);
    }
}
