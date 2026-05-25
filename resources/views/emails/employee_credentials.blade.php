@php
$loginUrl = rtrim(config('app.url'), '/') . '/login';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Welcome to OrboOne</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #F5F7FC;
            font-family: Arial, Helvetica, sans-serif;
            color: #101828;
        }

        .wrapper {
            width: 100%;
            padding: 24px 10px;
            box-sizing: border-box;
        }

        .container {
            max-width: 720px;
            width: 100%;
            margin: 0 auto;
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #E7EAF3;
        }

        .hero {
            text-align: center;
            padding: 32px 20px 36px;
            background: linear-gradient(135deg, #2600A8 0%, #5B00E8 45%, #A100F2 100%);
        }

        .logo-glass {
            width: 190px;
            height: 112px;
            margin: 0 auto 22px;
            border-radius: 28px;
            background: rgba(255, 255, 255, .88);
            border: 1px solid rgba(255, 255, 255, .95);
            box-shadow: 0 16px 34px rgba(0, 0, 0, .20);
            text-align: center;
        }

        .logo-glass img {
            width: 160px;
            max-width: 160px;
            height: auto;
            margin: 22px auto 0;
            display: block;
            border: 0;
        }

        .hero h1 {
            margin: 0;
            font-size: 30px;
            line-height: 1.2;
            font-weight: 800;
            color: #fff;
        }

        .hero p {
            margin: 10px 0 0;
            font-size: 14px;
            line-height: 1.55;
            color: rgba(255, 255, 255, .90);
        }

        .content {
            padding: 34px;
        }

        .badge {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 999px;
            background: #F4EDFF;
            border: 1px solid #E4D4FF;
            color: #4B00E8;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 22px;
        }

        h2 {
            margin: 0 0 14px;
            font-size: 28px;
            line-height: 1.25;
            color: #101828;
        }

        p {
            margin: 0 0 14px;
            font-size: 15px;
            line-height: 1.75;
            color: #596579;
        }

        .credentials {
            margin: 26px 0 22px;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #D8C8FF;
            background: #fff;
        }

        .credentials-head {
            padding: 20px 22px;
            background: linear-gradient(135deg, #F7F2FF, #FFF);
            border-bottom: 1px solid #E6DAFF;
        }

        .credentials-head h3 {
            margin: 0;
            font-size: 18px;
            color: #101828;
        }

        .credentials-head p {
            margin: 5px 0 0;
            font-size: 13px;
            color: #667085;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 17px 22px;
            border-bottom: 1px solid #EEF0F6;
            font-size: 14px;
            vertical-align: middle;
        }

        .info-table tr:last-child td {
            border-bottom: 0;
        }

        .label {
            width: 42%;
            color: #667085;
            font-weight: 700;
        }

        .value {
            color: #101828;
            font-weight: 800;
            word-break: break-word;
        }

        .password {
            display: inline-block;
            padding: 9px 14px;
            border-radius: 11px;
            background: #F1E8FF;
            border: 1px solid #DECFFF;
            color: #4B00E8;
            font-weight: 800;
        }

        .note {
            padding: 16px 18px;
            border-radius: 16px;
            background: #FFF8E8;
            border: 1px solid #FFDFA8;
            color: #7A4B00;
            font-size: 14px;
            line-height: 1.65;
        }

        .btn-wrap {
            text-align: center;
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 15px 34px;
            border-radius: 999px;
            background: linear-gradient(135deg, #4B00E8, #8600EE);
            color: #fff !important;
            text-decoration: none;
            font-size: 15px;
            font-weight: 800;
        }

        .help {
            margin-top: 18px;
            text-align: center;
            font-size: 13px;
            color: #7B8494;
        }

        .footer {
            text-align: center;
            padding: 24px;
            background: #FAFBFF;
            border-top: 1px solid #E7EAF3;
        }

        .footer strong {
            display: block;
            font-size: 14px;
            color: #101828;
            margin-bottom: 6px;
        }

        .footer p {
            margin: 0;
            font-size: 12px;
            line-height: 1.7;
            color: #7B8494;
        }

        @media only screen and (max-width:600px) {
            .hero {
                padding: 24px 14px 30px !important;
            }

            .logo-glass {
                width: 160px !important;
                height: 94px !important;
                border-radius: 22px !important;
                margin-bottom: 18px !important;
                background: rgba(255, 255, 255, .92) !important;
            }

            .logo-glass img {
                width: 132px !important;
                max-width: 132px !important;
                margin-top: 18px !important;
            }

            .hero h1 {
                font-size: 23px !important;
            }

            .hero p {
                font-size: 12px !important;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">

            <div class="hero">
                <div class="logo-glass">
                    <img src="https://orbosis.in/public/images/Picsart_26-04-02_12-19-10-396.png" alt="Orbosis Logo">
                </div>

                <h1>Welcome to OrboOne HRMS</h1>
                <p>
                    Premium Human Resource Management System<br>
                    Powered by Orbosis Global Pvt. Ltd.
                </p>
            </div>

            <div class="content">
                <span class="badge">Employee Account Created</span>

                <h2>Hello {{ $name }},</h2>

                <p>
                    Welcome to <strong>Orbosis Global Pvt. Ltd.</strong>.
                    Your employee account has been created successfully on
                    <strong>OrboOne HRMS</strong>.
                </p>

                <p>
                    Please use the login credentials below to access your employee portal.
                </p>

                <div class="credentials">
                    <div class="credentials-head">
                        <h3>Your OrboOne Login Credentials</h3>
                        <p>Use these details to securely access your HRMS account.</p>
                    </div>

                    <table class="info-table">
                        <tr>
                            <td class="label">Email Address</td>
                            <td class="value">{{ $email }}</td>
                        </tr>
                        <tr>
                            <td class="label">Employee Number</td>
                            <td class="value">{{ $empid }}</td>
                        </tr>
                        <tr>
                            <td class="label">Temporary Password</td>
                            <td class="value">
                                <span class="password">{{ $password }}</span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="note">
                    For security reasons, please change your password after your first login and do not share your credentials with anyone.
                </div>

                <div class="btn-wrap">
                    <a href="{{ $loginUrl }}" class="btn">Login to OrboOne HRMS</a>
                </div>

                <div class="help">
                    If you face any issue while logging in, please contact the HR department.
                </div>
            </div>

            <div class="footer">
                <strong>Orbosis Global Pvt. Ltd.</strong>
                <p>
                    © {{ date('Y') }} Orbosis Global Pvt. Ltd. All rights reserved.<br>
                    OrboOne Human Resource Management System
                </p>
            </div>

        </div>
    </div>
</body>

</html>