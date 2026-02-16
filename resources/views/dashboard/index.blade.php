@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="app-body">
    <div class="row gx-4">

        @php
            $cards = [
                [
                    'title' => 'Total Orders',
                    'count' => $totalOrders,
                    'desc'  => $percentChange . '% higher than last week.',
                    'color' => 'bg-danger', // Red card
                    'width' => $percentChange,
                ],
                [
                    'title' => 'Inprocess Orders',
                    'count' => $pendingOrders,
                    'desc'  => 'Orders waiting for delivery',
                    'color' => 'bg-primary', // Blue card
                    'width' => $totalOrders ? ($pendingOrders / $totalOrders) * 100 : 0,
                ],
                [
                    'title' => 'Delivered Orders',
                    'count' => $deliveredOrders,
                    'desc'  => 'Successfully delivered',
                    'color' => 'bg-success', // Green card
                    'width' => $totalOrders ? ($deliveredOrders / $totalOrders) * 100 : 0,
                ],[
                    'title' => 'Return Orders',
                    'count' => $deliveredOrders,
                    'desc'  => 'Return',
                    'color' => 'bg-info', // Green card
                    'width' => $totalOrders ? ($deliveredOrders / $totalOrders) * 100 : 0,
                ],
                
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
