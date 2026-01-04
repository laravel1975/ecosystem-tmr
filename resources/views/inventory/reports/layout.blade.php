<!DOCTYPE html>
<html lang="th">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Document</title>
    <style>
        /* Note: DomPDF รองรับ CSS แบบจำกัด
           แนะนำให้ใช้ Table Layout แทน Flexbox
        */
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            /* ⚠️ คุณอาจต้องดาวน์โหลดฟอนต์มาใส่ใน storage/fonts และชี้ path ให้ถูก */
            /* src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format('truetype'); */
        }

        body {
            font-family: "THSarabunNew", "Garuda", "sans-serif"; /* Fallback fonts */
            font-size: 14px;
            line-height: 1.4;
            color: #333;
        }

        .header-table { width: 100%; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header-title { font-size: 24px; font-weight: bold; text-transform: uppercase; text-align: right; }
        .company-info { font-size: 16px; font-weight: bold; }

        .info-table { width: 100%; margin-bottom: 20px; }
        .info-label { font-weight: bold; width: 120px; vertical-align: top; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background-color: #f0f0f0; border-bottom: 1px solid #000; padding: 8px; text-align: left; font-weight: bold; }
        .items-table td { border-bottom: 1px solid #ddd; padding: 8px; vertical-align: top; }
        .items-table .qty { text-align: right; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; height: 100px; }
        .signature-area { width: 100%; margin-top: 50px; }
        .sign-box { float: left; width: 30%; text-align: center; margin-right: 3%; }
        .sign-line { border-bottom: 1px solid #000; margin-bottom: 5px; height: 30px; }

        .badge { padding: 2px 6px; border: 1px solid #000; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="60%" valign="top">
                <div class="company-info">TMR ECOSYSTEM</div>
                <div>123 Warehouse St., Bangkok, Thailand</div>
                <div>Tel: 02-123-4567</div>
            </td>
            <td width="40%" valign="top" align="right">
                <div class="header-title">@yield('title')</div>
                <div>Reference: <strong>{{ $transfer->reference }}</strong></div>
                <div>Date: {{ \Carbon\Carbon::parse($transfer->scheduled_date)->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    @yield('content')

    <div class="footer">
        <div style="font-size: 10px; text-align: right; border-top: 1px solid #ccc; padding-top: 5px;">
            Printed by System | Page <span class="page-number"></span>
        </div>
    </div>

</body>
</html>
