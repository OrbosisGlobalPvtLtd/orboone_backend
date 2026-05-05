<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\AssetAllocationM as AssetAllocation;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use Illuminate\Http\Request;

class AssetAllocationC extends Controller
{
    public function index(Request $request)
    {
        $query = AssetAllocation::with('employee');

        if ($request->has('employee_name') && $request->employee_name != '') {
            $query->whereHas('employee', function ($q) use ($request) {
                // Adjusting typical employee search: if there's user relation or first_name, we use that. 
                // Given standard HRMS: Employee might have first_name, last_name, or relate to User.
                // Assuming employee name might be stored differently depending on the schema (first_name, last_name or name)
                // Assuming Employee model might have a name field or User relation.
                // Looking at Employee model we saw before, it has 'user' relation. Let's assume user.name.
                $q->whereHas('user', function ($uq) use ($request) {
                    $uq->where('name', 'like', '%' . $request->employee_name . '%');
                })->orWhere('id', 'like', '%' . $request->employee_name . '%'); 
            });
        }

        if ($request->has('asset_type') && $request->asset_type != '') {
            $query->where('asset_type', $request->asset_type);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $assetAllocations = $query->latest()->paginate(10);
        
        return view('hrms.employee.asset_allocations.index', compact('assetAllocations'));
    }

    public function create()
    {
        $employees = Employee::with('user')->get();
        return view('hrms.employee.asset_allocations.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'asset_type' => 'required|string',
            'issue_date' => 'required|date',
            'status' => 'required|in:Active,Returned',
        ]);

        AssetAllocation::create([
            'employee_id' => $request->employee_id,
            'asset_type' => $request->asset_type,
            'asset_id_sn' => $request->asset_id_sn,
            'brand_model' => $request->brand_model,
            'issue_date' => $request->issue_date,
            'condition' => $request->condition ?? 'New',
            'mobile_sim_number' => $request->mobile_sim_number,
            'id_card_options' => $request->has('id_card_options') ? json_encode($request->id_card_options) : null,
            'has_charger' => $request->boolean('has_charger'),
            'has_bag' => $request->boolean('has_bag'),
            'sim_details' => $request->sim_details,
            'plan_details' => $request->plan_details,
            'status' => $request->status,
        ]);

        return redirect()->route('hrms.assets.index')->with('success', 'Asset Allocation mapped and created successfully.');
    }

    public function show(AssetAllocation $assetAllocation)
    {
        return view('hrms.employee.asset_allocations.show', compact('assetAllocation'));
    }

    public function edit(AssetAllocation $assetAllocation)
    {
        $employees = Employee::with('user')->get();
        return view('hrms.employee.asset_allocations.edit', compact('assetAllocation', 'employees'));
    }

    public function update(Request $request, AssetAllocation $assetAllocation)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'asset_type' => 'required|string',
            'issue_date' => 'required|date',
            'status' => 'required|in:Active,Returned',
        ]);

        $assetAllocation->update([
            'employee_id' => $request->employee_id,
            'asset_type' => $request->asset_type,
            'asset_id_sn' => $request->asset_id_sn,
            'brand_model' => $request->brand_model,
            'issue_date' => $request->issue_date,
            'condition' => $request->condition ?? 'New',
            'mobile_sim_number' => $request->mobile_sim_number,
            'id_card_options' => $request->has('id_card_options') ? json_encode($request->id_card_options) : null,
            'has_charger' => $request->boolean('has_charger'),
            'has_bag' => $request->boolean('has_bag'),
            'sim_details' => $request->sim_details,
            'plan_details' => $request->plan_details,
            'status' => $request->status,
        ]);

        return redirect()->route('hrms.assets.index')->with('success', 'Asset Allocation record precisely updated.');
    }

    public function destroy(AssetAllocation $assetAllocation)
    {
        $assetAllocation->delete();
        return redirect()->route('hrms.assets.index')->with('success', 'Asset Allocation deleted successfully.');
    }
}
