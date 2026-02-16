@extends('layouts.master')

@section('title', ucfirst($model) . ' Chart')

@section('content')
<div class="page-content mt-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5>{{ ucfirst($model) }} Chart</h5>
            <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">← Back</a>
        </div>
        <div class="card-body">
            <canvas id="chartCanvas" height="100"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = @json($labels ?? []);
    const values = @json($values ?? []);
    const chartType = '{{ $chartType ?? 'bar' }}';
    
    if(labels.length === 0 || values.length === 0){
        document.getElementById('chartCanvas').insertAdjacentHTML('beforebegin','<p class="text-center mt-2">No data to display</p>');
    } else {
        const ctx = document.getElementById('chartCanvas').getContext('2d');
        new Chart(ctx, {
            type: chartType,
            data: {
                labels: labels,
                datasets: [{
                    label: '{{ $valueTitle ?? "Value" }}',
                    data: values,
                    backgroundColor: chartType === 'pie' ? [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ] : 'rgba(54, 162, 235, 0.6)',
                    borderColor: chartType === 'pie' ? [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ] : 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: '{{ $labelTitle ?? "Label" }} vs {{ $valueTitle ?? "Value" }}' },
                    legend: { display: chartType === 'pie' }
                },
                scales: chartType === 'pie' ? {} : {
                    y: { beginAtZero: true, title: { display: true, text: '{{ $valueTitle ?? "Value" }}' } },
                    x: { title: { display: true, text: '{{ $labelTitle ?? "Label" }}' } }
                }
            }
        });
    }
</script>
@endpush
