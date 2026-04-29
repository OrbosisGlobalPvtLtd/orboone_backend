<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ClientModel;
class ClientController extends Controller
{
    public function client_data()
    {
        $client_data = ClientModel::all();
        return view('pages.client_data', compact('client_data'));
    }

    public function showForm()
    {
        return view('pages.client_add');
    }

    public function client_add(Request $request)
    {
        if ($request->isMethod('post')) {

            $request->validate([
                'cleint_name' => 'required|string|max:255',
                'mobile_no' => 'required|numeric|digits:10',
                'email_id' => 'required|email|unique:clients,email_id',
                'bank_account_no' => 'required|string|regex:/^\d{9,18}$/',
                'ifce_code' => 'required|string|regex:/^[A-Za-z]{4}\d{7}$/',
                'bank_name_branch' => 'required|string|max:255',
                'gst_in' => ['required', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
                'pan' => 'required|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
                'aadhar' => 'required|string|size:12|regex:/^\d{12}$/',
                'firm_name' => 'required|string|max:255',
                'gst_login_id' => 'required|string|max:255',
                'gst_login_password' => 'required|string|min:8',
                'income_tax_login_id' => 'required|string|max:255',
                'income_tax_login_password' => 'required|string|min:8',
                'e_way_bill_id' => 'required|string|max:255',
                'e_way_bill_password' => 'required|string|min:8',
                'e_invoice_id' => 'required|string|max:255',
                'e_invoice_password' => 'required|string|min:8',

            ]);

            $gst_in = $request->gst_in;
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://gst-insights-api.p.rapidapi.com/getGSTDetailsUsingGST/{$gst_in}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "x-rapidapi-host: gst-insights-api.p.rapidapi.com",
                    "x-rapidapi-key: 88c113d7cemsh7563d54917ebfdfp14eee5jsn27acb5ff0a60"
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                return redirect()->back()->withErrors(['gst_in' => 'GST API Error: ' . $err])->withInput();
            }

            $apiResult = json_decode($response, true);

            $gstDetails = $apiResult['data'] ?? [];
            $tradeName = $gstDetails['tradeName'] ?? null;
            $legalName = $gstDetails['lgnm'] ?? null;
            $registrationStatus = $gstDetails['sts'] ?? null;
            $registrationDate = $gstDetails['rgdt'] ?? null;
            $businessType = $gstDetails['dty'] ?? null;
            $address = $gstDetails['pradr']['addr']['bnm'] ?? 'N/A';

            ClientModel::create([
                'cleint_name' => $request->cleint_name,
                'mobile_no' => $request->mobile_no,
                'email_id' => $request->email_id,
                'bank_account_no' => $request->bank_account_no,
                'ifce_code' => $request->ifce_code,
                'bank_name_branch' => $request->bank_name_branch,
                'gst_in' => $gst_in,
                'pan' => $request->pan,
                'aadhar' => $request->aadhar,
                'firm_name' => $request->firm_name,
                'gst_login_id' => $request->gst_login_id,
                'gst_login_password' => $request->gst_login_password,
                'income_tax_login_id' => $request->income_tax_login_id,
                'income_tax_login_password' => $request->income_tax_login_password,
                'e_way_bill_id' => $request->e_way_bill_id,
                'e_way_bill_password' => $request->e_way_bill_password,
                'e_invoice_id' => $request->e_invoice_id,
                'e_invoice_password' => $request->e_invoice_password,
             
            ]);

            return redirect()->route('pages.client_add')->with('success', 'Client Details Submit successfully!');
        }

        $client_data = ClientModel::latest()->get();
        return view("client_data", compact('client_data'));
    }
    public function client_data_print()
    {
        $client_data = ClientModel::all();
        return view('pages.client_data_print', compact('client_data'));
    }
}