<div class="col-12 py-5 text-center">
    <div class="mb-4 d-inline-flex align-items-center justify-content-center" style="background: {{ $background ?? 'rgba(75, 0, 232, 0.05)' }}; border-radius: 50%; width: 80px; height: 80px; color: {{ $color ?? 'var(--set-primary)' }}; font-size: 28px;">
        <i class="{{ $icon ?? 'fas fa-folder-open' }}"></i>
    </div>
    <h4 class="font-weight-black" style="color: var(--set-text); font-size: 18px;">{{ $title ?? 'Repository is Empty' }}</h4>
    <p class="text-muted mt-2" style="max-width: 360px; margin: 0 auto; font-size: 13px; line-height: 1.5;">
        {{ $description ?? 'There are currently no items available. Please check back later.' }}
    </p>
</div>
