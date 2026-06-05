{{-- resources/views/hrms/document-generation/pdf-templates/internship-certificate.blade.php --}}
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Internship</title>

    @php
        $isPreviewMode = $isPreview ?? false;

        $bgPath = public_path('assets/hrms/certificates/internship-certificate-bg.jpg');
        $fontPath = public_path('fonts/PinyonScript-Regular.ttf');

        if (!is_file($bgPath)) {
            throw new \Exception('Missing background image: public/assets/hrms/certificates/internship-certificate-bg.jpg');
        }

        if (!is_file($fontPath)) {
            throw new \Exception('Missing font: public/fonts/PinyonScript-Regular.ttf');
        }

        $bgSrc = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($bgPath));

        $cssFontUrl = $isPreviewMode
            ? 'data:font/truetype;base64,' . base64_encode(file_get_contents($fontPath))
            : str_replace('\\', '/', $fontPath);

        $internName = $employee_name ?? $candidate_name ?? 'Intern Name';
        $companyName = $company_name ?? 'Orbosis Global Pvt. Ltd';
        $designationText = $designation ?? 'Flutter Developer Intern';
        $startDate = $internship_start_date ?? 'Start Date';
        $endDate = $internship_end_date ?? 'End Date';

        $workText = $internship_work_summary
            ?? 'During his tenure, he worked on Flutter-based development, mobile app UI implementation, API integration, and application optimization tasks, demonstrating strong problem-solving skills and attention to detail.';

        $performanceText = $performance_summary
            ?? 'He showed dedication, professionalism, and a keen willingness to learn throughout the internship. We wish him all the best for his future endeavours.';

        $nameLength = strlen($internName);

        if ($nameLength > 25) {
            $fontSize = '52px';
            $topOffset = '112mm';
            $screenTop = '37.5%';
        } elseif ($nameLength > 20) {
            $fontSize = '68px';
            $topOffset = '108mm';
            $screenTop = '36.4%';
        } elseif ($nameLength > 15) {
            $fontSize = '80px';
            $topOffset = '106mm';
            $screenTop = '35.7%';
        } else {
            $fontSize = '94px';
            $topOffset = '104mm';
            $screenTop = '35%';
        }
    @endphp

    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        @font-face {
            font-family: 'PinyonScript';
            src: url('{{ $cssFontUrl }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
            background: #ffffff;
        }

        .certificate-container {
            position: relative;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
            background: #ffffff;
            font-size: 0;
        }

        .certificate-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            display: block;
        }

        .content {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #000000;
        }

        .intern-name {
            position: absolute;
            top: {{ $topOffset }};
            left: 0;
            width: 210mm;
            text-align: center;
            font-family: 'PinyonScript', cursive;
            font-size: {{ $fontSize }};
            font-weight: normal;
            font-style: normal;
            line-height: 0.9;
            color: #000000;
            white-space: nowrap;
        }

        .main-text {
            position: absolute;
            top: 142mm;
            left: 28mm;
            width: 154mm;
            text-align: justify;
            font-size: 15.5px;
            line-height: 1.45;
            color: #000000;
        }

        .work-text {
            position: absolute;
            top: 172mm;
            left: 28mm;
            width: 154mm;
            text-align: justify;
            font-size: 15px;
            line-height: 1.45;
            color: #000000;
        }

        .signatory {
            position: absolute;
            bottom: 27mm;
            left: 0;
            width: 210mm;
            text-align: center;
            font-size: 23px;
            font-weight: 900;
            line-height: 1.2;
            color: #000000;
        }

        strong {
            font-weight: 900;
        }

        @media screen {
            html,
            body {
                width: 100%;
                height: auto;
                overflow: visible;
                background: transparent;
            }

            .certificate-container {
                width: 100%;
                max-width: 100%;
                height: auto;
                aspect-ratio: 210 / 297;
            }

            .certificate-bg,
            .content {
                width: 100%;
                height: 100%;
            }

            .intern-name {
                top: {{ $screenTop }};
                left: 0;
                width: 100%;
                font-size: clamp(32px, 10vw, {{ $fontSize }});
                line-height: 0.9;
            }

            .main-text {
                top: 47.8%;
                left: 13.5%;
                width: 73%;
                font-size: clamp(9px, 1.45vw, 15.5px);
            }

            .work-text {
                top: 57.9%;
                left: 13.5%;
                width: 73%;
                font-size: clamp(9px, 1.35vw, 15px);
            }

            .signatory {
                bottom: 9.2%;
                width: 100%;
                font-size: clamp(14px, 2.3vw, 23px);
            }
        }
    </style>
</head>

<body>
    <div class="certificate-container">
        <img class="certificate-bg" src="{{ $bgSrc }}" alt="Certificate Background">

        <div class="content">
            <div class="intern-name">{{ $internName }}</div>

            <div class="main-text">
                has successfully completed internship program at
                <strong>{{ $companyName }}</strong>
                as a <strong>{{ $designationText }}</strong>
                from <strong>{{ $startDate }}</strong>
                to <strong>{{ $endDate }}</strong>.
            </div>

            <div class="work-text">
                {!! nl2br(e($workText)) !!}<br>
                {!! nl2br(e($performanceText)) !!}
            </div>

            <div class="signatory">
                {{ $signatory_name ?? $authorized_signatory ?? 'Prabhat Agrawal' }}<br>
                {{ $signatory_designation ?? 'CEO' }}
            </div>
        </div>
    </div>
</body>
</html>