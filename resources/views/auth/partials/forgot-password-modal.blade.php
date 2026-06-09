<!-- Forgot Password Modal CSS Customizations (Bootstrap 5 Scope) -->
<style>
    #forgotPasswordModal .modal-content {
        border-radius: 24px !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15) !important;
    }
    #forgotPasswordModal .modal-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        border-bottom: none !important;
        padding: 20px 24px !important;
        border-top-left-radius: 23px !important;
        border-top-right-radius: 23px !important;
    }
    #forgotPasswordModal .modal-title {
        color: #ffffff !important;
        font-weight: 800 !important;
        letter-spacing: -0.5px !important;
    }
    #forgotPasswordModal .modal-footer {
        border-top: none !important;
    }
    #forgotPasswordModal .form-control {
        border-radius: 12px !important;
        height: 48px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #f8fafc !important;
        color: #0f172a !important;
        transition: all 0.2s ease !important;
    }
    #forgotPasswordModal .form-control:focus {
        border-color: var(--orb-primary) !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.1) !important;
        outline: none !important;
    }
    #forgotPasswordModal .input-group-text {
        border-radius: 12px 0 0 12px !important;
        border: 1px solid #cbd5e1 !important;
        border-right: none !important;
        background-color: #f8fafc !important;
    }
    #forgotPasswordModal .input-group .form-control {
        border-radius: 0 12px 12px 0 !important;
        border-left: none !important;
    }
    #forgotPasswordModal .forgot-gradient-btn {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 12px !important;
        height: 48px !important;
        font-weight: 700 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2) !important;
        transition: all 0.2s ease !important;
    }
    #forgotPasswordModal .forgot-gradient-btn:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.3) !important;
        opacity: 0.95 !important;
    }
    #forgotPasswordModal .forgot-gradient-btn:disabled {
        opacity: 0.6 !important;
        cursor: not-allowed !important;
        transform: none !important;
    }
    #forgotPasswordModal .forgot-secondary-btn {
        border-radius: 12px !important;
        height: 48px !important;
        font-weight: 700 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease !important;
    }
    #forgotPasswordModal .alert {
        border-radius: 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }
</style>

<!-- Forgot Password Modal HTML Markup -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-black fs-4" id="forgotPasswordModalLabel">Forgot Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="box-shadow: none;"></button>
            </div>
            <div class="modal-body px-4 pb-4 pt-2">
                
                <!-- Step 1: Send OTP -->
                <div id="forgotStep1">
                    <p class="text-muted mb-4" style="font-size: 14px; line-height: 1.6;">Enter your work email. If it exists, we will send a 6-digit OTP.</p>
                    <div id="forgotStep1Alert" class="alert alert-danger d-none" role="alert"></div>
                    <form id="forgotStep1Form">
                        <div class="mb-4">
                            <label for="forgotEmail" class="form-label fw-bold text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.5px; margin-bottom: 8px;">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-regular fa-envelope text-muted"></i></span>
                                <input type="email" id="forgotEmail" class="form-control" placeholder="e.g. employee@company.com" required>
                            </div>
                        </div>
                        <button type="submit" class="btn w-100 forgot-gradient-btn" id="btnSendOtp">
                            <i class="fa-solid fa-paper-plane"></i> Send OTP Code
                        </button>
                    </form>
                </div>

                <!-- Step 2: Verify OTP -->
                <div id="forgotStep2" class="d-none">
                    <p class="text-muted mb-4" style="font-size: 14px; line-height: 1.6;">A 6-digit verification code has been sent to your email. Enter it below.</p>
                    <div id="forgotStep2Alert" class="alert alert-danger d-none" role="alert"></div>
                    <form id="forgotStep2Form">
                        <div class="mb-4">
                            <label for="forgotOtp" class="form-label fw-bold text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.5px; margin-bottom: 8px;">OTP Code</label>
                            <input type="text" id="forgotOtp" class="form-control text-center fw-bold fs-4" placeholder="Enter 6-digit OTP" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required style="letter-spacing: 4px;">
                        </div>
                        <button type="submit" class="btn w-100 forgot-gradient-btn mb-2" id="btnVerifyOtp">
                            <i class="fa-solid fa-check-circle"></i> Verify Code
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100 forgot-secondary-btn" id="btnResendOtp">
                            <i class="fa-solid fa-rotate"></i> Resend OTP
                        </button>
                    </form>
                </div>

                <!-- Step 3: Reset Password -->
                <div id="forgotStep3" class="d-none">
                    <p class="text-muted mb-4" style="font-size: 14px; line-height: 1.6;">OTP verified. Please enter your new password below.</p>
                    <div id="forgotStep3Alert" class="alert alert-danger d-none" role="alert"></div>
                    <form id="forgotStep3Form">
                        <div class="mb-3">
                            <label for="forgotNewPassword" class="form-label fw-bold text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.5px; margin-bottom: 8px;">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" id="forgotNewPassword" class="form-control" placeholder="Minimum 8 characters" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="forgotConfirmPassword" class="form-label fw-bold text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.5px; margin-bottom: 8px;">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" id="forgotConfirmPassword" class="form-control" placeholder="Re-type new password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn w-100 forgot-gradient-btn" id="btnResetPassword">
                            <i class="fa-solid fa-lock"></i> Update Password
                        </button>
                    </form>
                </div>

                <!-- Step 4: Success Message -->
                <div id="forgotStep4" class="d-none text-center py-4">
                    <div class="d-inline-flex align-items-center justify-content-center mb-4" style="width: 90px; height: 90px; border-radius: 50%; background-color: #f0fdf4;">
                        <i class="fa-solid fa-circle-check text-success" style="font-size: 56px;"></i>
                    </div>
                    <h4 class="fw-bold mb-2" style="color: #0f172a; font-weight: 800;">Password Reset!</h4>
                    <p class="text-muted mb-4" style="font-size: 14px; line-height: 1.6; padding: 0 15px;">Your password has been successfully updated. You can now log in with your new credentials.</p>
                    <button type="button" class="btn w-100 forgot-gradient-btn" id="btnSuccessClose">
                        <i class="fa-solid fa-right-to-bracket"></i> Sign In Now
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('forgotPasswordModal');
        let bsModal = null;
        if (modalEl && typeof bootstrap !== 'undefined') {
            bsModal = new bootstrap.Modal(modalEl);
        }

        const forgotStep1 = document.getElementById('forgotStep1');
        const forgotStep2 = document.getElementById('forgotStep2');
        const forgotStep3 = document.getElementById('forgotStep3');
        const forgotStep4 = document.getElementById('forgotStep4');

        const forgotStep1Alert = document.getElementById('forgotStep1Alert');
        const forgotStep2Alert = document.getElementById('forgotStep2Alert');
        const forgotStep3Alert = document.getElementById('forgotStep3Alert');

        const forgotStep1Form = document.getElementById('forgotStep1Form');
        const forgotStep2Form = document.getElementById('forgotStep2Form');
        const forgotStep3Form = document.getElementById('forgotStep3Form');

        const forgotEmailInput = document.getElementById('forgotEmail');
        const forgotOtpInput = document.getElementById('forgotOtp');
        const forgotNewPasswordInput = document.getElementById('forgotNewPassword');
        const forgotConfirmPasswordInput = document.getElementById('forgotConfirmPassword');

        let currentEmail = '';
        let currentOtp = '';

        function openForgotModal() {
            if (bsModal) {
                bsModal.show();
            } else if (modalEl) {
                // Fallback in case bootstrap object isn't resolved loaded yet
                modalEl.classList.add('show');
                modalEl.style.display = 'block';
                document.body.classList.add('modal-open');
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'forgot-backdrop-fallback';
                document.body.appendChild(backdrop);
            }
            resetForgotModal();
        }

        function closeForgotModal() {
            if (bsModal) {
                bsModal.hide();
            } else if (modalEl) {
                // Fallback removal
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrop = document.getElementById('forgot-backdrop-fallback');
                if (backdrop) backdrop.remove();
            }
        }

        function resetForgotModal() {
            if (forgotStep1) forgotStep1.classList.remove('d-none');
            if (forgotStep2) forgotStep2.classList.add('d-none');
            if (forgotStep3) forgotStep3.classList.add('d-none');
            if (forgotStep4) forgotStep4.classList.add('d-none');
            
            if (forgotStep1Alert) {
                forgotStep1Alert.classList.add('d-none');
                forgotStep1Alert.textContent = '';
            }
            if (forgotStep2Alert) {
                forgotStep2Alert.classList.add('d-none');
                forgotStep2Alert.textContent = '';
            }
            if (forgotStep3Alert) {
                forgotStep3Alert.classList.add('d-none');
                forgotStep3Alert.textContent = '';
            }
            
            if (forgotStep1Form) forgotStep1Form.reset();
            if (forgotStep2Form) forgotStep2Form.reset();
            if (forgotStep3Form) forgotStep3Form.reset();
            
            const btnSend = document.getElementById('btnSendOtp');
            const btnVerify = document.getElementById('btnVerifyOtp');
            const btnReset = document.getElementById('btnResetPassword');
            if (btnSend) {
                btnSend.disabled = false;
                btnSend.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send OTP Code';
            }
            if (btnVerify) {
                btnVerify.disabled = false;
                btnVerify.innerHTML = '<i class="fa-solid fa-check-circle"></i> Verify Code';
            }
            if (btnReset) {
                btnReset.disabled = false;
                btnReset.innerHTML = '<i class="fa-solid fa-lock"></i> Update Password';
            }
        }

        // Intercept all "Forgot password?" links
        document.querySelectorAll('.helper-link').forEach(link => {
            if (link.textContent.toLowerCase().includes('forgot password') || link.href.includes('forgot-password')) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    openForgotModal();
                });
            }
        });

        // Step 1: Send OTP
        if (forgotStep1Form) {
            forgotStep1Form.addEventListener('submit', function(e) {
                e.preventDefault();
                const email = forgotEmailInput.value.trim();
                if (!email) return;
                
                const btn = document.getElementById('btnSendOtp');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Sending...';
                forgotStep1Alert.classList.add('d-none');
                
                fetch("{{ route('password.otp.send') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: email })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    currentEmail = email;
                    forgotStep1.classList.add('d-none');
                    forgotStep2.classList.remove('d-none');
                    
                    forgotStep2Alert.className = 'alert alert-success';
                    forgotStep2Alert.textContent = data.message || 'OTP sent successfully.';
                    forgotStep2Alert.classList.remove('d-none');
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send OTP Code';
                    forgotStep1Alert.className = 'alert alert-danger';
                    forgotStep1Alert.textContent = err.message || (err.errors && err.errors.email ? err.errors.email[0] : 'Failed to send OTP.');
                    forgotStep1Alert.classList.remove('d-none');
                });
            });
        }

        // Step 2: Verify OTP
        if (forgotStep2Form) {
            forgotStep2Form.addEventListener('submit', function(e) {
                e.preventDefault();
                const otp = forgotOtpInput.value.trim();
                if (!otp || otp.length !== 6) return;
                
                const btn = document.getElementById('btnVerifyOtp');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Verifying...';
                forgotStep2Alert.classList.add('d-none');
                
                fetch("{{ route('password.otp.verify') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: currentEmail, otp: otp })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    currentOtp = otp;
                    forgotStep2.classList.add('d-none');
                    forgotStep3.classList.remove('d-none');
                    
                    forgotStep3Alert.className = 'alert alert-success';
                    forgotStep3Alert.textContent = data.message || 'OTP verified successfully.';
                    forgotStep3Alert.classList.remove('d-none');
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-check-circle"></i> Verify Code';
                    forgotStep2Alert.className = 'alert alert-danger';
                    forgotStep2Alert.textContent = err.message || (err.errors && err.errors.otp ? err.errors.otp[0] : 'Invalid OTP.');
                    forgotStep2Alert.classList.remove('d-none');
                });
            });
        }

        // Resend OTP
        const resendBtn = document.getElementById('btnResendOtp');
        if (resendBtn) {
            resendBtn.addEventListener('click', function() {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Resending...';
                forgotStep2Alert.classList.add('d-none');
                
                fetch("{{ route('password.otp.send') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: currentEmail })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-rotate"></i> Resend OTP';
                    forgotStep2Alert.className = 'alert alert-success';
                    forgotStep2Alert.textContent = 'A new OTP has been sent successfully.';
                    forgotStep2Alert.classList.remove('d-none');
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-rotate"></i> Resend OTP';
                    forgotStep2Alert.className = 'alert alert-danger';
                    forgotStep2Alert.textContent = err.message || 'Failed to resend OTP.';
                    forgotStep2Alert.classList.remove('d-none');
                });
            });
        }

        // Step 3: Reset Password
        if (forgotStep3Form) {
            forgotStep3Form.addEventListener('submit', function(e) {
                e.preventDefault();
                const password = forgotNewPasswordInput.value;
                const confirmPassword = forgotConfirmPasswordInput.value;
                
                if (password.length < 8) {
                    forgotStep3Alert.className = 'alert alert-danger';
                    forgotStep3Alert.textContent = 'Password must be at least 8 characters.';
                    forgotStep3Alert.classList.remove('d-none');
                    return;
                }
                
                if (password !== confirmPassword) {
                    forgotStep3Alert.className = 'alert alert-danger';
                    forgotStep3Alert.textContent = 'Passwords do not match.';
                    forgotStep3Alert.classList.remove('d-none');
                    return;
                }
                
                const btn = document.getElementById('btnResetPassword');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Resetting...';
                forgotStep3Alert.classList.add('d-none');
                
                fetch("{{ route('password.reset.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: currentEmail,
                        otp: currentOtp,
                        password: password,
                        password_confirmation: confirmPassword
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-lock"></i> Update Password';
                    forgotStep3.classList.add('d-none');
                    forgotStep4.classList.remove('d-none');
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-lock"></i> Update Password';
                    forgotStep3Alert.className = 'alert alert-danger';
                    forgotStep3Alert.textContent = err.message || (err.errors && err.errors.password ? err.errors.password[0] : 'Failed to reset password.');
                    forgotStep3Alert.classList.remove('d-none');
                });
            });
        }

        // Step 4 close & reload
        const successCloseBtn = document.getElementById('btnSuccessClose');
        if (successCloseBtn) {
            successCloseBtn.addEventListener('click', function() {
                closeForgotModal();
                location.reload();
            });
        }
    });
</script>
