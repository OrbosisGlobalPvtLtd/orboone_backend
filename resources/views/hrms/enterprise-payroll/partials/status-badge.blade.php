@php
    $statusValue = strtolower((string) $status);
    $class = in_array($statusValue, ['active', 'approved', 'processed', 'generated', 'paid', 'locked'], true)
        ? 'ep-badge-success'
        : (in_array($statusValue, ['pending', 'draft'], true) ? 'ep-badge-warning' : 'ep-badge-danger');
@endphp
<span class="ep-badge {{ $class }}">{{ ucfirst($statusValue ?: 'N/A') }}</span>
