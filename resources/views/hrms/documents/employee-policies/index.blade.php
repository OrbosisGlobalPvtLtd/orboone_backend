@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Company Policies')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
    }
    .eo-page { min-height: calc(100vh - 90px); padding: 16px 10px 30px; background: var(--orb-bg); }
    .eo-container { max-width: 1320px; margin: 0 auto; }
    .eo-header { background: #fff; border: 1px solid var(--orb-border); border-radius: 20px; padding: 16px; margin-bottom: 20px; }
    .eo-card { background: #fff; border-radius: 20px; border: 1px solid var(--orb-border); padding: 20px; }
</style>

<div class="eo-page">
    <div class="eo-container">
        <div class="eo-header">
            <h1 style="margin:0; font-size: 24px; font-weight: 900; color: var(--orb-text);">Company Policies</h1>
            <p>View latest company policies</p>
        </div>

        <div class="row">
            @foreach($policies as $policy)
                <div class="col-md-4 mb-4">
                    <div class="eo-card">
                        <h4 style="font-weight: 800;">{{ $policy->title }}</h4>
                        <p class="text-muted" style="font-size: 13px;">{{ $policy->documentType->name ?? '-' }}</p>
                        @if($policy->description)
                            <p style="font-size: 13px;">{{ Str::limit($policy->description, 100) }}</p>
                        @endif
                        @if($policy->file_path)
                            <a href="{{ route('hrms.documents.file', $policy->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm btn-block">View Policy</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
