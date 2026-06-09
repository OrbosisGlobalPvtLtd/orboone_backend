<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $mailTitle ?? branding_name() }}</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f3f5f9;
            font-family: Arial, Helvetica, sans-serif;
            color: #101828;
        }

        .wrap {
            width: 100%;
            padding: 16px 10px;
            box-sizing: border-box;
        }

        .card {
            max-width: 680px;
            margin: 0 auto;
            background: linear-gradient(
                135deg,
                {{ branding_primary_color() }} 0%,
                {{ branding_secondary_color() }} 100%
            );
            border-radius: 24px;
            padding: 16px 6px;
            box-sizing: border-box;
        }

        .card-badge {
            text-align: center;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 6px 0 16px;
        }

        .inner-card {
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
        }

        .hero {
            text-align: center;
            background: #f9fafb;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .hero-logo-container {
            text-align: center;
        }

        .hero-logo {
            display: inline-block;
            width: auto;
            height: 65px;
            max-width: 100%;
            object-fit: contain;
            border: 0;
            outline: none;
            text-decoration: none;
        }

        .body {
            padding: 24px 20px;
        }

        .footer {
            border-top: 1px solid #e4e7ec;
            padding: 20px;
            text-align: center;
        }

        .footer p {
            margin: 4px 0;
            color: #667085;
            font-size: 12px;
            line-height: 1.5;
        }

        .footer strong {
            color: #344054;
        }

        .badge {
            display: inline-block;
            background: #e6f0ff;
            color: #0b5fff;
            border: 1px solid #c8dcff;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        table.meta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        table.meta td {
            border-bottom: 1px solid #eef2f6;
            padding: 10px 0;
            vertical-align: top;
            font-size: 14px;
            line-height: 1.5;
        }

        table.meta td.label {
            width: 36%;
            color: #667085;
            font-weight: 700;
            padding-right: 10px;
        }

        table.meta td.value {
            color: #101828;
            font-weight: 600;
            word-break: break-word;
        }

        .btn-wrap {
            margin-top: 18px;
            text-align: center;
        }

        .btn {
            display: inline-block;
            background: #0b5fff;
            color: #ffffff !important;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 13px;
            line-height: 1.2;
        }

        @media only screen and (max-width: 600px) {
            .wrap {
                padding: 10px 8px;
            }

            .card {
                border-radius: 16px;
                padding: 10px 4px;
            }

            .card-badge {
                padding: 4px 0 10px;
                font-size: 10px;
            }

            .inner-card {
                border-radius: 12px;
                padding: 0;
            }

            .hero {
                padding: 12px 10px;
            }

            .hero-logo {
                height: 50px;
            }

            .body {
                padding: 16px 12px;
            }

            .footer {
                padding: 16px 12px 12px;
                margin-top: 0;
            }

            table.meta td {
                display: block;
                width: 100%;
                padding: 6px 0;
                border-bottom: 0;
            }

            table.meta tr {
                display: block;
                border-bottom: 1px solid #eef2f6;
                padding: 8px 0;
            }

            table.meta td.label {
                width: 100%;
                padding-right: 0;
                font-size: 12px;
            }

            table.meta td.value {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="card">
            <!-- Top badge text in the gradient header area -->
            <!-- <div class="card-badge">
                {{ branding_name() }}
            </div> -->

            <!-- Inner White Card containing logo, body, and footer -->
            <div class="inner-card">
                <div class="hero">
                    <div class="hero-logo-container">
                        <img
                            src="{{ branding_logo_url_or_embed($message ?? null) }}"
                            alt="{{ branding_name() }}"
                            class="hero-logo">
                    </div>
                </div>

                <div class="body">
                    @yield('content')
                </div>

                <div class="footer">
                    <p><strong>{{ company_name() }}</strong></p>
                    <p>{{ config('app.url') }}</p>
                    <p>Support: {{ config('hrms.emails.support') ?: config('mail.from.address') }}</p>
                    <p>&copy; {{ date('Y') }} {{ company_name() }}. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>