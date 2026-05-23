@extends('layouts.app')

@section('nav')
    @include('components.nav')
@endsection

@section('content')
<div class="container pb-5" id="recruitments">
  <div class="row">
      <div class="col-12 text-center">
          <h3>Recruitment's Detail</h3>
          <div class="line text-center"> &nbsp;</div>
      </div>
  </div>

  @if (session('status'))
    <div class="alert alert-success">
      {{ session('status') }}
    </div>
  @endif

  <div class="row">
    <div class="col-12">
      <div class="mb-3">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label for="position_id">Position:</label>
              <input type="text" name="position_id" id="position_id" value="{{ $recruitment->position->name }}" class="form-control-plaintext" readonly>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label for="title">Title:</label>
              <input type="text" name="title" id="title" class="form-control-plaintext" readonly value="{{ $recruitment->title }}">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label for="description">Description:</label>
              <input type="text" name="description" id="description" class="form-control-plaintext" readonly value="{{ $recruitment->description }}">
            </div>
          </div>
        </div>

        @if ($recruitment->attachment !== null)
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label for="attachment">Attachment:</label>
                <br>
                <a href="{{ asset('/storage/' . $recruitment->attachment) }}" download="attachment" class="btn btn-outline-dark">
                  <i class="fas fa-download mr-1"></i>
                  Download
                </a>
              </div>
            </div>
          </div>
        @endif

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <button type="button" class="btn btn-primary mr-2 px-5" data-toggle="modal" data-target="#applyModal">
                Apply
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="applyModal" tabindex="-1" role="dialog" aria-labelledby="applyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 650px;">
    <div class="modal-content orb-modal">
      <div class="modal-header orb-modal-header">
        <div>
          <h5 class="modal-title" id="applyModalLabel">Submit Application</h5>
          <div class="orb-modal-subtitle">Apply for {{ $recruitment->position->name }}</div>
        </div>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="background:none; border:none; font-size:24px; opacity:0.8; outline:none;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('recruitment-candidates.store') }}" method="POST" enctype="multipart/form-data" style="width: 100%;">
        @csrf
        <div class="modal-body orb-modal-body">
          <input type="hidden" name="recruitment_id" value="{{ $recruitment->id }}">
          
          <div class="orb-form-section">
            <div class="orb-form-section-title">
              <i class="fas fa-user-tie"></i> Candidate Details
            </div>
            
            <div class="orb-form-grid">
              <div>
                <label class="orb-form-label" for="name">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Enter name" class="form-control @error('name') is-invalid @enderror">
                @error('name')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>
              
              <div>
                <label class="orb-form-label" for="email">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="Enter email" class="form-control @error('email') is-invalid @enderror">
                @error('email')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label class="orb-form-label" for="phone">Phone Number <span class="text-danger">*</span></label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required placeholder="Enter phone" class="form-control @error('phone') is-invalid @enderror" minlength="11" maxlength="13">
                @error('phone')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label class="orb-form-label" for="address">Address <span class="text-danger">*</span></label>
                <input type="text" name="address" id="address" value="{{ old('address') }}" required placeholder="Enter address" class="form-control @error('address') is-invalid @enderror">
                @error('address')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="mt-3">
              <label class="orb-form-label" for="message">Cover Message <span class="text-danger">*</span></label>
              <textarea name="message" id="message" required placeholder="Enter message" class="form-control @error('message') is-invalid @enderror" style="height: 100px;"></textarea>
              @error('message')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="orb-form-section">
            <div class="orb-form-section-title">
              <i class="fas fa-paperclip"></i> Documents
            </div>
            
            <div class="orb-form-grid">
              <div>
                <label class="orb-form-label" for="photo">Photo <span class="text-danger">*</span></label>
                <input type="file" name="photo" id="photo" class="form-control-file @error('photo') is-invalid @enderror" required style="padding-top: 6px;">
                @error('photo')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label class="orb-form-label" for="cv">CV / Resume <span class="text-danger">*</span></label>
                <input type="file" name="cv" id="cv" class="form-control-file @error('cv') is-invalid @enderror" required style="padding-top: 6px;">
                @error('cv')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer orb-modal-footer">
          <button type="button" class="orb-btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="orb-btn-primary"><i class="fas fa-paper-plane"></i> Submit Application</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection