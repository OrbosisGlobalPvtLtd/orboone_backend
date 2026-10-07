        @foreach($employeeDocuments as $doc)
        @php
        $documentTypeId = $doc->document_type_id ?? $doc->category_id ?? null;
        @endphp

        @if($documentTypeId && Route::has('hrms.documents.employee.upload_from_profile'))
        <form id="docUploadForm{{ $loop->iteration }}"
            action="{{ route('documents.employee.upload_from_profile', [$employeeData->id, $documentTypeId]) }}"
            method="POST"
            enctype="multipart/form-data"
            class="d-none">
            @csrf
            <input type="hidden" name="keep_verified" value="1">
        </form>
        @endif
        @endforeach
