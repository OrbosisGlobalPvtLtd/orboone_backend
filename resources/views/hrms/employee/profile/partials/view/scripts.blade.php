<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = $('#docPreviewModal');
        const dialog = document.getElementById('docPreviewDialog');
        const title = document.getElementById('docPreviewTitle');
        const body = document.getElementById('docPreviewBody');

        function resetDialogClass() {
            if (dialog) {
                dialog.classList.remove('doc-modal-pdf', 'doc-modal-image', 'doc-modal-small-image', 'doc-modal-other');
            }
        }

        document.querySelectorAll('.js-doc-preview').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                const ext = (this.getAttribute('data-ext') || '').toLowerCase();
                const docTitle = this.getAttribute('data-title') || 'Document Preview';

                if (title) title.textContent = docTitle;
                if (body) body.innerHTML = '';
                resetDialogClass();

                if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
                    if (dialog) dialog.classList.add('doc-modal-image');

                    if (body) {
                        body.innerHTML = `
                            <div class="doc-preview-image-wrap">
                                <img id="docPreviewImage" src="${url}" alt="${docTitle}">
                            </div>
                        `;
                    }

                    const img = document.getElementById('docPreviewImage');
                    if (img) {
                        img.onload = function() {
                            resetDialogClass();
                            if (img.naturalWidth <= 700 && img.naturalHeight <= 900) {
                                dialog.classList.add('doc-modal-small-image');
                            } else {
                                dialog.classList.add('doc-modal-image');
                            }
                        };
                    }
                } else if (ext === 'pdf') {
                    if (dialog) dialog.classList.add('doc-modal-pdf');
                    if (body) body.innerHTML = `<iframe class="doc-preview-frame" src="${url}#toolbar=0&navpanes=0&scrollbar=1"></iframe>`;
                } else {
                    if (dialog) dialog.classList.add('doc-modal-other');
                    if (body) body.innerHTML = `<iframe class="doc-preview-frame" src="${url}"></iframe>`;
                }

                if (modal && modal.length) modal.modal('show');
            });
        });

        document.querySelectorAll('.js-auto-upload-input').forEach(function(input) {
            input.addEventListener('change', function() {
                if (!this.files || !this.files.length) return;

                const form = this.closest('.js-auto-upload-form');
                const card = this.closest('.doc-upload-card');

                if (card) {
                    card.classList.add('is-uploading');
                    const text = card.querySelector('.doc-upload-text');
                    if (text) text.textContent = 'Uploading...';
                }

                if (form) form.submit();
            });
        });

        $('#docPreviewModal').on('hidden.bs.modal', function() {
            if (body) body.innerHTML = '';
            resetDialogClass();
        });

        function toggleViewExperienceFields(value) {
            const container = document.getElementById('view_total_experience_container');
            const input = document.getElementById('view_total_experience');
            if (value === 'fresher') {
                if (container) container.style.display = 'none';
                if (input) {
                    input.removeAttribute('required');
                    input.value = '0';
                }
            } else {
                if (container) container.style.display = 'block';
                if (input) {
                    input.setAttribute('required', 'required');
                    if (input.value === '0') input.value = '';
                }
            }
        }
        window.toggleViewExperienceFields = toggleViewExperienceFields;
        
        const expSelect = document.getElementById('view_experience_type');
        if (expSelect) {
            toggleViewExperienceFields(expSelect.value);
            $(expSelect).on('change change.select2', function() {
                toggleViewExperienceFields(this.value);
            });
        } else {
            const currentExpType = '{{ strtolower($profile->experience_type ?? "fresher") }}';
            if (currentExpType === 'fresher') {
                const container = document.getElementById('view_total_experience_container');
                if (container) container.style.display = 'none';
            }
        }
    });
</script>
