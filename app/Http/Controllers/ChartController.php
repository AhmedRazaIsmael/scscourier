<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
      public function universalChart(Request $request, $model)
    {
        $label = $request->input('label');
        $value = $request->input('value');
        $function = $request->input('function', 'count');
        $chartType = $request->input('type', 'bar');

        // Map model name to actual class
        $modelClass = match(strtolower($model)) {
            'booking' => Booking::class,
            default => Booking::class,
        };

        $data = $modelClass::select($label, DB::raw("$function($value) as aggregate"))
            ->groupBy($label)
            ->orderBy($label)
            ->get();

        $columnNames = [
            'bookNo' => 'Booking Number',
            'bookDate' => 'Booking Date',
            'customer_name' => 'Customer',
            'product' => 'Product',
            'origin' => 'Origin',
            'destination' => 'Destination',
            'shipperName' => 'Shipper Name',
            'shipperNumber' => 'Shipper Contact',
            'shipperAddress' => 'Shipper Address',
            'consigneeName' => 'Consignee Name',
            'consigneeNumber' => 'Consignee Contact',
        ];

        return view('chart', [
            'labels' => $data->pluck($label),
            'values' => $data->pluck('aggregate'),
            'chartType' => $chartType,
            'labelTitle' => $columnNames[$label] ?? ucfirst($label),
            'valueTitle' => ucfirst($function) . ' of ' . ($columnNames[$value] ?? ucfirst($value)),
            'model' => $model
        ]);
    }
}
