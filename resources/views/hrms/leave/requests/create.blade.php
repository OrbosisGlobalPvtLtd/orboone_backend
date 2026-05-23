@extends('layouts.panel')

@section('page_title', 'Apply Leave')

@section('_head')
@include('settings.partials.styles')
<style>
    .leave-page-container {
        max-width: 1120px;
        margin: 0 auto;
    }
</style>
@endsection

@section('_content')
<div class="set-page">
    <div class="leave-page-container">
        
        <!-- Premium Purple Gradient Hero -->
        <div class="set-header">
            <div>
                <div class="set-kicker">
                    <i class="fas fa-calendar-alt"></i> EMPLOYEE &bull; LEAVE MANAGEMENT
                </div>
                <h1 class="set-title">Apply Leave</h1>
                <p class="set-subtitle">Submit your leave request for approval. Requests remain pending until HR/admin approval.</p>
            </div>
            
            <!-- Glassmorphic Info Badge -->
            <div class="set-glass-badge">
                <div style="font-size: 24px; font-weight: 900; line-height: 1;"><i class="fas fa-edit"></i></div>
                <div style="font-size: 9px; font-weight: 850; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; opacity: 0.9;">Apply Form</div>
            </div>
        </div>

        @include('hrms.leave.shared.flash')

        <!-- Apply Form Card -->
        <div class="set-card">
            <div class="set-card-header">
                <div class="set-head-left">
                    <div class="set-icon-box"><i class="fas fa-file-signature"></i></div>
                    <div>
                        <h5 class="set-card-title">Leave Details</h5>
                        <p class="set-card-subtitle">Ensure all required fields are accurately filled for timely processing.</p>
                    </div>
                </div>
            </div>

            <div class="set-card-body" style="padding: 30px;">
                <form method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="set-grid mb-4">
                        <div>
                            <label class="set-label">Leave Type <span class="text-danger">*</span></label>
                            <select name="leave_type_id" class="set-control" required>
                                <option value="" disabled selected>Select leave type...</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="set-label">Attachment</label>
                            <input type="file" name="attachment" class="set-control" style="padding: 6px 12px; height: 38px;">
                        </div>

                        <div>
                            <label class="set-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}" class="set-control" required>
                        </div>

                        <div>
                            <label class="set-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" class="set-control" required>
                        </div>

                        <div>
                            <label class="set-label">Half Day</label>
                            <select name="is_half_day" class="set-control">
                                <option value="0" {{ old('is_half_day') == '0' ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('is_half_day') == '1' ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>

                        <div>
                            <label class="set-label">Half Day Type</label>
                            <select name="half_day_type" class="set-control">
                                <option value="" {{ old('half_day_type') == '' ? 'selected' : '' }}>Full day</option>
                                <option value="first_half" {{ old('half_day_type') == 'first_half' ? 'selected' : '' }}>First half</option>
                                <option value="second_half" {{ old('half_day_type') == 'second_half' ? 'selected' : '' }}>Second half</option>
                            </select>
                        </div>

                        <div style="grid-column: 1 / -1;">
                            <label class="set-label">Reason / Explanation <span class="text-danger">*</span></label>
                            <textarea name="reason" rows="4" class="set-control" required placeholder="Describe the purpose of your leave request...">{{ old('reason') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex align-items-center flex-wrap pt-3 border-top" style="gap: 12px;">
                        <button type="submit" class="set-btn">
                            <i class="fas fa-check-circle"></i> Submit Request
                        </button>
                        <a href="{{ route('leave-requests.index') }}" class="set-btn set-btn-soft">
                            <i class="fas fa-arrow-left"></i> Cancel & Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
