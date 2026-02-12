<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\TaskTemplate;
use App\Models\Task;
use App\Models\TaskSeries;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model TaskTemplate
 *
 * Menguji semua method dan relasi: tasks(), series(), association_label,
 * getAssociations(), scope active()
 */
class TaskTemplateTest extends TestCase
{
    use RefreshDatabase;

    /** Test: TaskTemplate dapat dibuat dengan field fillable */
    public function test_can_create_task_template_with_fillable_fields(): void
    {
        $template = TaskTemplate::create([
            'name' => 'Template Penanaman',
            'description' => 'Deskripsi template',
            'association' => 'penanaman',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('task_templates', [
            'task_template_id' => $template->task_template_id,
            'name' => 'Template Penanaman',
            'association' => 'penanaman',
            'is_active' => true,
        ]);
    }

    /** Test: Relasi tasks() mengembalikan task yang memakai template ini */
    public function test_tasks_relationship_returns_tasks_using_this_template(): void
    {
        $template = TaskTemplate::create([
            'name' => 'T',
            'association' => 'penanaman',
            'is_active' => true,
        ]);

        $task1 = Task::create([
            'title' => 'Task 1',
            'template_id' => $template->task_template_id,
        ]);
        $task2 = Task::create([
            'title' => 'Task 2',
            'template_id' => $template->task_template_id,
        ]);

        $tasks = $template->tasks;

        $this->assertCount(2, $tasks);
        $this->assertTrue($tasks->contains($task1));
        $this->assertTrue($tasks->contains($task2));
    }

    /** Test: Relasi series() mengembalikan TaskSeries yang memakai template ini */
    public function test_series_relationship_returns_series_using_this_template(): void
    {
        $template = TaskTemplate::create([
            'name' => 'T',
            'association' => 'gudang',
            'is_active' => true,
        ]);

        $series1 = TaskSeries::create([
            'name' => 'Series 1',
            'template_id' => $template->task_template_id,
            'is_active' => true,
        ]);

        $series = $template->series;

        $this->assertCount(1, $series);
        $this->assertTrue($series->contains($series1));
    }

    /** Test: association_label accessor mengembalikan label Indonesia */
    public function test_association_label_returns_indonesian_label(): void
    {
        $t = TaskTemplate::create(['name' => 'T', 'association' => 'penanaman', 'is_active' => true]);
        $this->assertSame('Penanaman', $t->association_label);

        $t2 = TaskTemplate::create(['name' => 'T2', 'association' => 'sertifikasi', 'is_active' => true]);
        $this->assertSame('Sertifikasi', $t2->association_label);

        $t3 = TaskTemplate::create(['name' => 'T3', 'association' => 'gudang', 'is_active' => true]);
        $this->assertSame('Gudang', $t3->association_label);

        $t4 = TaskTemplate::create(['name' => 'T4', 'association' => 'penjualan', 'is_active' => true]);
        $this->assertSame('Penjualan', $t4->association_label);
    }

    /** Test: getAssociations() mengembalikan array semua asosiasi */
    public function test_get_associations_returns_all_available_associations(): void
    {
        $associations = TaskTemplate::getAssociations();

        $this->assertIsArray($associations);
        $this->assertArrayHasKey('penanaman', $associations);
        $this->assertArrayHasKey('sertifikasi', $associations);
        $this->assertArrayHasKey('gudang', $associations);
        $this->assertArrayHasKey('penjualan', $associations);
    }

    /** Test: Scope active() hanya mengembalikan template dengan is_active = true */
    public function test_scope_active_filters_only_active_templates(): void
    {
        TaskTemplate::create(['name' => 'A', 'association' => 'penanaman', 'is_active' => true]);
        TaskTemplate::create(['name' => 'B', 'association' => 'penanaman', 'is_active' => false]);

        $active = TaskTemplate::active()->get();

        $this->assertCount(1, $active);
        $this->assertSame('A', $active->first()->name);
    }
}
