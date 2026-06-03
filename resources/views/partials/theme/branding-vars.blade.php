<style>
    :root {
        --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
        --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-soft: #F4F2FF;
        --primary: var(--orb-primary);
        --primary-2: var(--orb-secondary);
    }

    .orb-modal-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #fff !important;
        border-bottom: 0 !important;
    }

    .orb-modal-header .modal-title {
        color: #fff !important;
        font-weight: 800 !important;
    }

    .orb-modal-header .close,
    .orb-modal-header .btn-close {
        color: #fff !important;
        opacity: 0.9 !important;
        text-shadow: none !important;
    }

    .orb-gradient-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #fff !important;
    }
</style>
