<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\CertificationReport;
use App\Models\Harvest;
use App\Models\Plant;
use App\Models\Planting;
use App\Models\PlantingLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificationController extends Controller
{
    public function index(Request $request)
    {
        // Get harvests that have certifications or are ready for certification
        $query = Harvest::with([
            'plant.type',
            'location',
            'planting',
            'certification.reports' => function($q) {
                $q->orderBy('report_date', 'desc');
            }
        ]);

        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }

        $harvests = $query->orderBy('harvested_at', 'desc')->paginate(15);
        $plants = Plant::orderBy('name')->get();
        
        // Get harvests available for certification (without existing certification)
        $availableHarvests = Harvest::with(['plant.type', 'location'])
            ->whereDoesntHave('certification')
            ->orderBy('harvested_at', 'desc')
            ->limit(50)
            ->get();

        return view('certifications/index', compact('harvests', 'plants', 'availableHarvests'));
    }

    public function show(Harvest $harvest)
    {
        // Get or create certification for this harvest
        $certification = Certification::firstOrCreate(
            ['harvest_id' => $harvest->id],
            [
                'certification_status' => 'dalam_proses',
                'seed_class_requested' => 'BP', // Default
            ]
        );

        $certification->load([
            'harvest.plant.type',
            'harvest.location.baseLocation',
            'harvest.planting',
            'plantingLocation.baseLocation',
            'plant.type',
            'reports' => function($q) {
                $q->orderBy('report_date', 'desc');
            }
        ]);

        return view('certifications/show', compact('certification', 'harvest'));
    }

    public function createReport(Certification $certification)
    {
        $certification->load('harvest.plant.type', 'harvest.location.baseLocation');
        $harvests = Harvest::with('plant.type')->orderBy('harvested_at', 'desc')->get();
        
        return view('certifications/reports/create', compact('certification', 'harvests'));
    }

    public function storeReport(Request $request, Certification $certification)
    {
        $data = $request->validate([
            'report_number_bpsb' => 'nullable|string|max:255',
            'report_date' => 'required|date',
            'growing_season' => 'nullable|string|max:255',
            'inspection_phase' => 'required|string|in:Vegetatif,Generatif,Menjelang Panen,Lainnya',
            'inspector_name' => 'nullable|string|max:255',
            'seed_class_result' => 'nullable|string|in:BS,BP,BR',
            'isolation_north' => 'nullable|string|max:255',
            'isolation_east' => 'nullable|string|max:255',
            'isolation_south' => 'nullable|string|max:255',
            'isolation_west' => 'nullable|string|max:255',
            'plant_characteristics_match' => 'nullable|boolean',
            'pest_disease_condition' => 'nullable|string',
            'weed_condition' => 'nullable|string|in:Bersih,Cukup Bersih,Kotor',
            'population_per_sample' => 'nullable|integer|min:0',
            'other_variety_mix_count' => 'nullable|integer|min:0',
            'other_variety_mix_percentage' => 'nullable|numeric|min:0|max:100',
            'estimated_yield' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'conclusion' => 'required|string|in:LULUS,TIDAK LULUS',
            'scan_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
        ]);

        // Handle file upload
        if ($request->hasFile('scan_file')) {
            $file = $request->file('scan_file');
            $fileName = 'certification_reports/' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('public', $fileName);
            $data['scan_file_path'] = $fileName;
        }

        // Add certification_id
        $data['certification_id'] = $certification->id;

        // Remove scan_file from data array (we already processed it)
        unset($data['scan_file']);

        $report = CertificationReport::create($data);

        // Update certification status based on conclusion
        if ($report->conclusion === 'LULUS') {
            $certification->update(['certification_status' => 'lulus']);
        } else {
            $certification->update(['certification_status' => 'tidak_lulus']);
        }

        return redirect()->route('certifications.show', $certification->harvest)
            ->with('success', 'Laporan pemeriksaan berhasil ditambahkan');
    }

    public function showReport(CertificationReport $report)
    {
        $report->load('certification.harvest.plant.type', 'certification.harvest.location.baseLocation');
        return view('certifications/reports/show', compact('report'));
    }

    public function create(Request $request)
    {
        // Get all planting locations for dropdown
        $plantingLocations = PlantingLocation::with('baseLocation')->orderBy('name')->get();
        
        // Get all plants (benih) for dropdown
        $plants = Plant::with('type')->orderBy('name')->get();
        
        // Get all harvests for reference (if needed)
        $harvests = Harvest::with(['plant.type', 'location', 'planting'])
            ->orderBy('harvested_at', 'desc')
            ->limit(100)
            ->get();
        
        // If planting_location_id or plant_id is provided in query, pre-select them
        $selectedPlantingLocationId = $request->get('planting_location_id');
        $selectedPlantId = $request->get('plant_id');

        return view('certifications/create', compact('plantingLocations', 'plants', 'harvests', 'selectedPlantingLocationId', 'selectedPlantId'));
    }

    public function store(Request $request)
    {
        // Validate certification and report data
        $data = $request->validate([
            'planting_location_id' => 'required|exists:planting_locations,id',
            'plant_id' => 'required|exists:plants,id',
            'seed_class_requested' => 'nullable|string|in:BS,BP,BR',
            
            // Report data
            'report_number_bpsb' => 'nullable|string|max:255',
            'report_date' => 'required|date',
            'growing_season' => 'nullable|string|max:255',
            'inspection_phase' => 'required|string|in:Vegetatif,Generatif,Menjelang Panen,Lainnya',
            'inspector_name' => 'nullable|string|max:255',
            'seed_class_result' => 'nullable|string|in:BS,BP,BR',
            'isolation_north' => 'nullable|string|max:255',
            'isolation_east' => 'nullable|string|max:255',
            'isolation_south' => 'nullable|string|max:255',
            'isolation_west' => 'nullable|string|max:255',
            'plant_characteristics_match' => 'nullable|boolean',
            'pest_disease_condition' => 'nullable|string',
            'weed_condition' => 'nullable|string|in:Bersih,Cukup Bersih,Kotor',
            'population_per_sample' => 'nullable|integer|min:0',
            'other_variety_mix_count' => 'nullable|integer|min:0',
            'other_variety_mix_percentage' => 'nullable|numeric|min:0|max:100',
            'estimated_yield' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'conclusion' => 'required|string|in:LULUS,TIDAK LULUS',
            'scan_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Find or create a harvest for this combination (optional, for backward compatibility)
        $plantingLocation = PlantingLocation::findOrFail($request->planting_location_id);
        $plant = Plant::findOrFail($request->plant_id);
        
        // Try to find a harvest for this plant and location, or create a placeholder
        $harvest = Harvest::where('plant_id', $plant->id)
            ->where('planting_location_id', $plantingLocation->id)
            ->orderBy('harvested_at', 'desc')
            ->first();
        
        // If no harvest exists, create a placeholder one
        if (!$harvest) {
            $harvest = Harvest::create([
                'plant_id' => $plant->id,
                'planting_location_id' => $plantingLocation->id,
                'harvested_at' => now(),
                'batch_no' => 'CERT-' . date('Y') . '-' . str_pad(Harvest::whereYear('harvested_at', date('Y'))->count() + 1, 3, '0', STR_PAD_LEFT),
                'quantity' => 0,
                'unit' => 'kg',
                'source' => $plantingLocation->name,
            ]);
        }

        // Create or get certification
        $certification = Certification::firstOrCreate(
            [
                'harvest_id' => $harvest->id,
                'planting_location_id' => $plantingLocation->id,
                'plant_id' => $plant->id,
            ],
            [
                'certification_status' => 'dalam_proses',
                'seed_class_requested' => $request->seed_class_requested ?: 'BP',
            ]
        );
        
        // Update planting_location_id and plant_id if certification already exists
        if (!$certification->wasRecentlyCreated) {
            $certification->update([
                'planting_location_id' => $plantingLocation->id,
                'plant_id' => $plant->id,
            ]);
        }

        // Prepare report data
        $reportData = $request->only([
            'report_number_bpsb',
            'report_date',
            'growing_season',
            'inspection_phase',
            'inspector_name',
            'seed_class_result',
            'isolation_north',
            'isolation_east',
            'isolation_south',
            'isolation_west',
            'plant_characteristics_match',
            'pest_disease_condition',
            'weed_condition',
            'population_per_sample',
            'other_variety_mix_count',
            'other_variety_mix_percentage',
            'estimated_yield',
            'expiry_date',
            'conclusion',
        ]);

        // Handle file upload
        if ($request->hasFile('scan_file')) {
            $file = $request->file('scan_file');
            $fileName = 'certification_reports/' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('public', $fileName);
            $reportData['scan_file_path'] = $fileName;
        }

        $reportData['certification_id'] = $certification->id;
        $report = CertificationReport::create($reportData);

        // Update certification status based on conclusion
        if ($report->conclusion === 'LULUS') {
            $certification->update(['certification_status' => 'lulus']);
        } else {
            $certification->update(['certification_status' => 'tidak_lulus']);
        }

        // Redirect to certification show page using certification
        return redirect()->route('certifications.show', $harvest)
            ->with('success', 'Sertifikasi dan laporan pemeriksaan berhasil ditambahkan');
    }
}

