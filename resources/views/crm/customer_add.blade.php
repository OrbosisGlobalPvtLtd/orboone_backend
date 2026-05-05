@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@section('_content')
<div class="container-fluid mt-2 px-4">
    <div class="row">
        <div class="col-12">
            <h4 class="font-weight-bold">Customer' Data</h4>
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <h5 class="text-center font-weight-bold mb-3">Add Customer</h5>

            <!-- Show success message if any -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <form action="{{ route('pages.customer_add') }}" method="POST" enctype="multipart/form-data" id="clientForm">
                @csrf
                <div class="mb-3">
                    <h6 class="font-weight-bold">Customer Information</h6>
                    <hr>
                    <!-- Client Name -->
                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="name">Customer Name:</label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Enter  name" required>
                            </div>
                            @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    
                    <!-- Email ID -->
                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="email">email:</label>
                                <input type="email" name="email_id" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter email ID" required>
                            </div>
                            @error('email')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="phone">Phone No:</label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="Enter mobile number" required>
                            </div>
                            @error('phone')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="gst_number">GST Number:</label>
                                <input type="text" name="gst_number" id="gst_number" class="form-control @error('gst_number') is-invalid @enderror" value="{{ old('gst_number') }}" placeholder="Enter bank account number" required>
                            </div>
                            @error('gst_number')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="pan_card">PAN Card:</label>
                                <input type="text" name="pan_card" id="pan_card" class="form-control @error('pan_card') is-invalid @enderror" value="{{ old('pan_card') }}" placeholder="Enter IFSC code" required>
                            </div>
                            @error('pan_card')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="display_name">Display Name:</label>
                                <input type="text" name="display_name" id="display_name" class="form-control @error('display_name') is-invalid @enderror" value="{{ old('display_name') }}" placeholder="Enter bank name & branch" required>
                            </div>
                            @error('display_name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="company_name">Company Name:</label>
                                <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" placeholder="Enter GST number" required>
                            </div>
                            @error('company_name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="address">address:</label>
                                <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}" placeholder="Enter address number" required>
                            </div>
                            @error('address')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                   
                    <!-- Submit Button -->
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
