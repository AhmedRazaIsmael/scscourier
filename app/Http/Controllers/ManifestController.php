<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManifestController extends Controller
{
    public function manifestPL()
    {
        // Dummy data for now — you can replace this with a DB query later
        $manifestData = [
            [
                'awb' => '176-7875 9870',
                'bag' => 'Bag 1',
                'airline' => 'Emirates',
                'date' => '11-JAN-25',
                'flight_no' => 'EK',
                'pcs' => 5,
                'airline_wtt' => 80,
                'pl_wtt' => 113.5,
                'diff' => 33.5,
                'count' => 19,
                'flight_cost' => 90596,
                'pl_cost' => 260696,
                'total_cost' => 351292,
                'revenue' => 392264,
                'profit_loss' => 40972,
                'profit_percent' => 10.45
            ],
            // ... add more rows as needed
        ];

        return view('manifest-pl', compact('manifestData'));
    }
}
