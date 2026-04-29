@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<!-- Modal -->
<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="viewModalLabel">Client Details</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody>
            <tr><th>ID</th><td id="modal_id"></td></tr>
            <tr><th>Client Name</th><td id="modal_cleint_name"></td></tr>
            <tr><th>Mobile No</th><td id="modal_mobile_no"></td></tr>
            <tr><th>Email ID</th><td id="modal_email_id"></td></tr>
            <tr><th>Bank Account No</th><td id="modal_bank_account_no"></td></tr>
            <tr><th>IFSC Code</th><td id="modal_ifce_code"></td></tr>
            <tr><th>Bank Name & Branch</th><td id="modal_bank_name_branch"></td></tr>
            <tr><th>GST IN</th><td id="modal_gst_in"></td></tr>
            <tr><th>PAN</th><td id="modal_pan"></td></tr>
            <tr><th>Aadhar</th><td id="modal_aadhar"></td></tr>
            <tr><th>Firm Name</th><td id="modal_firm_name"></td></tr>
            <tr><th>GST Login ID</th><td id="modal_gst_login_id"></td></tr>
            <tr><th>GST Login Password</th><td id="modal_gst_login_password"></td></tr>
            <tr><th>Income Tax Login ID</th><td id="modal_income_tax_login_id"></td></tr>
            <tr><th>Income Tax Login Password</th><td id="modal_income_tax_login_password"></td></tr>
            <tr><th>E-Way Bill ID</th><td id="modal_e_way_bill_id"></td></tr>
            <tr><th>E-Way Bill Password</th><td id="modal_e_way_bill_password"></td></tr>
            <tr><th>E-Invoice ID</th><td id="modal_e_invoice_id"></td></tr>
            <tr><th>E-Invoice Password</th><td id="modal_e_invoice_password"></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid mt-2 px-4">
  <div class="row">
    <div class="col-12">
      <h4 class="font-weight-bold">Client's Data</h4>
      <hr>
    </div>
  </div>
  
  <div class="row">
    <div class="col-12 mb-3">
      <div class="bg-light text-dark card p-3 overflow-auto">
        <div class="d-flex justify-content-between">
          <a href="{{ route('pages.client_add') }}" class="btn btn-outline-dark mb-3 w-25">
            <i class="fas fa-plus mr-1"></i>
            <span> Add Client</span>
          </a>
          <a href="{{ route('pages.client_data_print') }}" class="btn btn-outline-dark mb-3 w-25" target="_blank">
            <i class="fas fa-print mr-1"></i>
            <span> Print</span>
          </a>
        </div>
      </div>
      @if (session('status'))
      <div class="alert alert-success">
        {{ session('status') }}
      </div>
      @endif
      <table class="table table-light table-striped table-hover table-bordered text-center">
        <thead>
          <tr>
            <th scope="col" class="table-dark">ID</th>
            <th scope="col" class="table-dark">Client Name</th>
            <th scope="col" class="table-dark">Mobile No</th>
            <th scope="col" class="table-dark">Email Id</th>
            <th scope="col" class="table-dark">Bank Account No</th>
            <th scope="col" class="table-dark">IFCE Code</th>
            <th scope="col" class="table-dark">Bank Name Branch</th>
            <th scope="col" class="table-dark">GST In</th>
            <th scope="col" class="table-dark">PAN</th>
            <th scope="col" class="table-dark">Aadhar No</th>
            <th scope="col" class="table-dark">Firm Name</th>
            <th scope="col" class="table-dark">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($client_data as $client)
          <tr>
            <td>{{ $client->id }}</td>
            <td>{{ $client->cleint_name }}</td>
            <td>{{ $client->mobile_no }}</td>
            <td>{{ $client->email_id }}</td>
            <td>{{ $client->bank_account_no }}</td>
            <td>{{ $client->ifce_code }}</td>
            <td>{{ $client->bank_name_branch }}</td>
            <td>{{ $client->gst_in }}</td>
            <td>{{ $client->pan }}</td>
            <td>{{ $client->aadhar }}</td>
            <td>{{ $client->firm_name }}</td>
            <td>
              <button class="btn btn-info view-btn" data-toggle="modal" data-target="#viewModal"
                data-id="{{ $client->id }}"
                data-cleint_name="{{ $client->cleint_name }}"
                data-mobile_no="{{ $client->mobile_no }}"
                data-email_id="{{ $client->email_id }}"
                data-bank_account_no="{{ $client->bank_account_no }}"
                data-ifce_code="{{ $client->ifce_code }}"
                data-bank_name_branch="{{ $client->bank_name_branch }}"
                data-gst_in="{{ $client->gst_in }}"
                data-pan="{{ $client->pan }}"
                data-aadhar="{{ $client->aadhar }}"
                data-firm_name="{{ $client->firm_name }}"
                data-gst_login_id="{{ $client->gst_login_id }}"
                data-gst_login_password="{{ $client->gst_login_password }}"
                data-income_tax_login_id="{{ $client->income_tax_login_id }}"
                data-income_tax_login_password="{{ $client->income_tax_login_password }}"
                data-e_way_bill_id="{{ $client->e_way_bill_id }}"
                data-e_way_bill_password="{{ $client->e_way_bill_password }}"
                data-e_invoice_id="{{ $client->e_invoice_id }}"
                data-e_invoice_password="{{ $client->e_invoice_password }}"
                >
                View
              </button>
            </td>
          </tr>
          @endforeach
        </tbody>
    </table>
    
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  $(document).ready(function () {
    // Bind click event to the View button
    $('.view-btn').on('click', function () {
      var clientData = $(this).data();  

      // Generate the modal content dynamically
      var content = `
        <table class="table table-bordered">
          <tbody>
            <tr>
              <td><strong>Client Name:</strong></td>
              <td>${clientData.cleint_name}</td>
            </tr>
            <tr>
              <td><strong>Mobile No:</strong></td>
              <td>${clientData.mobile_no}</td>
            </tr>
            <tr>
              <td><strong>Email ID:</strong></td>
              <td>${clientData.email_id}</td>
            </tr>
            <tr>
              <td><strong>Bank Account No:</strong></td>
              <td>${clientData.bank_account_no}</td>
            </tr>
            <tr>
              <td><strong>IFCE Code:</strong></td>
              <td>${clientData.ifce_code}</td>
            </tr>
            <tr>
              <td><strong>Bank Name & Branch:</strong></td>
              <td>${clientData.bank_name_branch}</td>
            </tr>
            <tr>
              <td><strong>GST IN:</strong></td>
              <td>${clientData.gst_in}</td>
            </tr>
            <tr>
              <td><strong>PAN:</strong></td>
              <td>${clientData.pan}</td>
            </tr>
            <tr>
              <td><strong>Aadhar No:</strong></td>
              <td>${clientData.aadhar}</td>
            </tr>
            <tr>
              <td><strong>Firm Name:</strong></td>
              <td>${clientData.firm_name}</td>
            </tr>

            <tr>
              <td><strong>GST login Id:</strong></td>
              <td>${clientData.gst_login_id}</td>
            </tr>
            <tr>
              <td><strong>GST login Password:</strong></td>
              <td>${clientData.gst_login_password}</td>
            </tr>
            <tr>
              <td><strong>Income Tax login id:</strong></td>
              <td>${clientData.income_tax_login_id}</td>
            </tr>
            <tr>
              <td><strong>Income Tax login Password:</strong></td>
              <td>${clientData.income_tax_login_password}</td>
            </tr>
            <tr>
              <td><strong>E Way Bill Id:</strong></td>
              <td>${clientData.e_way_bill_id}</td>
            </tr>
            <tr>
              <td><strong>E Way Bill Password:</strong></td>
              <td>${clientData.e_way_bill_password}</td>
            </tr>
             <tr>
              <td><strong>E invoice Id:</strong></td>
              <td>${clientData.e_invoice_id}</td>
            </tr>
             <tr>
              <td><strong>E Invoice Password:</strong></td>
              <td>${clientData.e_invoice_password}</td>
            </tr>
          </tbody>
        </table>
      `;

      // Inject the content into the modal body
      $('#modal-body-content').html(content);
    });
  });


    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', () => {
            const fields = [
                'id', 'cleint_name', 'mobile_no', 'email_id', 'bank_account_no', 'ifce_code',
                'bank_name_branch', 'gst_in', 'pan', 'aadhar', 'firm_name',
                'gst_login_id', 'gst_login_password',
                'income_tax_login_id', 'income_tax_login_password',
                'e_way_bill_id', 'e_way_bill_password',
                'e_invoice_id', 'e_invoice_password'
            ];

            fields.forEach(field => {
                document.getElementById(`modal_${field}`).textContent = button.dataset[field] || '-';
            });
        });
    });


</script>
@endsection