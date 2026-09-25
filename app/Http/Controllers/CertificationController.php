<?php

namespace App\Http\Controllers;

use App\Models\CertificationReport;
use App\Models\Plant;
use App\Models\Planting;
use App\Models\PlantingLocation;
use App\Models\PlantingPostHarvest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Ringkasan laporan sertifikasi lintas produksi. Pencatatan laporan itu sendiri
 * dilakukan dari halaman laporan produksi (ProductionActivityController).
 */
class CertificationController extends Controller
{
    public function index()
    {
        $plants = Plant::with(['type'])->orderBy('name')->get();

        $plantingsByPlant = Planting::with('seedSource')
            ->get()
            ->groupBy(fn (Planting $planting) => $planting->seedSource?->seed_varieties_id);

        foreach ($plants as $plant) {
            $plantings = $plantingsByPlant->get($plant->getKey(), collect());
            $ids = $plantings->pluck('planting_production_id')->all();

            $plant->certification_reports_count = $this->stageCount($ids);
            $plant->lab_results_count = PlantingPostHarvest::whereIn('planting_id', $ids)->count();
            $plant->labels_count = CertificationReport::whereIn(
                'planting_post_harvest_id',
                PlantingPostHarvest::whereIn('planting_id', $ids)->select('planting_post_harvest_id')
            )->count();
        }

        return view('certifications.index', compact('plants'));
    }

    public function showByPlant(Plant $plant, Request $request)
    {
        $plantings = Planting::with(['seedSource.variety', 'field.plantingLocation'])
            ->whereHas('seedSource', fn ($q) => $q->where('seed_varieties_id', $plant->getKey()))
            ->get();

        $locationFilter = $request->get('location_id');
        if ($locationFilter) {
            $plantings = $plantings->filter(fn (Planting $p) => $p->field?->planting_location_id === $locationFilter);
        }

        $reports = collect();
        foreach ($plantings as $planting) {
            foreach (ProductionActivityController::STAGES as $stage => $meta) {
                /** @var class-string<Model> $class */
                $class = $meta['model'];
                foreach ($class::where('planting_id', $planting->getKey())->with('creator')->get() as $record) {
                    $reports->push([
                        'planting' => $planting,
                        'stage' => $stage,
                        'stage_label' => $meta['label'],
                        'title' => $record->reportTitle(),
                        'date' => $record->reportDate(),
                        'supervisor' => $record->supervisorName(),
                        'status' => $record->reportStatus(),
                        'record' => $record,
                    ]);
                }
            }
        }
        $reports = $reports->sortByDesc(fn (array $r) => $r['record']->created_at)->values();

        $labResults = PlantingPostHarvest::whereIn('planting_id', $plantings->pluck('planting_production_id'))
            ->with(['planting.field.plantingLocation', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        $labels = CertificationReport::whereIn('planting_post_harvest_id', $labResults->pluck('planting_post_harvest_id'))
            ->with('postHarvest.planting.field.plantingLocation')
            ->orderByDesc('created_at')
            ->get();

        $locations = PlantingLocation::orderBy('name')->get();

        return view('certifications.by-plant', compact('plant', 'reports', 'labResults', 'labels', 'locations', 'locationFilter'));
    }

    public function showLabel(CertificationReport $report)
    {
        $report->load(['postHarvest.planting.field.plantingLocation', 'postHarvest.planting.seedSource.variety', 'packagings']);

        return view('certifications.label-show', compact('report'));
    }

    /**
     * @param  array<int, string>  $plantingIds
     */
    protected function stageCount(array $plantingIds): int
    {
        if (empty($plantingIds)) {
            return 0;
        }

        $total = 0;
        foreach (ProductionActivityController::STAGES as $meta) {
            /** @var class-string<Model> $class */
            $class = $meta['model'];
            $total += $class::whereIn('planting_id', $plantingIds)->count();
        }

        return $total;
    }
}
