<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | OrboOne HRMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body{margin:0;min-height:100vh;font-family:Arial,Helvetica,sans-serif;background:linear-gradient(135deg,#f8faff 0%,#f3f4ff 45%,#fcf6ff 100%);display:flex;align-items:center;justify-content:center;padding:18px;color:#111827;}
        .auth-card{width:100%;max-width:430px;background:#fff;border:1px solid #E7EAF3;border-radius:24px;box-shadow:0 22px 44px rgba(15,23,42,.10);padding:26px;}
        h1{margin:0 0 6px;font-size:26px;font-weight:900;}
        p{margin:0 0 20px;color:#667085;font-size:14px;line-height:1.6;}
        label{display:block;margin:0 0 6px;color:#667085;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.4px;}
        input{width:100%;height:44px;border-radius:12px;border:1px solid #E7EAF3;background:#F9FAFB;padding:8px 12px;font-size:14px;box-sizing:border-box;margin-bottom:12px;}
        button,.auth-link{width:100%;min-height:44px;border:0;border-radius:12px;margin-top:8px;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;font-weight:800;}
        button{background:linear-gradient(135deg,#4B00E8,#8600EE);color:#fff;cursor:pointer;}
        .auth-link{color:#4B00E8;background:#F4F2FF;}
        .alert{border-radius:12px;padding:10px 12px;margin-bottom:14px;font-size:13px;font-weight:700;}
        .alert-danger{background:#FEF3F2;color:#B42318;}
        .alert-success{background:#ECFDF3;color:#067647;}
    </style>
</head>
<body>
    <div class="auth-card">
        <h1>Verify OTP</h1>
        <p>Enter the OTP sent to your email.</p>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <form action="{{ route('password.otp.verify') }}" method="POST">
            @csrf
            <label>Email</label>
            <input type="email" name="email" value="{{ $email }}" required>
            <label>OTP</label>
            <input type="text" name="otp" inputmode="numeric" maxlength="6" value="{{ old('otp') }}" required>
            <button type="submit"><i class="fas fa-check-circle"></i> Verify OTP</button>
        </form>

        <a href="{{ route('password.forgot') }}" class="auth-link">Request New OTP</a>
    </div>
</body>
</html>
