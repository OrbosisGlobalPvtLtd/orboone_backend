@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container-fluid mt-2 px-4">
    <div class="row">
        <div class="col-12">
            <h4 class="font-weight-bold">Clients' Data</h4>
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <h5 class="text-center font-weight-bold mb-3">Add Client</h5>
            <!-- Show success message if any -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <form action="{{ route('pages.client_add') }}" method="POST" enctype="multipart/form-data" id="clientForm">
                @csrf
                <div class="mb-3">
                    <h6 class="font-weight-bold">Client Information</h6>
                    <hr>
                    <!-- Client Name -->
                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="cleint_name">Client Name:</label>
                                <input type="text" name="cleint_name" id="cleint_name" class="form-control @error('cleint_name') is-invalid @enderror" value="{{ old('cleint_name') }}" placeholder="Enter client name" required>
                            </div>
                            @error('cleint_name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-----------Mobile No------------>
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="mobile_no">Mobile No:</label>
                                <input type="text" name="mobile_no" id="mobile_no" class="form-control @error('mobile_no') is-invalid @enderror" value="{{ old('mobile_no') }}" placeholder="Enter mobile number" required>
                            </div>
                            @error('mobile_no')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!------------ Email ID ----------->

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="email_id">Email ID:</label>
                                <input type="email" name="email_id" id="email_id" class="form-control @error('email_id') is-invalid @enderror" value="{{ old('email_id') }}" placeholder="Enter email ID" required>
                            </div>
                            @error('email_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!--------- Bank Account No-------->

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="bank_account_no">Bank Account No:</label>
                                <input type="text" name="bank_account_no" id="bank_account_no" class="form-control @error('bank_account_no') is-invalid @enderror" value="{{ old('bank_account_no') }}" placeholder="Enter bank account number" required>
                            </div>
                            @error('bank_account_no')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!------------IFSC Code----------------->

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="ifce_code">IFSC Code:</label>
                                <input type="text" name="ifce_code" id="ifce_code" class="form-control @error('ifce_code') is-invalid @enderror" value="{{ old('ifce_code') }}" placeholder="Enter IFSC code" required>
                            </div>
                            @error('ifce_code')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!------- Bank Name & Branch ---------->

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="bank_name_branch">Bank Name & Branch:</label>
                                <input type="text" name="bank_name_branch" id="bank_name_branch" class="form-control @error('bank_name_branch') is-invalid @enderror" value="{{ old('bank_name_branch') }}" placeholder="Enter bank name & branch" required>
                            </div>
                            @error('bank_name_branch')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-------------------GST No------------->

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="gst_in">GST IN:</label>
                                <input type="text" name="gst_in" id="gst_in" class="form-control @error('gst_in') is-invalid @enderror" value="{{ old('gst_in') }}" placeholder="Enter GST number" required>
                            </div>
                            @error('gst_in')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!--------------------PAN No------------->

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="pan">PAN No:</label>
                                <input type="text" name="pan" id="pan" class="form-control @error('pan') is-invalid @enderror" value="{{ old('pan') }}" placeholder="Enter PAN number" required>
                            </div>
                            @error('pan')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!------------------Aadhar No------------------->

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="aadhar">Aadhar No:</label>
                                <input type="text" name="aadhar" id="aadhar" class="form-control @error('aadhar') is-invalid @enderror" value="{{ old('aadhar') }}" placeholder="Enter Aadhar number" required>
                            </div>
                            @error('aadhar')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!--------------- Firm Name------------------>

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="firm_name">Firm Name:</label>
                                <input type="text" name="firm_name" id="firm_name" class="form-control @error('firm_name') is-invalid @enderror" value="{{ old('firm_name') }}" placeholder="Enter firm name" required>
                            </div>
                            @error('firm_name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!---------------GST Login Details--------------->

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="gst_login_id">GST Login ID:</label>
                                <input type="text" name="gst_login_id" id="gst_login_id" class="form-control @error('gst_login_id') is-invalid @enderror" value="{{ old('gst_login_id') }}" placeholder="Enter GST login ID" required>
                            </div>
                            @error('gst_login_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!---------------GST Login Password-------------->

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="gst_login_password">GST Login Password:</label>
                                <input type="password" name="gst_login_password" id="gst_login_password" class="form-control @error('gst_login_password') is-invalid @enderror" value="{{ old('gst_login_password') }}" placeholder="Enter GST login password" required>
                            </div>
                            @error('gst_login_password')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!----------------Income Tax Login Details-------------->

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="income_tax_login_id">Income Tax Login ID:</label>
                                <input type="text" name="income_tax_login_id" id="income_tax_login_id" class="form-control @error('income_tax_login_id') is-invalid @enderror" value="{{ old('income_tax_login_id') }}" placeholder="Enter Income Tax login ID" required>
                            </div>
                            @error('income_tax_login_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!---------------Income Tax Login Password------------->

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="income_tax_login_password">Income Tax Login Password:</label>
                                <input type="password" name="income_tax_login_password" id="income_tax_login_password" class="form-control @error('income_tax_login_password') is-invalid @enderror" value="{{ old('income_tax_login_password') }}" placeholder="Enter Income Tax login password" required>
                            </div>
                            @error('income_tax_login_password')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!------------------E-Way Bill Details----------------------->

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="e_way_bill_id">E-Way Bill ID:</label>
                                <input type="text" name="e_way_bill_id" id="e_way_bill_id" class="form-control @error('e_way_bill_id') is-invalid @enderror" value="{{ old('e_way_bill_id') }}" placeholder="Enter E-Way Bill ID" required>
                            </div>
                            @error('e_way_bill_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-----------------E-Way Bill Password-------------------->

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="e_way_bill_password">E-Way Bill Password:</label>
                                <input type="password" name="e_way_bill_password" id="e_way_bill_password" class="form-control @error('e_way_bill_password') is-invalid @enderror" value="{{ old('e_way_bill_password') }}" placeholder="Enter E-Way Bill password" required>
                            </div>
                            @error('e_way_bill_password')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-------------------E-Invoice Details------------>

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="e_invoice_id">E-Invoice ID:</label>
                                <input type="text" name="e_invoice_id" id="e_invoice_id" class="form-control @error('e_invoice_id') is-invalid @enderror" value="{{ old('e_invoice_id') }}" placeholder="Enter E-Invoice ID" required>
                            </div>
                            @error('e_invoice_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!----------------E-Invoice Password-------------->

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="e_invoice_password">E-Invoice Password:</label>
                                <input type="password" name="e_invoice_password" id="e_invoice_password" class="form-control @error('e_invoice_password') is-invalid @enderror" value="{{ old('e_invoice_password') }}" placeholder="Enter E-Invoice password" required>
                            </div>
                            @error('e_invoice_password')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!----------------Submit Button--------------->

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary px-5">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- JavaScript for Client-Side Validation -->
<script>
    document.getElementById('clientForm').addEventListener('submit', function (event) {
        var form = this;
        if (form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
</script>
@endsection