@forelse($scans as $scan)
<tr class="data-row">
    <td class="data-cell">{{ $scan->book_no }}</td>
    <td class="data-cell">{{ $scan->created_at->format('d-M-Y H:i:s') }}</td>
    <td class="data-cell">{{ $scan->hub->name ?? $scan->booking->destination ?? '-' }}</td>
    <td class="data-cell">{{ $scan->user ? strtoupper($scan->user->name) : '-' }}</td>
    <td class="data-cell">{{ $scan->user ? $scan->user->name : '-' }}</td>
    <td class="data-cell">{{ $scan->latestBookingStatus->status ?? '-' }}</td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center text-muted py-3">No scans found</td>
</tr>
@endforelse