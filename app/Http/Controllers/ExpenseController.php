<?php

namespace App\Http\Controllers;

use App\Models\PlantingLocation;
use App\Models\Expense;
use App\Models\Plant;
use App\Models\Planting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    /**
     * Display all expenses with filters
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get filter parameters
        $year = $request->get('year', '');
        $month = $request->get('month', '');
        $plantId = $request->get('plant_id', '');
        $plantingLocationId = $request->get('planting_location_id', '');
        $plantingId = $request->get('planting_id', '');
        
        // Build query
        $query = Expense::with([
            'treatment.planting.plant.type', 
            'nutrient.planting.plant.type'
        ])
            ->orderBy('expense_date', 'desc');
        
        // Filter by year
        if ($year) {
            $query->whereYear('expense_date', $year);
        }
        
        // Filter by month
        if ($month) {
            $query->whereMonth('expense_date', $month);
        }
        
        // Filter by plant (from treatment or nutrient)
        if ($plantId) {
            $query->where(function($q) use ($plantId) {
                $q->whereHas('treatment.planting', function($q) use ($plantId) {
                    $q->where('plant_id', $plantId);
                })->orWhereHas('nutrient.planting', function($q) use ($plantId) {
                    $q->where('plant_id', $plantId);
                });
            });
        }
        
        // Filter by planting location
        if ($plantingLocationId) {
            $query->where('planting_location_id', $plantingLocationId);
        }
        
        // Filter by planting
        if ($plantingId) {
            $query->where('planting_id', $plantingId);
        }
        
        // Filter by user access if not admin
        if (!$user->isAdmin()) {
            if (in_array($user->role, ['kepala_satuan_tugas', 'penangkar'])) {
                $managedIds = $user->managedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $workedIds = $user->workedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $assignedLocationIds = array_unique(array_merge($managedIds, $workedIds));
                
                if (count($assignedLocationIds) > 0) {
                    $query->whereIn('planting_location_id', $assignedLocationIds);
                } else {
                    $query->whereRaw('1 = 0'); // No access
                }
            }
        }
        
        $expenses = $query->paginate(20)->withQueryString();
        
        // Calculate total expenses (from all expenses matching filters, not just current page)
        $totalExpensesQuery = Expense::query();
        
        // Apply same filters for total calculation
        if ($year) {
            $totalExpensesQuery->whereYear('expense_date', $year);
        }
        if ($month) {
            $totalExpensesQuery->whereMonth('expense_date', $month);
        }
        if ($plantId) {
            $totalExpensesQuery->where(function($q) use ($plantId) {
                $q->whereHas('treatment.planting', function($q) use ($plantId) {
                    $q->where('plant_id', $plantId);
                })->orWhereHas('nutrient.planting', function($q) use ($plantId) {
                    $q->where('plant_id', $plantId);
                });
            });
        }
        if ($plantingLocationId) {
            $totalExpensesQuery->where('planting_location_id', $plantingLocationId);
        }
        if ($plantingId) {
            $totalExpensesQuery->where('planting_id', $plantingId);
        }
        
        // Filter by user access if not admin
        if (!$user->isAdmin()) {
            if (in_array($user->role, ['kepala_satuan_tugas', 'penangkar'])) {
                $managedIds = $user->managedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $workedIds = $user->workedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $assignedLocationIds = array_unique(array_merge($managedIds, $workedIds));
                
                if (count($assignedLocationIds) > 0) {
                    $totalExpensesQuery->whereIn('planting_location_id', $assignedLocationIds);
                } else {
                    $totalExpensesQuery->whereRaw('1 = 0');
                }
            }
        }
        
        $totalExpenses = $totalExpensesQuery->sum('amount');
        
        // Get available years from expenses
        $availableYears = Expense::selectRaw('YEAR(expense_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        // Always include current year
        if (!$availableYears->contains(date('Y'))) {
            $availableYears = $availableYears->prepend(date('Y'))->sortDesc()->values();
        }
        
        // If no expenses, add current year
        if ($availableYears->isEmpty()) {
            $availableYears = collect([date('Y')]);
        }
        
        // Get all plants for filter
        $plants = Plant::with('type')->orderBy('name')->get();
        
        // Get all planting locations for filter
        $plantingLocationsQuery = PlantingLocation::orderBy('name');
        
        // Filter planting locations by user access if not admin
        if (!$user->isAdmin()) {
            if (in_array($user->role, ['kepala_satuan_tugas', 'penangkar'])) {
                $managedIds = $user->managedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $workedIds = $user->workedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                $assignedLocationIds = array_unique(array_merge($managedIds, $workedIds));
                
                if (count($assignedLocationIds) > 0) {
                    $plantingLocationsQuery->whereIn('id', $assignedLocationIds);
                } else {
                    $plantingLocationsQuery->whereRaw('1 = 0');
                }
            }
        }
        
        $plantingLocations = $plantingLocationsQuery->get();
        
        // Get all plantings for filter dropdown
        // If planting location is selected, show only plantings from that location
        // Otherwise, show all plantings
        $plantingsQuery = Planting::with(['plant', 'location']);
        
        if ($plantingLocationId) {
            $plantingsQuery->where('planting_location_id', $plantingLocationId);
        } else {
            // Filter by user access if not admin
            if (!$user->isAdmin()) {
                if (in_array($user->role, ['kepala_satuan_tugas', 'penangkar'])) {
                    $managedIds = $user->managedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                    $workedIds = $user->workedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                    $assignedLocationIds = array_unique(array_merge($managedIds, $workedIds));
                    
                    if (count($assignedLocationIds) > 0) {
                        $plantingsQuery->whereIn('planting_location_id', $assignedLocationIds);
                    } else {
                        $plantingsQuery->whereRaw('1 = 0');
                    }
                }
            }
        }
        
        $allPlantings = $plantingsQuery->orderBy('planted_at', 'desc')->get();
        
        return view('expenses/index', compact(
            'expenses',
            'totalExpenses',
            'year',
            'month',
            'plantId',
            'plantingLocationId',
            'plantingId',
            'availableYears',
            'plants',
            'plantingLocations',
            'allPlantings'
        ));
    }

    /**
     * Display expenses for a specific planting location (existing method)
     */
    public function show(PlantingLocation $plantingLocation, Request $request)
    {
        $user = auth()->user();
        
        // Check if user has access to this planting location
        if (!$user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        
        // Get filter parameters
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));
        
        // Build query
        $query = $plantingLocation->expenses()
            ->with(['treatment.planting.plant.type', 'nutrient.planting.plant.type'])
            ->orderBy('expense_date', 'desc');
        
        // Filter by year
        if ($year) {
            $query->whereYear('expense_date', $year);
        }
        
        // Filter by month
        if ($month) {
            $query->whereMonth('expense_date', $month);
        }
        
        $expenses = $query->get();
        
        // Calculate total expenses
        $totalExpenses = $expenses->sum('amount');
        
        // Get available years from expenses
        $availableYears = $plantingLocation->expenses()
            ->selectRaw('YEAR(expense_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        // Always include current year
        if (!$availableYears->contains(date('Y'))) {
            $availableYears = $availableYears->prepend(date('Y'))->sortDesc()->values();
        }
        
        // If no expenses, add current year
        if ($availableYears->isEmpty()) {
            $availableYears = collect([date('Y')]);
        }
        
        // Get months for selected year
        $availableMonths = [];
        if ($year) {
            $monthsInYear = $plantingLocation->expenses()
                ->whereYear('expense_date', $year)
                ->selectRaw('MONTH(expense_date) as month')
                ->distinct()
                ->orderBy('month', 'asc')
                ->pluck('month')
                ->map(function($month) {
                    return [
                        'value' => str_pad($month, 2, '0', STR_PAD_LEFT),
                        'label' => Carbon::create(null, $month)->locale('id')->monthName
                    ];
                });
            $availableMonths = $monthsInYear->toArray();
        }
        
        // Month names for dropdown
        $monthNames = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthNames[$i] = Carbon::create(null, $i)->locale('id')->monthName;
        }
        
        
        return view('planting/planting-locations/expenses/index', compact(
            'plantingLocation',
            'expenses',
            'totalExpenses',
            'year',
            'month',
            'availableYears',
            'availableMonths',
            'monthNames'
        ));
    }
}
