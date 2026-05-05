<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanySettingsController extends Controller
{
    public function index()
    {
        $company = DB::table('company_settings')->first();

        return view('settings.company', compact('company'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:180'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'url', 'max:180'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'seal' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($data['logo'], $data['seal']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('company', 'public');
        }

        if ($request->hasFile('seal')) {
            $data['seal'] = $request->file('seal')->store('company', 'public');
        }

        $existing = DB::table('company_settings')->first();

        if ($existing) {
            $data['updated_at'] = now();
            DB::table('company_settings')->where('id', $existing->id)->update($data);
        } else {
            $data['created_at'] = now();
            $data['updated_at'] = now();
            DB::table('company_settings')->insert($data);
        }

        return redirect()
            ->route('settings.company.index')
            ->with('success', 'Company settings updated successfully.');
    }
}
