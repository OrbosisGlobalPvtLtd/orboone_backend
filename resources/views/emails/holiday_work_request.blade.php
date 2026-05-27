@php
$actionUrl = $action_url ?? '#';
$status = strtolower($status ?? 'submitted');
$themeColor = '#4B00E8';
$gradientStart = '#2600A8';
$gradientEnd = '#A100F2';
$statusLabel = 'New Work Request';

if ($status === 'approved') {
    $themeColor = '#10B981';
    $gradientStart = '#059669';
    $gradientEnd = '#34D399';
    $statusLabel = 'Work Request Approved';
} elseif ($status === 'rejected') {
    $themeColor = '#EF4444';
    $gradientStart = '#DC2626';
    $gradientEnd = '#F87171';
    $statusLabel = 'Work Request Rejected';
}
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject ?? 'Work Request Update' }}</title>
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
            background: linear-gradient(135deg, {{ $gradientStart }} 0%, {{ $themeColor }} 45%, {{ $gradientEnd }} 100%);
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
            background: {{ $status === 'approved' ? '#ECFDF5' : ($status === 'rejected' ? '#FEF2F2' : '#F4EDFF') }};
            border: 1px solid {{ $status === 'approved' ? '#A7F3D0' : ($status === 'rejected' ? '#FCA5A5' : '#E4D4FF') }};
            color: {{ $themeColor }};
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 22px;
        }
        h2 {
            margin: 0 0 14px;
            font-size: 24px;
            line-height: 1.25;
            color: #101828;
        }
        p {
            margin: 0 0 14px;
            font-size: 15px;
            line-height: 1.75;
            color: #596579;
        }
        .card {
            margin: 26px 0 22px;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid {{ $status === 'approved' ? '#A7F3D0' : ($status === 'rejected' ? '#FCA5A5' : '#D8C8FF') }};
            background: #fff;
        }
        .card-head {
            padding: 20px 22px;
            background: linear-gradient(135deg, {{ $status === 'approved' ? '#F0FDF4' : ($status === 'rejected' ? '#FEF2F2' : '#F7F2FF') }}, #FFF);
            border-bottom: 1px solid {{ $status === 'approved' ? '#A7F3D0' : ($status === 'rejected' ? '#FCA5A5' : '#E6DAFF') }};
        }
        .card-head h3 {
            margin: 0;
            font-size: 18px;
            color: #101828;
        }
        .card-head p {
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
            width: 40%;
            color: #667085;
            font-weight: 700;
        }
        .value {
            color: #101828;
            font-weight: 800;
            word-break: break-word;
        }
        .note {
            padding: 16px 18px;
            border-radius: 16px;
            background: #FFF8E8;
            border: 1px solid #FFDFA8;
            color: #7A4B00;
            font-size: 14px;
            line-height: 1.65;
            margin-top: 15px;
        }
        .btn-wrap {
            text-align: center;
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            padding: 15px 34px;
            border-radius: 999px;
            background: linear-gradient(135deg, {{ $themeColor }}, {{ $gradientEnd }});
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
                <h1>OrboOne HRMS</h1>
                <p>
                    Premium Human Resource Management System<br>
                    Powered by Orbosis Global Pvt. Ltd.
                </p>
            </div>

            <div class="content">
                <span class="badge">{{ $statusLabel }}</span>

                @if($status === 'submitted')
                    <h2>New Work Request Submitted</h2>
                    <p>
                        A new Holiday/Weekoff work request has been submitted by <strong>{{ $employee_name }}</strong> and is pending your review.
                    </p>
                @elseif($status === 'approved')
                    <h2>Work Request Approved</h2>
                    <p>
                        Dear <strong>{{ $employee_name }}</strong>, your Holiday/Weekoff work request has been approved.
                    </p>
                @elseif($status === 'rejected')
                    <h2>Work Request Rejected</h2>
                    <p>
                        Dear <strong>{{ $employee_name }}</strong>, your Holiday/Weekoff work request has been rejected.
                    </p>
                @endif

                <div class="card">
                    <div class="card-head">
                        <h3>Request Details</h3>
                        <p>Summary of the submitted work request.</p>
                    </div>

                    <table class="info-table">
                        <tr>
                            <td class="label">Employee Name</td>
                            <td class="value">{{ $employee_name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Employee Code</td>
                            <td class="value">{{ $employee_code }}</td>
                        </tr>
                        @if(!empty($department))
                        <tr>
                            <td class="label">Department</td>
                            <td class="value">{{ $department }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Work Date</td>
                            <td class="value">{{ $worked_date }}</td>
                        </tr>
                        <tr>
                            <td class="label">Work Type</td>
                            <td class="value">{{ $work_type }}</td>
                        </tr>
                        @if(!empty($work_mode))
                        <tr>
                            <td class="label">Work Mode</td>
                            <td class="value">{{ $work_mode }}</td>
                        </tr>
                        @endif
                        @if(!empty($reason))
                        <tr>
                            <td class="label">Reason</td>
                            <td class="value">{{ $reason }}</td>
                        </tr>
                        @endif
                        @if($status === 'rejected' && !empty($rejection_reason))
                        <tr>
                            <td class="label">Rejection Reason</td>
                            <td class="value" style="color: #EF4444;">{{ $rejection_reason }}</td>
                        </tr>
                        @endif
                        @if(!empty($reviewer_name))
                        <tr>
                            <td class="label">Reviewed By</td>
                            <td class="value">{{ $reviewer_name }}</td>
                        </tr>
                        @endif
                    </table>
                </div>

                @if($status === 'approved')
                    <div class="note">
                        <strong>Note:</strong> Comp off will be generated after you complete attendance on the approved date(s). If attendance is already completed and eligible, comp off may already be generated.
                    </div>
                @endif

                @if(!empty($actionUrl) && $actionUrl !== '#')
                    <div class="btn-wrap">
                        <a href="{{ $actionUrl }}" class="btn">
                            {{ $status === 'submitted' ? 'Review Work Request' : 'View in HRMS' }}
                        </a>
                    </div>
                @endif

                <div class="help">
                    If you have any questions, please contact the HR department.
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
