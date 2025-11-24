<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\ProductionTarget;
use App\Models\PlantingLocation;
use App\Models\Planting;
use App\Models\Harvest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanningController extends Controller
{
    /**
     * Display the planning module index page
     */
    public function index()
    {
        return view('planning.index');
    }

    /**
     * A. RENCANA ANGGARAN (DPA)
     */

    /**
     * Display budget planning page
     */
    public function budgetIndex(Request $request)
    {
        $year = $request->get('year', date('Y'));

        // Get or create budget for the year
        $budget = Budget::firstOrCreate(
            ['fiscal_year' => $year],
            [
                'account_code' => null,
                'account_name' => null,
            ]
        );

        // Load all items with hierarchy
        $items = $budget->allItems()->with(['children' => function($q) {
            $q->orderBy('order');
        }])->orderBy('order')->get();

        // Organize items in tree structure
        $treeItems = $items->whereNull('parent_id');

        // Get available years
        $years = Budget::select('fiscal_year')
            ->distinct()
            ->orderBy('fiscal_year', 'desc')
            ->pluck('fiscal_year');

        // Get parent account codes for dropdown
        $parentAccounts = [
            '5.1.02' => 'Belanja Barang Jasa',
            '5.1.03' => 'Belanja Modal',
            '5.1.04' => 'Belanja Bantuan Sosial',
            // Add more as needed
        ];

        return view('planning.budget.index', compact('budget', 'treeItems', 'items', 'years', 'parentAccounts'));
    }

    /**
     * Show form to create/edit budget item
     */
    public function budgetItemCreate(Request $request)
    {
        $budgetId = $request->get('budget_id');
        $parentId = $request->get('parent_id');
        $budget = Budget::findOrFail($budgetId);

        $parentAccounts = [
            '5.1.02' => 'Belanja Barang Jasa',
            '5.1.03' => 'Belanja Modal',
            '5.1.04' => 'Belanja Bantuan Sosial',
        ];

        $fundSources = ['APBD', 'APBN', 'Swadaya', 'Hibah'];

        $parentItem = $parentId ? BudgetItem::find($parentId) : null;
        $maxOrder = BudgetItem::where('budget_id', $budgetId)
            ->where('parent_id', $parentId)
            ->max('order') ?? 0;

        return view('planning.budget.create', compact('budget', 'parentItem', 'parentAccounts', 'fundSources', 'maxOrder'));
    }

    /**
     * Store budget item
     */
    public function budgetItemStore(Request $request)
    {
        $data = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'parent_id' => 'nullable|exists:budget_items,id',
            'parent_account_code' => 'nullable|string|max:255',
            'account_code' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'budgeted_amount' => 'required|numeric|min:0',
            'fund_source' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $budget = Budget::findOrFail($data['budget_id']);

        // If parent_account_code is provided, create or update parent budget record
        if ($request->filled('parent_account_code')) {
            $budget->account_code = $request->parent_account_code;
            $budget->account_name = $request->parent_account_name ?? '';
            $budget->save();
        }

        // Determine level
        $level = 0;
        if ($data['parent_id']) {
            $parent = BudgetItem::find($data['parent_id']);
            $level = $parent->level + 1;
        }

        $data['level'] = $level;
        $data['order'] = $data['order'] ?? ($maxOrder = BudgetItem::where('budget_id', $data['budget_id'])
            ->where('parent_id', $data['parent_id'])
            ->max('order') ?? 0) + 1;

        unset($data['parent_account_code']);

        BudgetItem::create($data);

        return redirect()->route('planning.budget.index', ['year' => $budget->fiscal_year])
            ->with('success', 'Mata anggaran berhasil ditambahkan');
    }

    /**
     * Show form to edit budget item
     */
    public function budgetItemEdit(BudgetItem $budgetItem)
    {
        $budget = $budgetItem->budget;

        $parentAccounts = [
            '5.1.02' => 'Belanja Barang Jasa',
            '5.1.03' => 'Belanja Modal',
            '5.1.04' => 'Belanja Bantuan Sosial',
        ];

        $fundSources = ['APBD', 'APBN', 'Swadaya', 'Hibah'];

        return view('planning.budget.edit', compact('budgetItem', 'budget', 'parentAccounts', 'fundSources'));
    }

    /**
     * Update budget item
     */
    public function budgetItemUpdate(Request $request, BudgetItem $budgetItem)
    {
        $data = $request->validate([
            'account_code' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'budgeted_amount' => 'required|numeric|min:0',
            'fund_source' => 'nullable|string|max:255',
            'realized_amount' => 'nullable|numeric|min:0',
        ]);

        $budgetItem->update($data);

        return redirect()->route('planning.budget.index', ['year' => $budgetItem->budget->fiscal_year])
            ->with('success', 'Mata anggaran berhasil diperbarui');
    }

    /**
     * Delete budget item
     */
    public function budgetItemDestroy(BudgetItem $budgetItem)
    {
        $year = $budgetItem->budget->fiscal_year;
        
        // Check if item has children
        if ($budgetItem->children()->count() > 0) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus mata anggaran yang memiliki sub-item. Hapus sub-item terlebih dahulu.']);
        }

        $budgetItem->delete();

        return redirect()->route('planning.budget.index', ['year' => $year])
            ->with('success', 'Mata anggaran berhasil dihapus');
    }

    /**
     * B. TARGET PRODUKSI
     */

    /**
     * Display production target page
     */
    public function productionTargetIndex(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $commodity = $request->get('commodity');
        $locationId = $request->get('location_id');

        $query = ProductionTarget::with('plantingLocation')
            ->where('fiscal_year', $year);

        if ($commodity) {
            $query->where('commodity', $commodity);
        }

        if ($locationId) {
            $query->where('planting_location_id', $locationId);
        }

        $targets = $query->orderBy('commodity')->orderBy('variety_name')->get();

        // Update realized values from actual plantings and harvests
        foreach ($targets as $target) {
            $this->updateRealizedValues($target);
        }

        $locations = PlantingLocation::orderBy('name')->get();
        $commodities = ProductionTarget::select('commodity')
            ->distinct()
            ->orderBy('commodity')
            ->pluck('commodity');

        $years = ProductionTarget::select('fiscal_year')
            ->distinct()
            ->orderBy('fiscal_year', 'desc')
            ->pluck('fiscal_year');

        if (!$years->contains(date('Y'))) {
            $years->prepend(date('Y'));
        }

        return view('planning.production-target.index', compact('targets', 'locations', 'commodities', 'years'));
    }

    /**
     * Show form to create production target
     */
    public function productionTargetCreate(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $locations = PlantingLocation::orderBy('name')->get();
        $commodities = ['Padi', 'Palawija', 'Hortikultura'];

        return view('planning.production-target.create', compact('year', 'locations', 'commodities'));
    }

    /**
     * Store production target
     */
    public function productionTargetStore(Request $request)
    {
        $data = $request->validate([
            'fiscal_year' => 'required|integer|min:2020|max:2100',
            'commodity' => 'required|string|max:255',
            'variety_name' => 'required|string|max:255',
            'seed_class' => 'required|in:BS,BP,BR',
            'planting_location_id' => 'nullable|exists:planting_locations,id',
            'target_planting_area' => 'required|numeric|min:0',
            'target_production_volume' => 'required|numeric|min:0',
            'estimated_productivity' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Auto-calculate estimated_productivity if not provided
        if (empty($data['estimated_productivity']) && $data['target_planting_area'] > 0) {
            $data['estimated_productivity'] = $data['target_production_volume'] / $data['target_planting_area'];
        }

        ProductionTarget::create($data);

        return redirect()->route('planning.production-target.index', ['year' => $data['fiscal_year']])
            ->with('success', 'Target produksi berhasil ditambahkan');
    }

    /**
     * Show form to edit production target
     */
    public function productionTargetEdit(ProductionTarget $productionTarget)
    {
        $locations = PlantingLocation::orderBy('name')->get();
        $commodities = ['Padi', 'Palawija', 'Hortikultura'];

        return view('planning.production-target.edit', compact('productionTarget', 'locations', 'commodities'));
    }

    /**
     * Update production target
     */
    public function productionTargetUpdate(Request $request, ProductionTarget $productionTarget)
    {
        $data = $request->validate([
            'commodity' => 'required|string|max:255',
            'variety_name' => 'required|string|max:255',
            'seed_class' => 'required|in:BS,BP,BR',
            'planting_location_id' => 'nullable|exists:planting_locations,id',
            'target_planting_area' => 'required|numeric|min:0',
            'target_production_volume' => 'required|numeric|min:0',
            'estimated_productivity' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Auto-calculate estimated_productivity if not provided
        if (empty($data['estimated_productivity']) && $data['target_planting_area'] > 0) {
            $data['estimated_productivity'] = $data['target_production_volume'] / $data['target_planting_area'];
        }

        $productionTarget->update($data);

        return redirect()->route('planning.production-target.index', ['year' => $productionTarget->fiscal_year])
            ->with('success', 'Target produksi berhasil diperbarui');
    }

    /**
     * Delete production target
     */
    public function productionTargetDestroy(ProductionTarget $productionTarget)
    {
        $year = $productionTarget->fiscal_year;
        $productionTarget->delete();

        return redirect()->route('planning.production-target.index', ['year' => $year])
            ->with('success', 'Target produksi berhasil dihapus');
    }

    /**
     * Helper method to update realized values from actual data
     */
    private function updateRealizedValues(ProductionTarget $target)
    {
        $year = $target->fiscal_year;

        // Calculate realized planting area from plantings
        $realizedArea = Planting::whereYear('planted_at', $year)
            ->whereHas('plant', function($q) use ($target) {
                $q->where('variety', 'like', '%' . $target->variety_name . '%')
                  ->orWhere('name', 'like', '%' . $target->variety_name . '%');
            })
            ->when($target->planting_location_id, function($q) use ($target) {
                $q->where('planting_location_id', $target->planting_location_id);
            })
            ->get()
            ->sum(function($planting) {
                return $planting->location->map_size ?? 0;
            });

        // Calculate realized production volume from harvests
        $realizedVolume = Harvest::whereYear('harvested_at', $year)
            ->whereHas('plant', function($q) use ($target) {
                $q->where('variety', 'like', '%' . $target->variety_name . '%')
                  ->orWhere('name', 'like', '%' . $target->variety_name . '%');
            })
            ->when($target->planting_location_id, function($q) use ($target) {
                $q->where('planting_location_id', $target->planting_location_id);
            })
            ->get()
            ->sum(function($harvest) {
                // Convert to ton
                $unit = strtolower($harvest->unit ?? 'kg');
                $factors = ['kg' => 0.001, 'kilogram' => 0.001, 'gram' => 0.000001, 'ton' => 1, 'kuintal' => 0.1];
                return $harvest->quantity * ($factors[$unit] ?? 1);
            });

        $target->realized_planting_area = round($realizedArea, 2);
        $target->realized_production_volume = round($realizedVolume, 2);
        $target->save();
    }
}
