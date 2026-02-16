<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Tracking Number - {{ $booking->bookNo }}</title>
 <style>
        @page {
    margin: 0px; /* removes all default PDF margins */
}
body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    font-size: 12px;
}
.page {
    width: 100%;
    padding: 0;
    box-sizing: border-box;
}
        .logo{
            float: left;
        }
       

       .header {
    display: flex;
    align-items: center; /* vertically center all items */
    justify-content: flex-start; /* items from left */
    gap: 20px; /* space between logo, company, and tracking */
    margin-bottom: 30px;
}

.logo img {
    height: 50px;
    width: auto;
    margin: 0; /* remove previous large top/left margins */
    margin-left: -60px;
    margin-top:150px;
    width: 150px;
}

.company {
    font-size: 11px;
    margin-top:60px;
    line-height: 1.2;
    margin-top:150px;
    text-align: right; /* align text left */
    height: 30px;
}

.tracking {
    font-size: 12px;
    font-weight: bold;
     margin-left: 120px;
     /* text-align: center; */
     margin-right: 230px;
}
        /* Main container: left boxes + right barcode */
        .main {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        /* Left side: stacked boxes */
        .left {
            flex: 1;              /* take all remaining space */
            display: flex;
            flex-direction: column;
            gap: 15px;            /* spacing between boxes */
        }

        /* Boxes */
        .box {

            /* border: 1px solid #000; */
            /* padding: 5px; */
            padding-top: 15px; /* extra top padding to make space for title */
            overflow: hidden;
            width: 100%;
            /* margin-left: -60px; */
            /* margin-bottom: 5px; */
            /* padding-bottom: 5px;  */
            position: relative; /* for absolute positioning of title */
        }

      .box-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #000;
            color: #fff;
            text-align: center;
            margin: 0;
            position: absolute; /* absolute inside box */
            top: 0;
            left: 0;
            width: 100%; /* stretch full width */
            box-sizing: border-box;
            height: 28px; 
            line-height: 22px; 
        }

        table {
            width: 95%;
            border-collapse: collapse;
            margin: 10px auto;
        }

        td {
            font: 10px Arial, sans-serif;
            /* padding: 4px 6px; */
            
            /* vertical-align: middle; */
        }
          /* Barcode & QR below header */
   .barcode-section {
    display: flex;               /* Flex use karen side-by-side alignment ke liye */
    justify-content: flex-start; /* dono left se start ho */
    align-items: center;         /* vertically center */
    gap: 20px;                   /* dono ke beech gap */
    margin-top: 20px;
}

    .barcode-box {
    flex: 0 0 auto;              /* size content ke according */
    /* border: 1px solid #000; */
    text-align: center;
    background-color: #fff;
    margin-left: -150px;
}

    .barcode {
        margin: 0 auto 10px;
        
    }

    .barcode-number {
        font-size: 14px;
        margin-top: 5px;
    }

    .qrcode img {
        width: 500px;
        height: 100px;
    }

    .tracking {
        font-size: 14px;
        font-weight: bold;
        margin: 20px 0;
    }

        td:first-child {
            width: 45%;
            margin-left: -10%;
            /* font-weight: bold; */
            white-space: nowrap;
            padding-left: 0px;
        }

        .box input[type="text"] {
            width: 100%;          /* slightly less than 100% so it doesn't overflow */
            border: none;
            border-bottom: 1px solid #000;
            padding: 2px 2px;    /* reduce right padding */
            background: transparent;
            /* font-size: 12px; */
            margin-left: 0;      /* move input to left */
            box-sizing: border-box;
        }
         body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        margin: 0;
        padding: 10px 20px;
    }

    /* Header: logo + company info */
    .header {
        overflow: hidden;
        margin-bottom: 10px;
    }

    .logo {
        float: left;
    }

    .logo img {
        height: 50px;
        width: auto;
    }

    .company {
        float: right;
        font-size: 11px;
        line-height: 1.4;
        text-align: right;
    }

    .clear {
        clear: both;
    }


        

        /* Footer */
        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 20px;
            color: #555;
        }
            /* Completely left-align inputs in this specific table */
        .left-align-inputs td:last-child {
            padding-left: 0 !important;  
        }
        
        .left-align-inputs td:last-child input {
            width: 80%;       
            margin: 0;         
            padding: 2px 0;    
            box-sizing: border-box;
        }
        .input-address {
    width: 500px;
    border: none;
    border-bottom: 1px solid #000;
    background: transparent;
    font-size: 10px;
    font-family: Arial, sans-serif;
    resize: none;
    overflow: hidden;
    line-height: 1.4;
    height: auto;
}
    </style>
</head>
<body>
  <!-- Header: Logo + Company -->
<table style="width:100%; border-collapse:collapse; margin-bottom:1px; margin-left:-50px; transform:translateY(10px);">
    <tr>
        <td style="width:600px; vertical-align:middle; padding-top:-10px;">
            <img src="file://{{ str_replace('\\', '/', public_path('dashboard-assets/images/logo3.png')) }}"
                 alt="Logo"
                 style="height:110px; width:110px; margin-left:50px; margin-top:10px;">
        </td>
        <td style="white-space:nowrap; padding-left:0; padding-top:20px; margin-left:-100px;">
    <div style="font-size:14px; line-height:1.2; margin-left:-180px;">
        Airborn Courier Express
    </div>
    <div style="font-size:11px; line-height:1.2; margin-left:-180px;">
        Office# SB 26/1, Mumtaz Square
    </div>
    <div style="font-size:11px; line-height:1.2; margin-left:-160px;">
        Ground Floor, Block K
    </div>
    <div style="font-size:11px; line-height:1.2; margin-left:-160px;">
        North Nazimabad, Karachi
    </div>
    <div style="font-size:10px; line-height:1.2; margin-left:-135px;">
        info@airborncx.com
    </div>
    <div style="font-size:10px; line-height:1.2; margin-left:-145px;">
        accounts@airborncx.com
    </div>
    <div style="font-size:11px; line-height:1.2; margin-left:-118px;">
        +92-339-2472676
    </div>
</td>

    </tr>
</table>


    <!-- Tracking Number -->
    <div style="text-align:left; font-weight:bold; font-size:28px; margin-top:-40px; margin-left:170px; position:relative; top:20px;">
    {{ $booking->bookNo }}
</div>

   <!-- Barcode & QR code slightly lower -->
<table style="width:100%; border-collapse:collapse; margin-bottom:15px;">
<tr>
    <td style="text-align:left; padding-top:0px;">
        <div class="barcode-box" style="padding:10px; display:inline-block; margin-left:15px; margin-bottom:-10px;">
            <div class="barcode">
                {!! DNS1D::getBarcodeHTML($booking->bookNo, 'C128', 1.6, 50) !!}
                <div class="barcode-number">{{ $booking->bookNo }}</div>
            </div>
            {{-- <div class="barcode-number">{{ $booking->bookNo }}</div> --}}
        </div>
    </td>
    <td style="text-align:right; padding-top:5px; vertical-align:top;">
    @if(!empty($booking->qr_path) && file_exists($booking->qr_path))
        <div class="qrcode" 
             style="display:inline-block; width:60px; height:60px; margin-right:10px;">
            <img src="file://{{ $booking->qr_path }}" 
                 style="width:60px; height:60px; object-fit:contain;">
        </div>
    @else
        <div class="qrcode" 
             style="width:100px; height:100px; border:1px dashed red; 
                    display:flex; align-items:center; justify-content:center; 
                    color:red; font-size:10px; margin-right:40px;">
            QR not loaded
        </div>
    @endif
</td>
</tr>
</table>
<div class="page">


    <!-- Main container -->
       <div class="main">
        <div class="left" style="margin-top:-20px;">
            <!-- Booking Details -->
            <div class="box">
                <div class="box-title">Booking Details</div>
                <table>
                    <tr>
                        <td>
                            <table style="margin-left:-10px;">
                                <tr><td>Book Date:</td><td><input type="text" value="{{ \Carbon\Carbon::parse($booking->bookDate)->format('d-M-Y') }}"></td></tr>
                                <tr><td>Account Code:</td><td><input type="text" value="{{ $booking->customer->code ?? '-' }}"></td></tr>
                                <tr><td>Account Name:</td><td><input type="text" value="{{ $booking->customer->customer_name ?? '-' }}"></td></tr>
                                <tr><td>Service:</td><td><input type="text" value="{{ $booking->service ?? '-' }}"></td></tr>
                                <tr><td>Origin:</td><td><input type="text" value="{{ $booking->origin ?? '-' }}"></td></tr>
                                <tr><td>Item Content:</td><td><input type="text" value="{{ $booking->itemContent ?? '-' }}"></td></tr>
                            </table>
                        </td>
                        <td>
                            <table style="margin-left:60px;">
                                <tr><td>Weight (KG):</td><td><input type="text" value="{{ $booking->weight ?? '-' }}" style="width:145px;"></td></tr>
                                <tr><td>Pieces</td><td><input type="text" value="{{ $booking->pieces ?? '-' }}"style="width:145px;"></td></tr>
                                <tr><td>Order No.:</td><td><input type="text" value="{{ $booking->orderNo ?? '-' }}"style="width:145px;"></td></tr>
                                <tr><td>Payment Mode:</td><td><input type="text" value="{{ $booking->paymentMode ?? '-' }}"style="width:145px;"></td></tr>
                                <tr><td>Destination:</td><td><input type="text" value="{{ $booking->destination ?? ($booking->destinationCountry . '-') }}"style="width:145px;"></td></tr>
                                <tr><td>Item Detail:</td><td><input type="text" value="{{ $booking->itemDetail ?? '-' }}"style="width:145px;"></td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Shipper Details -->
            <div class="box">
                <div class="box-title">Shipper Details</div>
                <table style="margin-left:3px;">
                    <tr><td>Name:</td><td><input type="text" value="{{ $booking->shipperCompany ?? '-' }}" style="margin-left:-2px; width:600px"></td></tr>
                    <tr><td>Email:</td><td><input type="text" value="{{ $booking->shipperEmail ?? '-' }}"style="margin-left:-2px; width:600px"></td></tr>
                    <tr>
                      <td>Contact No.:</td>
                      <td>
                        <table style="width:100%; border-collapse:collapse;">
                          <tr>
                            <td><input type="text" value="{{ $booking->shipperNumber ?? '-' }}" style="width:200px;"></td>
                             <td style="padding-left:10px; white-space:nowrap;">Contact Person:</td>
                            <td><input type="text" value="{{ $booking->shipperName ?? '-' }}" style="width:300px;"></td>
                          </tr>
                        </table>
                      </td>
                    </tr>

                    
                   <td>Address:</td>
                    <td>
                      <textarea class="input-address" style="width:600px;">{{ $booking->shipperAddress ?? '-' }}</textarea>
                    </td>
                </table>
            </div>

            <!-- Consignee Details -->
            <div class="box">
                <div class="box-title">Consignee Details</div>
                <table style="margin-left:3px;">
                    <tr><td>Name:</td><td><input type="text" value="{{ $booking->consigneeCompany ?? '-' }}" style="margin-left:-2px; width:600px"></td></tr>
                    <tr><td>Email:</td><td><input type="text" value="{{ $booking->consigneeEmail ?? '-' }}" style="margin-left:-2px; width:600px"></td></tr>
                    <tr>
                  <td>Contact No.:</td>
                  <td>
                    <table style="width:100%; border-collapse:collapse;">
                      <tr>
                        <td><input type="text" value="{{ $booking->consigneeNumber ?? '-' }}" style="width:200px;"></td>
                         <td style="padding-left:10px; white-space:nowrap;">Contact Person:</td>
                        <td><input type="text" value="{{ $booking->consigneeName ?? '-' }}" style="width:300px;"></td>
                      </tr>
                    </table>
                  </td>
                </tr>

                   <tr>
  <td>Address:</td>
  <td>
    <textarea class="input-address" style="width:600px;">{{ $booking->consigneeAddress ?? '-' }}</textarea>
  </td>
</tr>
                </table>
            </div>
        </div>
 <div class="clearfix"></div>
    </div>
</div> <!-- end page -->
</body>
</html>
