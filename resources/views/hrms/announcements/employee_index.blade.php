@extends('layouts.panel', ['active' => 'announcements'])

@section('page_title', 'Notice & Announcements')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .ann-page {
        background: var(--orb-bg);
        padding: 18px 10px 35px;
        min-height: calc(100vh - 90px);
    }

    .ann-hero {
        border-radius: 26px;
        padding: 24px;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        box-shadow: var(--orb-shadow);
        margin-bottom: 25px;
    }

    .ann-hero h3 {
        font-weight: 800;
        margin: 0;
    }

    .ann-hero p {
        opacity: .9;
        margin: 6px 0 0;
    }

    .ann-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    @media (max-width: 1200px) {
        .ann-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .ann-grid {
            grid-template-columns: 1fr;
        }
    }

    .ann-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        padding: 24px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .ann-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(16, 24, 40, .12);
    }

    .ann-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--orb-primary);
        opacity: 0.8;
    }

    .ann-card.priority-urgent::before { background: #C01048; }
    .ann-card.priority-high::before { background: #B54708; }
    .ann-card.priority-normal::before { background: #3538CD; }
    .ann-card.priority-low::before { background: #667085; }

    .ann-badge {
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        margin-bottom: 12px;
    }

    .badge-general { background: #EEF2FF; color: #3538CD; }
    .badge-holiday { background: #ECFDF3; color: #027A48; }
    .badge-emergency { background: #FEF3F2; color: #B42318; }
    .badge-policy { background: #F4F3FF; color: #5925DC; }
    .badge-meeting { background: #FFF7E6; color: #B54708; }

    .ann-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--orb-text);
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .ann-desc {
        font-size: 14px;
        color: var(--orb-muted);
        line-height: 1.6;
        margin-bottom: 20px;
        flex-grow: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .ann-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--orb-border);
        padding-top: 15px;
        margin-top: auto;
    }

    .ann-date {
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 600;
    }

    .btn-view {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border: 0;
        border-radius: 12px;
        font-weight: 800;
        font-size: 12px;
        padding: 8px 14px;
        transition: all 0.2s;
    }

    .btn-view:hover {
        background: var(--orb-primary);
        color: #fff;
    }

    .priority-indicator {
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .priority-urgent { color: #C01048; }
    .priority-high { color: #B54708; }
    .priority-normal { color: #3538CD; }
    .priority-low { color: #667085; }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        background: #fff;
        border-radius: 26px;
        box-shadow: var(--orb-shadow);
    }
    
    .empty-state i {
        font-size: 60px;
        color: var(--orb-soft);
        margin-bottom: 20px;
    }
</style>

<div class="ann-page">
    <div class="ann-hero">
        <h3>Notice & Announcements</h3>
        <p>Stay updated with the latest company news, policies, and events.</p>
    </div>

    @if($announcements->count() > 0)
    <div class="ann-grid">
        @foreach($announcements as $item)
        <div class="ann-card priority-{{ $item->priority }}">
            <div class="d-flex justify-content-between align-items-start">
                <span class="ann-badge badge-{{ $item->type }}">{{ $item->type }}</span>
                <div class="priority-indicator priority-{{ $item->priority }}">
                    <i class="fas fa-circle" style="font-size: 8px;"></i>
                    {{ ucfirst($item->priority) }}
                </div>
            </div>
            
            <h4 class="ann-title">{{ $item->title }}</h4>
            <div class="ann-desc">
                {!! strip_tags($item->description) !!}
            </div>

            <div class="ann-footer">
                <div class="ann-date">
                    <i class="far fa-calendar-alt me-1"></i>
                    {{ $item->created_at->format('d M, Y') }}
                </div>
                <div class="d-flex gap-2">
                    @if($item->attachment)
                    <a href="{{ $item->attachment_url }}" target="_blank" class="btn-view" title="View Attachment">
                        <i class="fas fa-paperclip"></i>
                    </a>
                    @endif
                    <button class="btn-view" onclick="viewAnnouncement({{ json_encode($item) }})">
                        Read More
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $announcements->links() }}
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-bullhorn"></i>
        <h4>No Announcements Found</h4>
        <p class="text-muted">There are no active notices or announcements at the moment.</p>
    </div>
    @endif
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content orb-modal">
            <div class="orb-modal-header">
                <div>
                    <h5 class="modal-title">Announcement Details</h5>
                    <p class="orb-modal-subtitle">Official company notice / broadcast details</p>
                </div>
                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body orb-modal-body">
                <div class="orb-form-section">
                    <div id="viewBadge" class="ann-badge mb-3"></div>
                    <h3 id="viewTitle" class="fw-900 mb-3" style="color: var(--orb-text);"></h3>
                    
                    <div class="d-flex gap-3 mb-4 text-muted small fw-bold">
                        <span><i class="far fa-calendar-alt me-1"></i> <span id="viewDate"></span></span>
                        <span><i class="far fa-user me-1"></i> <span id="viewAuthor"></span></span>
                        <span id="viewPriority" class="text-uppercase"></span>
                    </div>

                    <div id="viewContent" class="mb-4" style="line-height: 1.8; color: #444; font-size: 15px;"></div>

                    <div id="viewImagePreview" class="mb-4 d-none">
                        <img src="" class="img-fluid rounded-4 shadow-sm border w-100" style="max-height: 400px; object-fit: contain;" alt="Announcement Image">
                    </div>

                    <div id="viewAttachment" class="p-3 rounded-4 bg-light d-none align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white p-2 rounded-3 shadow-sm">
                                <i class="fas fa-file-pdf text-danger fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold small">Attachment Attached</div>
                                <div class="text-muted" style="font-size: 10px;">Click to download or view</div>
                            </div>
                        </div>
                        <a id="viewDownload" href="#" target="_blank" class="orb-btn-primary">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer orb-modal-footer">
                <button type="button" class="orb-btn-light" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function viewAnnouncement(item) {
        $('#viewTitle').text(item.title);
        $('#viewContent').html(item.description);
        $('#viewDate').text(new Date(item.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }));
        $('#viewAuthor').text(item.creator ? item.creator.name : 'System');
        $('#viewPriority').text(item.priority + ' priority').removeClass().addClass('priority-' + item.priority);
        
        $('#viewBadge').text(item.type).removeClass().addClass('ann-badge badge-' + item.type);

        if (item.attachment) {
            const attachmentUrl = item.attachment_url || ('/storage/' + item.attachment);
            const isImage = /\.(jpg|jpeg|png|webp|gif)$/i.test(attachmentUrl);

            if (isImage) {
                $('#viewImagePreview').removeClass('d-none');
                $('#viewImagePreview img').attr('src', attachmentUrl);
                $('#viewAttachment').addClass('d-none').removeClass('d-flex');
            } else {
                $('#viewImagePreview').addClass('d-none');
                $('#viewAttachment').removeClass('d-none').addClass('d-flex');
                $('#viewDownload').attr('href', attachmentUrl);
                
                // Set icon based on extension
                const isPdf = attachmentUrl.toLowerCase().endsWith('.pdf');
                $('#viewAttachment i').attr('class', isPdf ? 'fas fa-file-pdf text-danger fs-4' : 'fas fa-file-alt text-primary fs-4');
            }
        } else {
            $('#viewImagePreview').addClass('d-none');
            $('#viewAttachment').addClass('d-none').removeClass('d-flex');
        }

        $('#viewModal').modal('show');
    }
</script>
@endpush
