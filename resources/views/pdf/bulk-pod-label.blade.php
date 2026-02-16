<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Bulk POD Labels</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css">
<style>
body {
    font-family: Arial, sans-serif;
    font-size: 12px;
    margin: 0;
    padding: 0;
}

.page {
    width: 100%;
    box-sizing: border-box;
    page-break-after: always;
}

.page::after {
    content: "";
    display: table;
    clear: both;
}

/* Layout */
.main {
    width: 100%;
    display: block;
}

.left {
    width: 80%;
    float: left;
    margin-left:10px;
}

.barcode-section {
    width: 30%;
    float: right;
    text-align: center;
}

/* .box {
    border: 1px solid #000;
    padding-top: 20px;
    padding-bottom: 10px;
    position: relative;
    margin-bottom: 10px;
    margin-left: -30px;
} */

.box {
    border: 1px solid #000;
    padding-top: 20px;
    overflow: hidden;
    width: 105%;
    padding-bottom: 10px;
    position: relative;
    margin-bottom: 10px;
    margin-left: -25px;
}

.box-title {
    font-size: 14px;
    font-weight: bold;
    background-color: #000;
    color: #fff;
    text-align: center;
    margin: 0;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
}

table {
    width: 95%;
    border-collapse: collapse;
    margin: 10px auto;
}

td {
    font: 10px Arial, sans-serif;
}

td:first-child {
    width: 40%;
    white-space: nowrap;
    padding-left: 0;
}
td:first-child + td { 
    padding-left: 10px; 
    width: 60%; 
}

.box input[type="text"] {
    width: 90%;
    border: none;
    border-bottom: 1px solid #000;
    background: transparent;
    box-sizing: border-box;
}

/* Barcode box */
.barcode-box {
    border: 1px solid #000;
    padding: 10px;
    width: 60%;
    box-sizing: border-box;
    background-color: #fff;
    margin-left: 88px;
    min-height: 515px;
}

.barcode {
    display: block;
    transform: rotate(90deg);
    margin: 55px 10px 10px 0px;
}

.barcode-number {
    font-size: 20px;
    margin-left: 100px;
}

.qrcode {
    margin-top: 10px;
    margin-left: 10px;
}

.qrcode img {
    width: 100px;
    height: 100px;
}

.input-address {
    width: 600px;
    border: none;
    border-bottom: 1px solid #000;
    background: transparent;
    font-size: 10px;
    font-family: Arial, sans-serif;
    resize: none;
    overflow: hidden;
    line-height: 1.2;
    height: 35px;
}
</style>
</head>
<body>

@foreach($bookings as $booking)

@php
    // Base height estimates
    $bookingBoxHeight = 172;
    $consigneeBoxHeight = 172;
    $ackBoxHeight = 150;

    // Dynamic address height (roughly 15px per 90 chars)
    $addressLength = strlen($booking->shipperAddress ?? '');
    $extraHeight = max(0, floor($addressLength / 90) * 15);

    // total
    $leftHeight = $bookingBoxHeight + $consigneeBoxHeight + $ackBoxHeight + $extraHeight;

    // keep minimum
    if ($leftHeight < 490) $leftHeight = 490;
@endphp

<div class="page" @if($loop->last) style="page-break-after:auto;" @endif>

<!-- Header -->
<table style="width:100%; border-collapse:collapse; margin-bottom:15px; margin-left:-50px;">
  <tr>
    <td style="width:600px; vertical-align:middle;">
      <img src="file://{{ str_replace('\\','/', public_path('dashboard-assets/images/logo3.png')) }}"
           style="height:110px; width:110; display:block; margin-left:12px; margin-top: -30px;">
    </td>
   <td style="text-align:left; font-size:11px; line-height:1.4; padding-left:32px; white-space:nowrap; vertical-align: bottom;">
  <strong style="font-size:12px;">Airborn Courier Express</strong><br>
   Office# SB 26/1, Mumtaz Square,<br>
  Ground Floor, Block K,<br>
  North Nazimabad, Karachi<br>
  +92-339-2472676<br>
  info@airborncx.com<br>
  accounts@airborncx.com
</td>

  </tr>
</table>

<!-- Title -->
<div style="text-align:left; font-weight:bold; font-size:16px; color:red; margin:-75px 100px 5px 280px;">
  Proof of Delivery
</div>

<!-- Tracking -->
<div style="text-align:left; font-weight:bold; font-size:14px; margin:0 100px 20px 250px;">
  Tracking Number: {{ $booking->bookNo }}
</div>

<div class="main">
  <div class="left">
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
              <tr><td>Weight (KG):</td><td><input type="text" value="{{ $booking->weight ?? '-' }}" style="width:105px;"></td></tr>
              <tr><td>Pieces:</td><td><input type="text" value="{{ $booking->pieces ?? '-' }}" style="width:105px;"></td></tr>
              <tr><td>Order No.:</td><td><input type="text" value="{{ $booking->orderNo ?? '-' }}" style="width:105px;"></td></tr>
              <tr><td>Payment Mode:</td><td><input type="text" value="{{ $booking->paymentMode ?? '-' }}" style="width:105px;"></td></tr>
              <tr><td>Destination:</td><td><input type="text" value="{{ $booking->destination ?? ($booking->destinationCountry . '-') }}" style="width:105px;"></td></tr>
              <tr><td>Item Detail:</td><td><input type="text" value="{{ $booking->itemDetail ?? '-' }}" style="width:105px;"></td></tr>
            </table>
          </td>
        </tr>
      </table>
    </div>

    <!-- Consignee Details -->
    <div class="box">
      <div class="box-title">Consignee Details</div>
      <table style="margin-left:3px;">
        <tr><td>Name:</td><td><input type="text" value="{{ $booking->consigneeCompany ?? '-' }}" style="margin-left:-2px; width:424px;"></td></tr>
        <tr><td>Email:</td><td><input type="text" value="{{ $booking->consigneeEmail ?? '-' }}" style="margin-left:-2px; width:424px;"></td></tr>
        <tr>
          <td>Contact No.:</td>
          <td>
            <table style="width:100%; border-collapse:collapse;">
              <tr>
                <td><input type="text" value="{{ $booking->consigneeNumber ?? '-' }}" style="width:200px;"></td>
                <td style="padding-left:10px;white-space:nowrap;">Contact Person:</td>
                <td><input type="text" value="{{ $booking->consigneeName ?? '-' }}" style="width:100px;"></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>Address:</td>
          <td><textarea class="input-address" style="width:420px;">{{ $booking->consigneeAddress ?? '-' }}</textarea></td>
        </tr>
      </table>
    </div>

    <!-- Acknowledgment -->
    <div class="box">
      <div style="margin-bottom:15px; margin-left:2px;">
        <span>I acknowledge receipt of the above items in good condition</span>
      </div>
      <table style="width:100%; border-collapse:collapse; margin-top:20px;">
        <tr>
          <td style="width:65%; vertical-align:top;">
            Receiver Name: ____________________________<br><br>
            Received Date: ____________________________
          </td>
          <td style="width:40%; vertical-align:top;">
            Signature: ________________________________
          </td>
        </tr>
      </table>
    </div>
  </div>

  <!-- Barcode section -->
  <div class="barcode-section">
    <div class="barcode-box" style="height:54.2%;">
      <div class="barcode">
        {!! DNS1D::getBarcodeHTML($booking->bookNo, 'C128', 2, 70) !!}
        <div class="barcode-number">{{ $booking->bookNo }}</div>
      </div>

      <div class="qrcode" style="margin-top:220px; margin-left:10px;">
        @if(!empty($booking->qr_path) && file_exists($booking->qr_path))
          <img src="file://{{ $booking->qr_path }}">
        @else
          <div style="width:100px; height:100px; border:1px dashed red; color:red; font-size:10px; display:flex; align-items:center; justify-content:center;">
            QR not loaded
          </div>
        @endif
      </div>
    </div>
  </div>
</div><!-- main -->
</div><!-- page -->
@endforeach
</body>
</html>
