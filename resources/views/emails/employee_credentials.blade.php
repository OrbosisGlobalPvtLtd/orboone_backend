<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Welcome to OrboOne HRMS</title>

<style>
body{
    margin:0;
    padding:0;
    background:#eef2f7;
    font-family:Arial, Helvetica, sans-serif;
    color:#1f2937;
}

.email-wrapper{
    width:100%;
    padding:28px 12px;
    box-sizing:border-box;
}

.email-container{
    width:100%;
    max-width:680px;
    margin:0 auto;
    background:#ffffff;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 18px 45px rgba(15,23,42,0.12);
    border:1px solid #e5e7eb;
}

.header{
    background:linear-gradient(135deg,#4B00E8,#1560ab,#8600EE);
    padding:34px 28px;
    text-align:center;
}

.logo-box{
    width:82px;
    height:82px;
    margin:0 auto 14px;
    background:#ffffff;
    border-radius:22px;
    display:flex;
    align-items:center;
    justify-content:center;
    box-shadow:0 12px 28px rgba(0,0,0,0.18);
}

.logo-box img{
    max-height:58px;
    max-width:58px;
}

.header h1{
    margin:0;
    color:#ffffff;
    font-size:26px;
    font-weight:700;
    letter-spacing:.2px;
}

.header p{
    margin:8px 0 0;
    color:rgba(255,255,255,.88);
    font-size:14px;
}

.content{
    padding:34px 34px 30px;
}

.badge{
    display:inline-block;
    background:#f4f2ff;
    color:#4B00E8;
    border:1px solid #e6ddff;
    padding:7px 13px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
    letter-spacing:.4px;
    text-transform:uppercase;
    margin-bottom:18px;
}

.content h2{
    margin:0 0 14px;
    color:#101828;
    font-size:24px;
    line-height:1.35;
}

.content p{
    margin:0 0 14px;
    font-size:15px;
    line-height:1.7;
    color:#556070;
}

.employee-name{
    color:#101828;
    font-weight:700;
}

.credential-card{
    margin:26px 0;
    border-radius:16px;
    border:1px solid #e7eaf3;
    background:linear-gradient(180deg,#ffffff,#fafbff);
    box-shadow:0 10px 28px rgba(16,24,40,.07);
    overflow:hidden;
}

.credential-title{
    padding:18px 22px;
    background:#f8f7ff;
    border-bottom:1px solid #e7eaf3;
}

.credential-title h3{
    margin:0;
    color:#101828;
    font-size:17px;
}

.credential-title p{
    margin:4px 0 0;
    font-size:13px;
    color:#667085;
}

.credential-table{
    width:100%;
    border-collapse:collapse;
}

.credential-table td{
    padding:16px 22px;
    border-bottom:1px solid #eef0f5;
    font-size:14px;
    vertical-align:middle;
}

.credential-table tr:last-child td{
    border-bottom:none;
}

.credential-label{
    width:42%;
    color:#667085;
    font-weight:600;
}

.credential-value{
    color:#101828;
    font-weight:700;
    word-break:break-word;
}

.password-value{
    color:#4B00E8;
    background:#f4f2ff;
    padding:8px 12px;
    border-radius:8px;
    display:inline-block;
    letter-spacing:.3px;
}

.security-note{
    background:#fff8e6;
    border:1px solid #ffe3a3;
    color:#7a4b00;
    padding:14px 16px;
    border-radius:12px;
    font-size:14px;
    line-height:1.6;
    margin-top:18px;
}

.btn-wrap{
    text-align:center;
    margin-top:28px;
}

.login-btn{
    display:inline-block;
    background:linear-gradient(135deg,#4B00E8,#1560ab);
    color:#ffffff !important;
    padding:14px 30px;
    border-radius:10px;
    text-decoration:none;
    font-size:15px;
    font-weight:700;
    box-shadow:0 10px 22px rgba(75,0,232,.25);
}

.help-text{
    text-align:center;
    margin-top:18px !important;
    font-size:13px !important;
    color:#7b8494 !important;
}

.footer{
    background:#f8fafc;
    border-top:1px solid #e7eaf3;
    text-align:center;
    padding:22px 26px;
}

.footer strong{
    color:#101828;
    font-size:14px;
}

.footer p{
    margin:6px 0 0;
    font-size:12px;
    color:#7b8494;
    line-height:1.6;
}

@media only screen and (max-width:600px){
    .content{
        padding:26px 20px;
    }

    .header{
        padding:30px 20px;
    }

    .credential-table td{
        display:block;
        width:100%;
        padding:10px 18px;
        box-sizing:border-box;
    }

    .credential-label{
        padding-bottom:3px !important;
    }

    .credential-value{
        padding-top:3px !important;
    }
}
</style>
</head>

<body>

<div class="email-wrapper">
    <div class="email-container">

        <div class="header">
            <div class="logo-box">
                <img src="https://orbosis.in/public/images/Picsart_26-04-02_12-19-10-396.png" alt="Orbosis Logo">
            </div>
            <!-- <h1>OrboOne HRMS</h1> -->
            <!-- <p>Powered by Orbosis Global Pvt, Ltd</p> -->
        </div>

        <div class="content">

            <span class="badge">Employee Account Created</span>

            <h2>Welcome to Orbosis Global Pvt, Ltd</h2>

            <p>Hello <span class="employee-name">{{ $name }}</span>,</p>

            <p>
                We are pleased to welcome you to <strong>Orbosis Global Pvt, Ltd</strong>.
                Your employee account has been successfully created on <strong>OrboOne HRMS</strong>.
            </p>

            <p>
                Please use the credentials below to access your employee portal.
            </p>

            <div class="credential-card">

                <div class="credential-title">
                    <h3>Your Login Credentials</h3>
                    <p>Use these details to securely access your OrboOne HRMS account.</p>
                </div>

                <table class="credential-table">
                    <tr>
                        <td class="credential-label">Email Address</td>
                        <td class="credential-value">{{ $email }}</td>
                    </tr>

                    <tr>
                        <td class="credential-label">Employee Number</td>
                        <td class="credential-value">{{ $empid }}</td>
                    </tr>

                    <tr>
                        <td class="credential-label">Temporary Password</td>
                        <td class="credential-value">
                            <span class="password-value">{{ $password }}</span>
                        </td>
                    </tr>
                </table>

            </div>

            <div class="security-note">
                For your account security, please change your password after your first login and do not share your credentials with anyone.
            </div>

            <div class="btn-wrap">
                @if(! empty($passwordSetupUrl))
                    <a href="{{ $passwordSetupUrl }}" class="login-btn">Set Your Password</a>
                @else
                    <a href="{{ route('login') }}" class="login-btn">Login to OrboOne HRMS</a>
                @endif
            </div>

            <p class="help-text">
                If you face any issue while logging in, please contact the HR department.
            </p>

        </div>

        <div class="footer">
            <strong>Orbosis Global Pvt, Ltd</strong>
            <p>
                © {{ date('Y') }} Orbosis Global Pvt, Ltd. All rights reserved.<br>
                OrboOne Human Resource Management System
            </p>
        </div>

    </div>
</div>

</body>
</html>