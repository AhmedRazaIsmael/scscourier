@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="app-body">
    <div class="row gx-4">

        @php
            $cards = [
                [
                    'title' => 'TOTAL ORDERS',
                    'count' => $totalOrders,
                    'desc'  => $percentChange . '% higher than last week.',
                    'color' => 'bg-primary', // Red card
                    'width' => $percentChange,
                ],
                [
                    'title' => 'OUT FOR DELIVERY',
                    'count' => $pendingOrders,
                    'desc'  => 'Orders waiting for delivery',
                    'color' => 'bg-primary', // Blue card
                    'width' => $totalOrders ? ($pendingOrders / $totalOrders) * 100 : 0,
                ],
                [
                    'title' => 'IN TRANSIT',
                    'count' => $deliveredOrders,
                    'desc'  => 'Successfully delivered',
                    'color' => 'bg-info', // Green card
                    'width' => $totalOrders ? ($deliveredOrders / $totalOrders) * 100 : 0,
                ],[
                    'title' => 'AT DESTINATION WEARHOUSE',
                    'count' => $deliveredOrders,
                    'desc'  => 'Return',
                    'color' => 'bg-info', // Green card
                    'width' => $totalOrders ? ($deliveredOrders / $totalOrders) * 100 : 0,
                ],   [
                    'title' => 'RECIVED OFFICE',
                    'count' => $totalOrders,
                    'desc'  => $percentChange . '% higher than last week.',
                    'color' => 'bg-primary', // Red card
                    'width' => $percentChange,
                ],
                [
                    'title' => 'DELIVERED',
                    'count' => $pendingOrders,
                    'desc'  => 'Orders waiting for delivery',
                    'color' => 'bg-primary', // Blue card
                    'width' => $totalOrders ? ($pendingOrders / $totalOrders) * 100 : 0,
                ],
                [
                    'title' => 'RETURN CONFIRM',
                    'count' => $deliveredOrders,
                    'desc'  => 'Successfully delivered',
                    'color' => 'bg-info', // Green card
                    'width' => $totalOrders ? ($deliveredOrders / $totalOrders) * 100 : 0,
                ],[
                    'title' => 'RETURNED TO SHIPPER',
                    'count' => $deliveredOrders,
                    'desc'  => 'Return',
                    'color' => 'bg-info', // Green card
                    'width' => $totalOrders ? ($deliveredOrders / $totalOrders) * 100 : 0,
                ],[
                    'title' => 'SETTLED',
                    'count' => $deliveredOrders,
                    'desc'  => 'Return',
                    'color' => 'bg-primary', // Green card
                    'width' => $totalOrders ? ($deliveredOrders / $totalOrders) * 100 : 0,
                ]
                
            ];
        @endphp

        @foreach($cards as $index => $card)
            <div class="col-xxl-3 col-sm-6 col-12">
                <div class="card mb-4 border-0 {{ $card['color'] }}">
                    <div class="card-body p-2 text-white">
                        <div class="transparent-card p-3 rounded-2 border-0">
                            <h6 class="card-badge d-inline-flex">{{ $card['title'] }}</h6>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="display-6 m-0 fw-semibold">{{ $card['count'] }}</h2>
                                    <p class="mb-2">{{ $card['desc'] }}</p>
                                    <div class="progress small bg-white bg-opacity-25">
                                        <div class="progress-bar bg-white" style="width: {{ $card['width'] }}%"></div>
                                    </div>
                                </div>
                                <div class="ms-3 graph-mini" id="option{{ $index + 1 }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>
@endsection
