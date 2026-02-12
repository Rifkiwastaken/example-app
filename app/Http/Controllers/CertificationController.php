<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\CertificationReport;
use App\Models\Harvest;
use App\Models\InventoryType;
use App\Models\Plant;
use App\Models\Planting;
use App\Models\PlantingLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CertificationController extends Controller
{
    public function index(Request $request)
    {
        // Tanaman yang tampil: punya sertifikasi ATAU punya panen (panen siap disertifikasi)
        // sehingga record tanaman otomatis muncul jika ada data panen yang akan disertifikasi
        $query = Plant::with(['type'])
            ->withCount(['harvests as harvests_ready_for_cert_count' => function ($q) {
                $q->whereDoesntHave('certification');
            }])
            ->where(function ($q) {
                $q->whereHas('certifications')
                    ->orWhereHas('harvests');
            });

        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }

        $plants = $query->orderBy('name')->get();

        // Dropdown filter: tanaman yang punya sertifikasi atau panen
        $allPlants = Plant::with('type')
            ->where(function ($q) {
                $q->whereHas('certifications')->orWhereHas('harvests');
            })
            ->orderBy('name')
            ->get();

        return view('certifications/index', compact('plants', 'allPlants'));
    }

    /**
     * Show all certifications for a specific plant from all planting locations
     */
    public function showByPlant(Plant $plant, Request $request)
    {
        $plant->load(['type']);
        
        // Get filter parameters
        $locationFilter = $request->get('location_filter');
        $statusFilter = $request->get('status_filter');
        $stockStatusFilter = $request->get('stock_status_filter');
        
        // Get all certifications for this plant from all planting locations
        $certifications = Certification::where('plant_id', $plant->plant_id)
            ->with([
                'harvest.plant.type',
                'harvest.planting',
                'harvest.location',
                'plantingLocation',
                'reports' => function($q) {
                    $q->with('inventoryTypes')
                      ->orderBy('report_date', 'desc');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all reports from all certifications for this plant
        $allReportsQuery = CertificationReport::whereHas('certification', function($query) use ($plant) {
                $query->where('plant_id', $plant->plant_id);
            })
            ->with([
                'certification.harvest.location',
                'certification.plantingLocation',
                'certification.harvest.plant.type',
                'inventoryTypes'
            ]);
        
        // Apply filters
        if ($locationFilter) {
            $allReportsQuery->whereHas('certification', function($query) use ($locationFilter) {
                $query->where(function($q) use ($locationFilter) {
                    $q->where('planting_location_id', $locationFilter)
                      ->orWhereHas('harvest', function($harvestQuery) use ($locationFilter) {
                          $harvestQuery->where('planting_location_id', $locationFilter);
                      });
                });
            });
        }
        
        if ($statusFilter) {
            $allReportsQuery->whereHas('certification', function($query) use ($statusFilter) {
                $query->where('certification_status', $statusFilter);
            });
        }
        
        if ($stockStatusFilter === 'telah_ditambahkan') {
            $allReportsQuery->whereHas('inventoryTypes')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('inventory_type_seeds')
                        ->whereColumn('inventory_type_seeds.certification_report_id', 'certification_reports.certification_report_id');
                });
        } elseif ($stockStatusFilter === 'telah_dihapus') {
            $allReportsQuery->whereHas('inventoryTypes')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('inventory_type_seeds')
                        ->whereColumn('inventory_type_seeds.certification_report_id', 'certification_reports.certification_report_id');
                });
        } elseif ($stockStatusFilter === 'belum_ditambahkan') {
            $allReportsQuery->whereDoesntHave('inventoryTypes');
        }
        
        $allReports = $allReportsQuery->orderBy('report_date', 'desc')->get();
        
        // Force refresh all reports to ensure inventoryTypes relationship is loaded correctly
        foreach ($allReports as $report) {
            $report->load('inventoryTypes');
        }
        
        // Panen "belum disertifikasi" = belum ada laporan tersimpan (Simpan Laporan)
        // Termasuk: panen tanpa certification, atau punya certification tapi belum punya report
        $harvestsWithoutCertification = Harvest::where('plant_id', $plant->plant_id)
            ->with([
                'certification' => function ($q) {
                    $q->withCount('reports');
                },
                'plant.type',
                'planting',
                'location'
            ])
            ->orderBy('harvested_at', 'desc')
            ->get()
            ->filter(function ($harvest) {
                if (!$harvest->certification) {
                    return true;
                }
                return $harvest->certification->reports_count === 0;
            })
            ->values();
        
        // Get all inventory types for "Tambahkan ke Stok" modal
        $inventoryTypes = InventoryType::where('plant_id', $plant->plant_id)
            ->orderBy('name')
            ->get();
        
        // Get all planting locations for filter
        // Get locations that have plantings with this plant_id
        $locationIdsFromPlantings = PlantingLocation::whereHas('plantings', function($query) use ($plant) {
                $query->where('plant_id', $plant->plant_id);
            })
            ->pluck('planting_location_id');
        
        // Get locations that have harvests with this plant_id
        $locationIdsFromHarvests = Harvest::where('plant_id', $plant->plant_id)
            ->whereNotNull('planting_location_id')
            ->distinct()
            ->pluck('planting_location_id');
        
        // Combine and get unique location IDs
        $allLocationIds = $locationIdsFromPlantings->merge($locationIdsFromHarvests)->unique();
        
        // Get all planting locations (use custom primary key)
        $allPlantingLocations = PlantingLocation::whereIn('planting_location_id', $allLocationIds)
            ->orderBy('name')
            ->get();
        
        // Get data needed for add-to-stock form (same as seed-stock show page)
        $users = \App\Models\User::orderBy('name')->get();
        $plantTypes = \App\Models\PlantType::orderBy('name')->get();
        $plantingLocations = PlantingLocation::orderBy('name')->get();
        $plants = Plant::orderBy('name')->get();

        return view('certifications/by-plant', compact(
            'plant', 
            'certifications', 
            'allReports', 
            'harvestsWithoutCertification', 
            'inventoryTypes',
            'allPlantingLocations',
            'locationFilter',
            'statusFilter',
            'stockStatusFilter',
            'users',
            'plantTypes',
            'plantingLocations',
            'plants'
        ));
    }

    public function show(Harvest $harvest)
    {
        // Get or create certification for this harvest if it doesn't exist
        $certification = Certification::firstOrCreate(
            ['harvest_id' => $harvest->harvest_id],
            [
                'plant_id' => $harvest->plant_id,
                'planting_location_id' => $harvest->planting_location_id,
                'certification_status' => 'dalam_proses',
                'seed_class_requested' => 'BP', // Default
            ]
        );

        // Tampilkan form input laporan sertifikasi benih untuk panen ini
        return redirect()->route('certifications.reports.create', $certification);

        // Always get fresh data from database to ensure latest reports are loaded
        // Query directly from database to bypass any caching
        // Use fresh query to ensure we get all reports including newly created ones
        $certificationId = $certification->certification_id;
        
        // Clear any cached relationships and get fresh data
        $certification = Certification::withoutGlobalScopes()
            ->with([
                'harvest.plant.type',
                'harvest.planting',
                'plant.type',
                'reports' => function($q) {
                    // Load ALL reports without any filters - order by date and created_at
                    $q->orderBy('report_date', 'desc')
                      ->orderBy('created_at', 'desc'); // Also order by created_at to ensure newest first
                },
                'reports.inventoryTypes'
            ])
            ->findOrFail($certificationId);
        
        // Force reload reports relationship to ensure we have the latest data
        $certification->load('reports');
        
        // Double-check: Verify all reports are loaded by querying directly
        // Use fresh() to ensure we get the latest data from database
        $allReports = CertificationReport::where('certification_id', $certification->certification_id)
            ->orderBy('report_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->with('inventoryTypes')
            ->get();
        
        // Always use the directly queried reports to ensure we have all data
        $certification->setRelation('reports', $allReports);
        
        // Force refresh to ensure relationships are loaded correctly
        $certification->refresh();

        // Get all inventory types for dropdown
        $inventoryTypes = InventoryType::orderBy('name')->get();
        
        // Get data needed for add-to-stock form (same as seed-stock show page)
        $users = \App\Models\User::orderBy('name')->get();
        $plantTypes = \App\Models\PlantType::orderBy('name')->get();
        $plantingLocations = PlantingLocation::orderBy('name')->get();
        $plants = Plant::orderBy('name')->get();

        return view('certifications/show', compact('certification', 'harvest', 'inventoryTypes', 'users', 'plantTypes', 'plantingLocations', 'plants'));
    }

    public function createReport(Certification $certification)
    {
        $certification->load('harvest.plant.type');
        $harvests = Harvest::with('plant.type')->orderBy('harvested_at', 'desc')->get();
        
        return view('certifications/reports/create', compact('certification', 'harvests'));
    }

    public function storeReport(Request $request, Certification $certification)
    {
        // Auto-generate report_number_bpsb if not provided or empty
        $reportNumberBpsb = trim($request->input('report_number_bpsb', ''));
        if (empty($reportNumberBpsb)) {
            $year = date('Y');
            $reportCount = CertificationReport::whereYear('report_date', $year)->count() + 1;
            $reportNumberBpsb = 'BPSB-' . $year . '-' . str_pad($reportCount, 6, '0', STR_PAD_LEFT);
            
            // Ensure uniqueness by checking if report number already exists
            while (CertificationReport::where('report_number_bpsb', $reportNumberBpsb)->exists()) {
                $reportCount++;
                $reportNumberBpsb = 'BPSB-' . $year . '-' . str_pad($reportCount, 6, '0', STR_PAD_LEFT);
            }
            
            // Merge the generated report number back to request
            $request->merge(['report_number_bpsb' => $reportNumberBpsb]);
        }
        
        $data = $request->validate([
            'report_type' => 'nullable|string|max:255',
            'report_number_bpsb' => 'required|string|max:255|unique:certification_reports,report_number_bpsb',
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
            'expiry_date' => 'required|date',
            'certified_seed_quantity' => 'nullable|numeric|min:0',
            'certified_seed_unit' => 'nullable|string|in:kg,ton,gram,butir,pcs,batang',
            'seed_unit' => 'nullable|string|in:kg,ton,gram,butir,pcs,batang',
            'estimated_sale_price_per_kg' => 'nullable|numeric|min:0',
            'conclusion' => 'required|string|in:LULUS,TIDAK LULUS',
            'scan_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
            'renew_from_report_id' => 'nullable|exists:certification_reports,certification_report_id',
        ]);

        // Auto-fill reporter_name with current user
        $data['reporter_name'] = auth()->user()->name;
        
        // Handle sertifikasi ulang - create new certification if renew_from_report_id exists
        if ($request->has('renew_from_report_id') && $request->renew_from_report_id) {
            // Get the old report to get harvest information
            $oldReport = CertificationReport::with('certification.harvest')->findOrFail($request->renew_from_report_id);
            $oldHarvest = $oldReport->certification->harvest;
            
            // Create a new certification for the same harvest (new certification, not linked to old one)
            $newCertification = Certification::firstOrCreate(
                [
                    'harvest_id' => $oldHarvest->harvest_id,
                    'plant_id' => $oldHarvest->plant_id,
                    'planting_location_id' => $oldHarvest->planting_location_id,
                ],
                [
                    'certification_status' => 'dalam_proses',
                    'seed_class_requested' => 'BP', // Default
                ]
            );
            
            // Use the new certification instead of the old one
            $certification = $newCertification;
            $data['report_type'] = 'Laporan Sertifikasi Ulang';
        } elseif ($request->has('report_type') && $request->report_type === 'Laporan Sertifikasi Ulang') {
            // Also check if report_type is explicitly set to "Laporan Sertifikasi Ulang"
            $data['report_type'] = 'Laporan Sertifikasi Ulang';
        } else {
            // Set default report_type if not provided
            if (!isset($data['report_type']) || empty($data['report_type'])) {
                $data['report_type'] = 'Laporan Pemeriksaan Pertanaman';
            }
        }

        // Handle file upload
        if ($request->hasFile('scan_file')) {
            $file = $request->file('scan_file');
            $fileName = 'certification_reports/' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('public', $fileName);
            $data['scan_file_path'] = $fileName;
        }

        // Add certification_id
        $data['certification_id'] = $certification->certification_id;

        // Remove scan_file from data array (we already processed it)
        unset($data['scan_file']);

        // Ensure reporter_name is set
        if (!isset($data['reporter_name'])) {
            $data['reporter_name'] = auth()->user()->name;
        }

        // Use DB transaction to ensure data integrity
        DB::beginTransaction();
        try {
            $report = CertificationReport::create($data);

            // Verify report was created successfully and has correct certification_id
            if (!$report || !$report->certification_report_id || $report->certification_id != $certification->certification_id) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Gagal menyimpan laporan sertifikasi.'])->withInput();
            }

            // Update certification status based on conclusion
            if ($report->conclusion === 'LULUS') {
                $certification->update(['certification_status' => 'lulus']);
            } else {
                $certification->update(['certification_status' => 'tidak_lulus']);
            }

            // Commit transaction
            DB::commit();
            
            // Reload report from database to ensure we have fresh data
            $report->refresh();
            
            // Reload certification to ensure we have the latest data including new report
            $certification->refresh();
            
            // Get harvest from certification - ensure it exists
            $harvest = $certification->harvest;
            if (!$harvest) {
                return back()->withErrors(['error' => 'Harvest tidak ditemukan untuk sertifikasi ini.'])->withInput();
            }
            
            // Verify the report exists in database and is linked to the correct certification
            $verifyReport = CertificationReport::where('certification_report_id', $report->certification_report_id)
                ->where('certification_id', $certification->certification_id)
                ->first();
            
            if (!$verifyReport) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Laporan tidak terhubung dengan sertifikasi yang benar.'])->withInput();
            }
            
            // Final verification: Check that the report appears in certification's reports
            $certification->refresh();
            $reportsCount = $certification->reports()->count();
            $reportExists = $certification->reports()->where('certification_report_id', $report->certification_report_id)->exists();
            
            if (!$reportExists) {
                // Force reload relationship
                $certification->load('reports');
                $reportExists = $certification->reports->contains('certification_report_id', $report->certification_report_id);
            }
            
            // Get harvest from certification - ensure it exists
            $harvest = $certification->harvest;
            if (!$harvest) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Harvest tidak ditemukan untuk sertifikasi ini.'])->withInput();
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan laporan: ' . $e->getMessage()])->withInput();
        }
        
        // Get harvest from certification for redirect
        $harvest = $certification->harvest;
        
        // Redirect to by-plant page (main kelola sertifikasi page)
        if ($harvest && $harvest->plant_id) {
            $successMessage = $request->has('renew_from_report_id') && $request->renew_from_report_id
                ? 'Sertifikasi ulang berhasil dibuat. Laporan sertifikasi ulang berhasil ditambahkan. Sertifikasi baru dapat ditambahkan ke stok.'
                : 'Laporan pemeriksaan berhasil ditambahkan';
            
            return redirect()->route('certifications.by-plant', $harvest->plant)
                ->with('success', $successMessage);
        }
        
        // Fallback redirect
        return redirect()->back()
            ->with('success', 'Laporan pemeriksaan berhasil ditambahkan');
    }

    public function showReport(CertificationReport $report)
    {
        $report->load([
            'certification.harvest.plant.type',
            'certification.harvest.location',
            'certification.harvest.planting',
            'certification'
        ]);
        return view('certifications/reports/show', compact('report'));
    }

    public function editReport(CertificationReport $report)
    {
        $report->load('certification.harvest.plant.type');
        return view('certifications/reports/edit', [
            'report' => $report,
            'certification' => $report->certification,
            'harvest' => $report->certification->harvest,
        ]);
    }

    public function updateReport(Request $request, CertificationReport $report)
    {
        $data = $request->validate([
            'report_type' => 'nullable|string|max:255',
            'report_number_bpsb' => 'required|string|max:255|unique:certification_reports,report_number_bpsb,' . $report->certification_report_id,
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
            'expiry_date' => 'required|date',
            'certified_seed_quantity' => 'nullable|numeric|min:0',
            'certified_seed_unit' => 'nullable|string|in:kg,ton,gram,butir,pcs,batang',
            'seed_unit' => 'nullable|string|in:kg,ton,gram,butir,pcs,batang',
            'estimated_sale_price_per_kg' => 'nullable|numeric|min:0',
            'conclusion' => 'required|string|in:LULUS,TIDAK LULUS',
            'scan_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Handle file upload replacement
        if ($request->hasFile('scan_file')) {
            $file = $request->file('scan_file');
            $fileName = 'certification_reports/' . time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public', $fileName);
            // Optionally delete old file
            if (!empty($report->scan_file_path)) {
                Storage::delete('public/' . $report->scan_file_path);
            }
            $data['scan_file_path'] = $fileName;
        }

        // Ensure reporter_name kept/updated
        if (empty($report->reporter_name)) {
            $data['reporter_name'] = Auth::user()->name ?? $report->reporter_name;
        }

        $report->update($data);

        // Update certification status based on conclusion
        $certification = $report->certification;
        if ($report->conclusion === 'LULUS') {
            $certification->update(['certification_status' => 'lulus']);
        } elseif ($report->conclusion === 'TIDAK LULUS') {
            $certification->update(['certification_status' => 'tidak_lulus']);
        }

        return redirect()->route('certifications.show', $certification->harvest)
            ->with('success', 'Laporan pemeriksaan berhasil diperbarui.');
    }

    public function destroyReport(CertificationReport $report)
    {
        $certification = $report->certification;
        // Delete file if exists
        if (!empty($report->scan_file_path)) {
            Storage::delete('public/' . $report->scan_file_path);
        }
        $report->delete();

        return redirect()->route('certifications.show', $certification->harvest)
            ->with('success', 'Laporan pemeriksaan berhasil dihapus.');
    }

    public function create(Request $request)
    {
        // Prevent penangkar from creating certifications
        if (auth()->user()->role === 'penangkar') {
            abort(403, 'Anda tidak memiliki izin untuk membuat sertifikasi.');
        }
        
        // Get all planting locations for dropdown
        $plantingLocations = PlantingLocation::orderBy('name')->get();
        
        // Get all plants (benih) for dropdown
        $plants = Plant::with('type')->orderBy('name')->get();
        
        // Get all harvests for reference (if needed)
        $harvests = Harvest::with(['plant.type', 'location', 'planting'])
            ->orderBy('harvested_at', 'desc')
            ->limit(100)
            ->get();
        
        // If harvest_id is provided, load harvest and pre-fill data
        $harvest = null;
        $selectedHarvestId = null;
        $selectedPlantingLocationId = $request->get('planting_location_id');
        $selectedPlantId = $request->get('plant_id');
        $redirectPlantId = $request->get('plant_id'); // For redirect after store (from plant harvests page)
        
        if ($request->has('harvest_id')) {
            $selectedHarvestId = $request->get('harvest_id');
            $harvest = Harvest::with(['plant.type', 'location', 'planting'])->find($selectedHarvestId);
            
            if ($harvest) {
                // Pre-fill from harvest data
                $selectedPlantingLocationId = $harvest->planting_location_id;
                $selectedPlantId = $harvest->plant_id;
                // Keep redirectPlantId if provided, otherwise use harvest plant_id
                if (!$redirectPlantId) {
                    $redirectPlantId = $harvest->plant_id;
                }
            }
        }

        return view('certifications/create', compact(
            'plantingLocations', 
            'plants', 
            'harvests', 
            'selectedPlantingLocationId', 
            'selectedPlantId',
            'harvest',
            'selectedHarvestId',
            'redirectPlantId'
        ));
    }

    public function store(Request $request)
    {
        // When form is filled from harvest, disabled selects don't submit — fill from harvest if missing
        if ($request->filled('harvest_id')) {
            $harvestForMerge = Harvest::find($request->harvest_id);
            if ($harvestForMerge) {
                if (!$request->filled('planting_location_id')) {
                    $request->merge(['planting_location_id' => $harvestForMerge->planting_location_id]);
                }
                if (!$request->filled('plant_id')) {
                    $request->merge(['plant_id' => $harvestForMerge->plant_id]);
                }
            }
        }

        // Auto-generate report_number_bpsb if not provided or empty
        $reportNumberBpsb = trim($request->input('report_number_bpsb', ''));
        if (empty($reportNumberBpsb)) {
            $year = date('Y');
            $reportCount = CertificationReport::whereYear('report_date', $year)->count() + 1;
            $reportNumberBpsb = 'BPSB-' . $year . '-' . str_pad($reportCount, 6, '0', STR_PAD_LEFT);
            
            // Ensure uniqueness by checking if report number already exists
            while (CertificationReport::where('report_number_bpsb', $reportNumberBpsb)->exists()) {
                $reportCount++;
                $reportNumberBpsb = 'BPSB-' . $year . '-' . str_pad($reportCount, 6, '0', STR_PAD_LEFT);
            }
            
            // Merge the generated report number back to request
            $request->merge(['report_number_bpsb' => $reportNumberBpsb]);
        }
        
        // Validate certification and report data
        $data = $request->validate([
            'harvest_id' => 'nullable|exists:harvests,harvest_id',
            'planting_location_id' => 'required|exists:planting_locations,planting_location_id',
            'plant_id' => 'required|exists:plants,plant_id',
            'seed_class_requested' => 'nullable|string|in:BS,BP,BR',
            
            // Report data
            'report_type' => 'nullable|string|max:255',
            'report_number_bpsb' => 'required|string|max:255|unique:certification_reports,report_number_bpsb',
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
            'expiry_date' => 'required|date',
            'certified_seed_quantity' => 'nullable|numeric|min:0',
            'certified_seed_unit' => 'nullable|string|in:kg,ton,gram,butir,pcs,batang',
            'seed_unit' => 'nullable|string|in:kg,ton,gram,butir,pcs,batang',
            'estimated_sale_price_per_kg' => 'nullable|numeric|min:0',
            'conclusion' => 'required|string|in:LULUS,TIDAK LULUS',
            'scan_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Find or create a harvest for this combination (optional, for backward compatibility)
        $plantingLocation = PlantingLocation::findOrFail($request->planting_location_id);
        $plant = Plant::findOrFail($request->plant_id);
        
        // If harvest_id is provided, use it; otherwise find or create
        if ($request->has('harvest_id') && $request->harvest_id) {
            $harvest = Harvest::findOrFail($request->harvest_id);
            
            // Verify harvest matches the provided plant and location
            if ($harvest->plant_id != $plant->plant_id || $harvest->planting_location_id != $plantingLocation->planting_location_id) {
                return redirect()->back()
                    ->withErrors(['harvest_id' => 'Harvest tidak sesuai dengan tanaman dan lokasi yang dipilih.'])
                    ->withInput();
            }
        } else {
            // Try to find a harvest for this plant and location, or create a placeholder
            $harvest = Harvest::where('plant_id', $plant->plant_id)
                ->where('planting_location_id', $plantingLocation->planting_location_id)
                ->orderBy('harvested_at', 'desc')
                ->first();
            
            // If no harvest exists, create a placeholder one
            if (!$harvest) {
                $harvest = Harvest::create([
                    'plant_id' => $plant->plant_id,
                    'planting_location_id' => $plantingLocation->planting_location_id,
                    'harvested_at' => now(),
                    'batch_no' => 'CERT-' . date('Y') . '-' . str_pad(Harvest::whereYear('harvested_at', date('Y'))->count() + 1, 3, '0', STR_PAD_LEFT),
                    'quantity' => 0,
                    'unit' => 'kg',
                    'source' => $plantingLocation->name,
                ]);
            }
        }

        // Create or get certification
        $certification = Certification::firstOrCreate(
            [
                'harvest_id' => $harvest->harvest_id,
                'planting_location_id' => $plantingLocation->planting_location_id,
                'plant_id' => $plant->plant_id,
            ],
            [
                'certification_status' => 'dalam_proses',
                'seed_class_requested' => $request->seed_class_requested ?: 'BP',
            ]
        );
        
        // Update planting_location_id and plant_id if certification already exists
        if (!$certification->wasRecentlyCreated) {
            $certification->update([
                'planting_location_id' => $plantingLocation->planting_location_id,
                'plant_id' => $plant->plant_id,
            ]);
        }

        // Prepare report data
        $reportData = $request->only([
            'report_type',
            'report_number_bpsb',
            'report_date',
            'growing_season',
            'inspection_phase',
            'inspector_name',
            'reporter_name',
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
            'certified_seed_quantity',
            'certified_seed_unit',
            'seed_unit',
            'estimated_sale_price_per_kg',
            'conclusion',
        ]);

        // Auto-fill reporter_name with current user
        $reportData['reporter_name'] = auth()->user()->name;
        
        // Set default report_type if not provided
        if (!isset($reportData['report_type']) || empty($reportData['report_type'])) {
            $reportData['report_type'] = 'Laporan Pemeriksaan Pertanaman';
        }

        // Handle file upload
        if ($request->hasFile('scan_file')) {
            $file = $request->file('scan_file');
            $fileName = 'certification_reports/' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('public', $fileName);
            $reportData['scan_file_path'] = $fileName;
        }

        $reportData['certification_id'] = $certification->certification_id;
        $report = CertificationReport::create($reportData);

        // Update certification status based on conclusion
        if ($report->conclusion === 'LULUS') {
            $certification->update(['certification_status' => 'lulus']);
        } else {
            $certification->update(['certification_status' => 'tidak_lulus']);
        }

        // If harvest_id was provided, check where to redirect
        // Note: 'plant_id' in request might be for redirect (from plant harvests page)
        $redirectPlantId = $request->get('plant_id');
        
        if ($request->has('harvest_id') && $request->harvest_id) {
            $harvestForRedirect = Harvest::find($request->harvest_id);
            if ($harvestForRedirect) {
                // Check if redirectPlantId exists and matches harvest plant_id (coming from plant harvests page)
                if ($redirectPlantId && $redirectPlantId == $harvestForRedirect->plant_id) {
                    // Redirect back to plant harvests page
                    return redirect()->route('plants.harvests.index', $redirectPlantId)
                        ->with('success', 'Sertifikasi berhasil dibuat. Laporan pemeriksaan berhasil ditambahkan.');
                } elseif ($harvestForRedirect->planting_location_id) {
                    // Redirect back to planting location
                    return redirect()->route('planting-locations.show', $harvestForRedirect->planting_location_id)
                        ->with('success', 'Sertifikasi berhasil dibuat. Laporan pemeriksaan berhasil ditambahkan.');
                }
            }
        }

        // Redirect to certification show page using certification
        return redirect()->route('certifications.show', $harvest)
            ->with('success', 'Sertifikasi dan laporan pemeriksaan berhasil ditambahkan');
    }

    /**
     * Redirect to inventory type seed page with pre-filled data from certification report
     */
    public function addToStock(Request $request, CertificationReport $report)
    {
        $request->validate([
            'inventory_type_id' => 'required|exists:inventory_types,inventory_type_id',
        ]);

        $inventoryType = InventoryType::findOrFail($request->inventory_type_id);
        
        // Load certification data
        $report->load([
            'certification.plant.type'
        ]);

        // Redirect to inventory type show page with pre-filled data
        return redirect()->route('seed-stock.show', [
            'inventoryType' => $inventoryType->inventory_type_id,
            'certification_report_id' => $report->certification_report_id,
            'prefill' => 'true',
            'tab' => 'certified-seeds' // Auto-open the "Data Benih" tab
        ]);
    }

    /**
     * Display all harvests ready for certification
     */
    public function harvestsIndex(Request $request)
    {
        // Get all harvests with their relationships
        $query = Harvest::with([
            'plant.type',
            'planting',
            'certification.reports.inventoryTypes'
        ]);

        // Filter by plant (komoditas) if provided
        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }

        // Filter by planting location if provided
        if ($request->filled('planting_location_id')) {
            $query->where('planting_location_id', $request->planting_location_id);
        }

        // Get all harvests first
        $harvests = $query->get();

        // Filter by certification status if provided
        if ($request->filled('certification_status')) {
            $statusFilter = $request->certification_status;
            $harvests = $harvests->filter(function($harvest) use ($statusFilter) {
                if ($statusFilter === 'belum_disertifikasi') {
                    return !$harvest->certification;
                } elseif ($statusFilter === 'dalam_proses') {
                    return $harvest->certification && $harvest->certification->certification_status === 'dalam_proses';
                } elseif ($statusFilter === 'lulus') {
                    return $harvest->certification && $harvest->certification->certification_status === 'lulus';
                } elseif ($statusFilter === 'sudah_melewati_masa_edar') {
                    if (!$harvest->certification) {
                        return false;
                    }
                    // Check if any report has expired
                    return $harvest->certification->reports->contains(function($report) {
                        return $report->expiry_date && $report->expiry_date->isPast();
                    });
                }
                return true;
            });
        }

        // Filter by stock status if provided
        if ($request->filled('stock_status')) {
            $stockFilter = $request->stock_status;
            $harvests = $harvests->filter(function($harvest) use ($stockFilter) {
                if (!$harvest->certification) {
                    return $stockFilter === 'belum_ditambahkan_ke_stok';
                }
                
                $hasPivot = $harvest->certification->reports->contains(fn($r) => $r->inventoryTypes->count() > 0);
                $hasSeedStill = $harvest->certification->reports->contains(function($report) {
                    if ($report->inventoryTypes->count() === 0) return false;
                    $ids = $report->inventoryTypes->pluck('inventory_type_id')->toArray();
                    return \App\Models\InventoryTypeSeed::where('certification_report_id', $report->certification_report_id)
                        ->whereIn('inventory_type_id', $ids)->exists();
                });
                $stockWasDeleted = $hasPivot && !$hasSeedStill;

                if ($stockFilter === 'telah_ditambahkan_ke_stok') {
                    return $hasSeedStill;
                } elseif ($stockFilter === 'telah_dihapus') {
                    return $stockWasDeleted;
                } elseif ($stockFilter === 'belum_ditambahkan_ke_stok') {
                    return !$hasPivot;
                }
                return true;
            });
        }

        // Sort: harvests without certification first, then by harvest date (newest first)
        $sortBy = $request->get('sort_by', 'harvested_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Custom ordering: belum disertifikasi di atas, kemudian terbaru di atas
        $harvests = $harvests->sortBy(function($harvest) use ($sortBy, $sortOrder) {
            // Priority 1: belum disertifikasi = 0, sudah disertifikasi = 1
            $certificationPriority = $harvest->certification ? 1 : 0;
            
            // Priority 2: Secondary sort by user selection or default to harvest date (newest first)
            $secondarySort = match($sortBy) {
                'plant_name' => $harvest->plant->name ?? '',
                'location_name' => $harvest->location->name ?? '',
                'harvested_at' => $harvest->harvested_at ? $harvest->harvested_at->timestamp : 0,
                default => $harvest->harvested_at ? $harvest->harvested_at->timestamp : 0,
            };

            // For timestamp (harvested_at), use negative for descending (newest first)
            // For string (plant_name, location_name), use prefix for descending
            if (is_numeric($secondarySort)) {
                // For dates/timestamps: negative value for descending (newest first)
                $sortValue = $sortOrder === 'desc' ? -$secondarySort : $secondarySort;
            } else {
                // For strings: use prefix 'z' for descending to reverse order
                $sortValue = $sortOrder === 'desc' ? 'z' . $secondarySort : $secondarySort;
            }

            return [$certificationPriority, $sortValue];
        });

        // Convert to paginated collection
        $currentPage = $request->get('page', 1);
        $perPage = 15;
        $items = $harvests->values()->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $harvestsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $harvests->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get all plants for filter dropdown
        $allPlants = Plant::with('type')->orderBy('name')->get();
        
        // Get all planting locations for filter dropdown
        $allPlantingLocations = PlantingLocation::orderBy('name')->get();

        return view('certifications/harvests-index', compact('harvestsPaginated', 'allPlants', 'allPlantingLocations'));
    }
}

