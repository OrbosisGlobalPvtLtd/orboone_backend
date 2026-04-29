<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerModel;

class CustomerController extends Controller
{
    public function customer_data(Request $request)
    {
        $search = $request->input('search');
        $customer_data = CustomerModel::when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('pages.customer_data', compact('customer_data', 'search'));
    }

    public function showForm()
    {
        return view('pages.customer_add');
    }

    public function customer_add(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'nullable',
            'gst_number' => 'nullable',
            'pan_card' => 'nullable',
            'display_name' => 'nullable',
            'company_name' => 'nullable',
            'address' => 'nullable',
        ]);

        CustomerModel::create(
            [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'gst_number' => $request->mobile_no,
                'pan_card' => $request->pan_card,
                'display_name' => $request->display_name,
                'company_name' => $request->company_name,
                'address' => $request->address,

            ]);

        return redirect()->route('customer_data')->with('success', 'Customer added successfully!');
    }

    public function show(CustomerModel $customer)
    {
        return response()->json($customer);
    }

    public function update(Request $request, CustomerModel $customer)
    {

        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'gst_number' => $request->mobile_no,
            'pan_card' => $request->pan_card,
            'display_name' => $request->display_name,
            'company_name' => $request->company_name,
            'address' => $request->address,
           
        ]);

        return redirect()->route('customer_data')->with('success', 'Customer updated successfully!');
    }

    public function destroy(CustomerModel $customer)
    {
        $customer->delete();

        return redirect()->route('customer_data')->with('success', 'Customer deleted successfully!');
    }
    public function customer_print()
    {
        $customer_data = CustomerModel::all();
        return view('pages.customer_print', compact('customer_data'));
    }
    
}
