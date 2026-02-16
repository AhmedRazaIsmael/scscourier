<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice - {{ $invoice->invoice_no }}</title>
<style>
    body { margin:0; padding:0; font-family:Arial,Helvetica,sans-serif; background:#fff; }
    .invoice-container { max-width:793px; width:100%; margin:0 auto; border:1px solid #000; padding:15px; box-sizing:border-box; }
    table { width:100%; border-collapse:collapse; table-layout:fixed; word-wrap:break-word; }
    td, th { padding:5px; font-size:12px; border:1px solid #000; }
    th { background:#e6e6e6; font-weight:bold; }
    .section-title { background:#000; color:#fff; font-weight:bold; padding:5px 8px; }
    .label { background:#f9f9f9; }
    .table-responsive { overflow-x:auto; }
    @media (max-width:800px) { .invoice-container { padding:10px; } table, td, th { font-size:11px; } }
</style>
</head>
<body>
<div class="invoice-container" style="margin-left: -10px;">

    <!-- Header -->
    <table style="width:100%;border-collapse:collapse;margin-bottom:0px; border:none">
      <tr>
        <td style="width:50%;vertical-align:top;border:none !important;">
          <h2 style="margin:0;font-size:20px;font-weight:700;color:#000;">
    {{ $invoice->company_name ?? 'Airborn Courier Express' }}
</h2>

<p style="margin:2px 0;font-size:12px;">
        {!! $invoice->company_address ?? 'Office# SB 26/1, Mumtaz Square, Ground Floor,<br>Block K, North Nazimabad, Karachi' !!}
      </p>
<p style="margin:2px 0;font-size:12px;">
    Helpline : {{ $invoice->company_helpline ?? '+92-339-2472676' }}
</p>

<p style="margin:2px 0;font-size:12px;">
    {{ $invoice->company_email ?? 'info@airborncx.com' }}<br>
    {{ $invoice->company_email_2 ?? 'accounts@airborncx.com' }}
</p>

<p style="margin:2px 0;font-size:12px;">
    Website : 
    <a href="{{ $invoice->company_website ?? '#' }}" style="color:#000;text-decoration:none;">
        {{ $invoice->company_website ?? 'www.airborncx.com' }}
    </a>
</p>

        </td>
        <td style="text-align:right;vertical-align:top;border:none !important;">
          <img src="{{ $invoice->company_logo ?? public_path('dashboard-assets/images/logo3.png') }}" alt="Company Logo" style="height:100px;margin-top:-12px;">
        </td>
      </tr>
    </table>

    <!-- Title -->
    <h2 style="text-align:center;font-size:28px;font-weight:800;margin:10px 0;">INVOICE</h2>

    <!-- Bill To & Invoice Detail -->
    <table>
        <tr>
            <td style="width:55%;vertical-align:top;">
                <div class="section-title">BILL TO</div>
                <table>
                    <tr><td class="label">Customer</td><td>{{ $invoice->customer->customer_name ?? '-' }}</td></tr>
                    <tr><td>Contact Person</td><td>{{ $invoice->customer->contact_person_1 ?? '-' }}</td></tr>
                    <tr><td class="label">Contact No.</td><td>{{ $invoice->customer->contact_no_1 ?? $invoice->customer->contact_no_2 ?? '-' }}</td></tr>
                    <tr><td>Address</td><td>{{ $invoice->customer->address_1 ?? $invoice->customer->address_2 ?? '-' }}</td></tr>
                </table>
            </td>
            <td style="width:45%;vertical-align:top;">
                <div class="section-title">INVOICE DETAIL</div>
                <table>
                    <tr><td class="label">Invoice No.</td><td>{{ $invoice->invoice_no }}</td></tr>
                    <tr><td>Invoice Date</td><td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-M-Y') }}</td></tr>
                    <tr><td class="label">Due Date</td><td>{{ \Carbon\Carbon::parse($invoice->pay_due_date)->format('d-M-Y') }}</td></tr>
                    <tr><td>Mode of Payment</td><td>{{ ucfirst($invoice->pay_mode ?? '-') }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Invoice Description -->
<div class="section-title" style="margin-top:15px;text-align:center;border:none !important;">Invoice Description</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Sr No</th>
                <th>AWB/Book No</th>
                <th>Origin Country</th>
                <th>Destination Country</th>
                <th>Cartons</th>
                <th>Weight (KG)</th>
                <th>Currency</th>
                <th>Currency Rate</th>
                <th>Amount (PKR)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $gross_total = 0;

                // Initialize all expected charge heads with 0
                $other_charges = [
                    'Customs' => 0,
                    'Fuel' => 0,
                    'Sales Commission' => 0,
                    'Export Declaration' => 0,
                    'Other Cost' => 0,
                    'DO Charges' => 0,
                    'Clearance Charges' => 0,
                    'Transportation' => 0,
                ];

                $lastBookNo = null;
            @endphp

            @foreach($invoice->items as $index => $item)
                @php
                    if($item->account_head === 'Freight'){
                        $gross_total += $item->amount;
                        $displayBookNo = ($lastBookNo !== $item->book_no) ? $item->book_no : '';
                        $lastBookNo = $item->book_no;
                        $origin = $item->booking->origin ?? '-';
                        $destination = $item->booking->destination ?? '-';
                        $cartons = $item->booking->pieces ?? 0; // <-- Use pieces as Cartons
                    } else {
                        // Add amount to the corresponding charge head
                        $other_charges[$item->account_head] = ($other_charges[$item->account_head] ?? 0) + $item->amount;
                    }
                @endphp

                @if($item->account_head === 'Freight')
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $displayBookNo }}</td>
                        <td>{{ $displayBookNo ? $origin : '' }}</td>
                        <td>{{ $displayBookNo ? $destination : '' }}</td>
                         <td>{{ $cartons }}</td> <!-- Updated -->
                        <td>{{ $item->gross_weight ?? 0 }}</td>
                        <td>{{ $item->currency ?? '-' }}</td>
                        <td>{{ $item->currency_rate ?? 0 }}</td>
                        <td style="text-align:right;">{{ number_format($item->amount ?? 0, 2) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>

<!-- Charges / Totals -->
@php
    $net_total = $gross_total + array_sum($other_charges);

    function numberToWords($number) {
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $hundreds = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                     'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $words = '';

        if($no == 0){ $words = 'Zero'; }
        else if($no < 20) { $words = $hundreds[$no]; }
        else if($no < 100) { $words = $tens[floor($no / 10)] . ($no % 10 != 0 ? ' ' . $hundreds[$no % 10] : ''); }
        else if($no < 1000) { $words = $hundreds[floor($no / 100)] . ' Hundred' . ($no % 100 != 0 ? ' and ' . numberToWords($no % 100) : ''); }
        else { $words = numberToWords(floor($no / 1000)) . ' Thousand' . ($no % 1000 != 0 ? ' ' . numberToWords($no % 1000) : ''); }

        if ($point > 0) { $words .= " and " . numberToWords($point) . " Paise"; }
        return $words;
    }
@endphp

<table style="width:100%;margin-top:10px; border:none !important;">
    <tr>
        <td style="width:60%;">In Words : {{ numberToWords($net_total) }} Only</td>
        <td style="width:40%;">
            <table style="width:100%;">
                <tr><td>Gross Total (PKR)</td><td style="text-align:right;">{{ number_format($gross_total,2) }}</td></tr>

                <!-- Show all charges, even if 0 -->
                @foreach($other_charges as $head => $sum)
    @if(!in_array($head, ['Customs', 'Fuel', 'Sales Commission']) || $sum > 0)
        <tr>
            <td>{{ $head }}</td>
            <td style="text-align:right;">{{ number_format($sum,2) }}</td>
        </tr>
    @endif
@endforeach

                <tr style="font-weight:bold;"><td>Net Total (PKR)</td><td style="text-align:right;">{{ number_format($net_total,2) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<p style="font-size:12px;margin-top:10px;">Remarks: {{ $invoice->remarks ?? '-' }}</p>

<!-- Company Account Details and Note -->
<table style="width:100%;border-collapse:collapse;margin-top:10px;">
  <tr>
    <td style="width:60%;border:1px solid #000;vertical-align:top;">
      <div style="background:#000;color:#fff;font-size:13px;font-weight:bold;padding:5px 8px;">Company Account Details for Payments</div>
      <table style="width:100%;border-collapse:collapse;">
        <tr><td style="border:1px solid #000;padding:5px;font-size:12px;background:#f9f9f9;">Account Title</td><td style="border:1px solid #000;padding:5px;font-size:12px;">AIRBORN COURIER EXPRESS</td></tr>
        <tr><td style="border:1px solid #000;padding:5px;font-size:12px;background:#f9f9f9;">Bank Name</td><td style="border:1px solid #000;padding:5px;font-size:12px;">BARADARI NORTH KARACHI</td></tr>
        <tr><td style="border:1px solid #000;padding:5px;font-size:12px;background:#f9f9f9;">Account No</td><td style="border:1px solid #000;padding:5px;font-size:12px;">99390112884808</td></tr>
        <tr><td style="border:1px solid #000;padding:5px;font-size:12px;background:#f9f9f9;">IBAN</td><td style="border:1px solid #000;padding:5px;font-size:12px;">PK25MEZN0099390112884808</td></tr>
        <tr><td style="border:1px solid #000;padding:5px;font-size:12px;background:#f9f9f9;">NTN Number</td><td style="border:1px solid #000;padding:5px;font-size:12px;">3449480-4</td></tr>
      </table>
    </td>
    <td style="width:40%;border:1px solid #000;vertical-align:top;">
      <div style="background:#000;color:#fff;font-size:13px;font-weight:bold;padding:5px 8px;">NOTE</div>
      <div style="padding:6px;font-size:12px;line-height:1.5;">
        If any discrepancies or correction required please email within three days at <strong>{{ $invoice->company_email ?? 'info@airborncx.com' }}</strong>.<br><br>
        Payment must be paid as per the due date.<br><br>
        For self-deposit of payments through bank or online, slips must be shared timely.
      </div>
    </td>
  </tr>
</table>

<!-- Footer -->
<p style="text-align:center;font-size:11px;margin-top:20px;border-top:1px solid #000;padding-top:5px;">
    This is a system generated invoice and does not require any signatures or stamp
</p>

</div>
</body>
</html>
