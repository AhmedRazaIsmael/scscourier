<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Undertaking - {{ $booking->bookNo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            margin: 40px;
            color: #000;
        }

        h2 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 25px;
            position: relative;
            padding-top: 10px;
        }

        /* Full-width line above UNDERTAKING */
        h2::before {
            content: "";
            display: block;
            width: 110%;
            height: 1px;
            background-color: #000;
            margin-bottom: 10px;
            margin-left: -30px;
        }

        .booking-number {
            font-size: 20px;  /* larger but not bold */
            font-weight: normal;
        }

        .content {
            text-align: justify;
        }
        .content,p{
            font-size: 20px;  /* larger but not bold */
            font-weight: normal;
        }

        .signature {
            margin-top: 60px;
        }

        .signature table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature td {
            padding: 6px 4px;
            font-weight: normal; 
        }

       .footer {
            text-align: center;
            font-size: 11px;
            margin-top: 50px;
            border-top: 1px solid #000;   
            padding-top: 8px;
            position: relative;
            width: 110%;
            margin-left: -30px;
        }
        
        .footer::before {
            content: "";
            position: absolute;
            top: -3px;                    
            left: 0;
            width: 100%;
            height: 1px;
            background-color: #000;
        }

        
        .header-table {
            width: 100%;
            margin-bottom: 30px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .company-info {
            text-align: right;
            font-size: 12px;
            line-height: 1.4;
        }

        .company-info strong {
            font-size: 14px;
        }

        .logo img {
            height: 60px;
            width: auto;
        }
    </style>
</head>
<body>
    <table style="width:100%; border-collapse:collapse; margin-bottom:15px; margin-left: -50px;">
        <tr>
            <td style="width:600px; vertical-align:middle;">
                <img src="file://{{ str_replace('\\', '/', public_path('dashboard-assets/images/logo3.png')) }}" 
                     alt="Logo" 
                     style="height:110px; width:110px; margin-left:12px;">
            </td>
            <td style="text-align:left; font-size:11px; line-height:1.4; white-space: nowrap;">
    <div style="margin-left:-40px;">
        <strong style="font-size: 12px;">Airborn Courier Express</strong><br>
        Office# SB 26/1, Mumtaz Square, <br>Ground Floor,Block K, <br>North Nazimabad, Karachi<br>
        Helpline: +92-339-2472676<br>
        info@airborncx.com<br>
        accounts@airborncx.com
    </div>
</td>

        </tr>
    </table>

    <!-- Title -->
    <h2>UNDERTAKING</h2>

    <!-- Body Content -->
    <div class="content">
        <p>
            We hereby declare that our shipment booked under  
            <span class="booking-number">{{ $booking->bookNo }}</span>
            delivered at your operations office, does not contain any contraband material.
        </p>

        <p>
            If any contraband material (Narcotics, arms, explosives, antiques, currency, prohibited items, etc.)
            is discovered from this shipment during inspection or which may be against the export policy
            order of the Government, we shall be held responsible.
        </p>

        <p style="margin-top: 30px;">
            Yours Sincerely,
        </p>
    </div>

    <!-- Signature Table -->
    <div class="signature">
        <table>
            <tr>
                <td>Date:</td>
                <td> :  _____________________________</td>
            </tr>
            <tr>
                <td>Authorized Person Name:</td>
                <td> :  _____________________________</td>
            </tr>
            <tr>
                <td>CNIC # of Authorized Person:</td>
                <td> :  _____________________________</td>
            </tr>
            <tr>
                <td>Signature:</td>
                <td> :  _____________________________</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
         Office# SB 26/1, Mumtaz Square, Ground Floor, Block K, North Nazimabad, Karachi
    </div>

</body>
</html>
