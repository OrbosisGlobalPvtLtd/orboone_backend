<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | {{ $branding['company_name'] ?? config('app.name', 'OrboOne') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
            --orb-secondary: {{ $branding['secondary_color'] ?? '#8E2DE2' }};
            --text-dark: #111827;
            --text-soft: #4b5563;
            --border: #e2e8f0;
            --bg-light: #f8fafc;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', Arial, sans-serif;
            background: 
                radial-gradient(circle at top left, rgba(75,0,232,.12), transparent 26%),
                radial-gradient(circle at bottom right, rgba(236,78,116,.10), transparent 26%),
                linear-gradient(135deg, #f8faff 0%, #f3f4ff 45%, #fcf6ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            color: var(--text-dark);
        }
        .auth-card {
            width: 100%;
            max-width: 460px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.92);
            border-radius: 26px;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.12);
            padding: 36px 30px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            text-align: center;
        }
        p {
            margin: 0 0 24px;
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.6;
            text-align: center;
        }
        label {
            display: block;
            margin: 0 0 6px;
            color: var(--text-soft);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .input-group-custom {
            position: relative;
            margin-bottom: 16px;
        }
        input {
            width: 100%;
            height: 46px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--bg-light);
            padding: 0 46px 0 14px;
            font-size: 14px;
            color: var(--text-dark);
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        input:focus {
            outline: none;
            border-color: var(--orb-primary);
            box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.1);
            background: #ffffff;
        }
        .eye-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            z-index: 3;
            transition: color .2s ease;
        }
        .eye-icon:hover {
            color: var(--orb-primary);
        }
        button, .auth-link {
            width: 100%;
            height: 46px;
            border: 0;
            border-radius: 12px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        button {
            background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
            color: #fff;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2);
        }
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(75, 0, 232, 0.3);
        }
        .auth-link {
            color: var(--orb-primary);
            background: #F4F2FF;
            border: 1px solid rgba(75, 0, 232, 0.08);
            cursor: pointer;
        }
        .auth-link:hover {
            background: #ebe8ff;
        }
        .alert {
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.5;
        }
        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fee2e2;
        }
        .alert-warning {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fef3c7;
        }
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <h1>Change Default Password</h1>
        <p>For security reasons, you must change your temporary password before accessing your account.</p>

        @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <form action="{{ route('profile.password.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <label>Current Password</label>
            <div class="input-group-custom">
                <input type="password" id="current_password" name="current_password" placeholder="Enter current temporary password" required>
                <span class="eye-icon" onclick="togglePasswordVisibility('current_password', this)">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>

            <label>New Password</label>
            <div class="input-group-custom">
                <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
                <span class="eye-icon" onclick="togglePasswordVisibility('password', this)">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>

            <label>Confirm New Password</label>
            <div class="input-group-custom">
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" required>
                <span class="eye-icon" onclick="togglePasswordVisibility('password_confirmation', this)">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>

            <button type="submit"><i class="fas fa-key"></i> Update Password</button>
        </form>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="auth-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <script>
        function togglePasswordVisibility(inputId, toggleEl) {
            const input = document.getElementById(inputId);
            if (input) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
                const icon = toggleEl.querySelector('i');
                if (icon) {
                    if (type === 'text') {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            }
        }
    </script>
</body>
</html>
