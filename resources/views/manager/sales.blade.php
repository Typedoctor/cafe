@extends('manager.layout')

@section('title', 'Sales Log')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/sales-log.css') }}">
@endpush

@section('content')
<div class="sl-header">Sales Log</div>
<form id="filterForm" action="{{ route('sales.index') }}" method="GET">
    <div class="sl-all-tabs">
        <div class="sl-tab sl-transactions {{ $tab === 'transactions' ? 'active' : '' }}" onclick="showTab('transactions')">ALL TRANSACTIONS</div>
        <div class="sl-tab sl-summary {{ $tab === 'summary' ? 'active' : '' }}" onclick="showTab('summary')">SALES SUMMARY</div>
    </div>
    <div class="sl-filter-box">
        <select class="sl-month-filter" name="month" onchange="submitForm()">
            @for ($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
            @endfor
        </select>
        <select class="sl-year-filter" name="year" onchange="submitForm()">
            @for ($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>
    <input type="hidden" name="tab" id="tabInput" value="{{ $tab }}">
</form>

<div id="transactions-content" style="display: {{ $tab === 'transactions' ? 'block' : 'none' }};">
    <div class="sl-metrics">
        <div class="sl-metric-box">
            <div class="sl-metric-title">Total Sales</div>
            <div class="sl-metric-value sl-revenue">₱{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="sl-metric-box">
            <div class="sl-metric-title">Total Quantity Sold</div>
            <div class="sl-metric-value">{{ $totalQuantity }}</div>
        </div>
    </div>
    <div class="sl-table-container" id="salesLogTableContainer">
        <div class="sl-section-title">All Sales Transactions</div>
        <table class="sl-table sl-table-striped" id="salesLogTable">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                    <th>Customer Name</th>
                    <th>Order Type</th>
                    <th>Status</th>
                    <th>Special Instructions</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($saleLogs as $log)
                    <tr>
                        <td>{{ $log->order_id ?? 'N/A' }}</td>
                        <td>{{ $log->product_name ?? 'N/A' }}</td>
                        <td>{{ $log->quantity ?? 0 }}</td>
                        <td>₱{{ number_format($log->unit_price ?? 0, 2) }}</td>
                        <td>₱{{ number_format($log->total_price ?? 0, 2) }}</td>
                        <td>{{ $log->customer_name ?? 'Unknown' }}</td>
                        <td>{{ $log->order_type ?? 'N/A' }}</td>
                        <td>{{ $log->status ?? 'N/A' }}</td>
                        <td>{{ $log->special_instructions ?? 'N/A' }}</td>
                        <td>{{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('F j Y, g:i A') : 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">No sales logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="summary-content" style="display: {{ $tab === 'summary' ? 'block' : 'none' }};">
    <div class="sl-metrics">
        <div class="sl-metric-box">
            <div class="sl-metric-title">Total Revenue</div>
            <div class="sl-metric-value sl-revenue">₱{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="sl-metric-box">
            <div class="sl-metric-title">Total Quantity Sold</div>
            <div class="sl-metric-value">{{ $totalQuantity }}</div>
        </div>
    </div>
    <div class="sl-table-container" id="salesSummaryTableContainer">
        <div class="sl-section-title">Sales Summary by Product</div>
        <table class="sl-table sl-table-striped" id="salesSummaryTable">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Total Quantity Sold</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($salesSummary as $summary)
                    <tr>
                        <td>{{ $summary->product_name ?? 'N/A' }}</td>
                        <td>{{ $summary->total_quantity_sold ?? 0 }}</td>
                        <td>₱{{ number_format($summary->total_revenue ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No sales recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    const salesLogTable = $('#salesLogTable');
    const hasSalesLogRows = salesLogTable.find('tbody tr').length > 0 && !salesLogTable.find('tbody tr td[colspan]').length;
    if (hasSalesLogRows) {
        salesLogTable.DataTable({
            pageLength: 10,
            responsive: true,
            order: [[9, 'desc']],
            columnDefs: [{ orderable: true, targets: '_all' }]
        });
    }

    const salesSummaryTable = $('#salesSummaryTable');
    const hasSalesSummaryRows = salesSummaryTable.find('tbody tr').length > 0 && !salesSummaryTable.find('tbody tr td[colspan]').length;
    if (hasSalesSummaryRows) {
        salesSummaryTable.DataTable({
            pageLength: 10,
            responsive: true,

            order: [[1, 'desc']],
            columnDefs: [{ orderable: true, targets: '_all' }]
        });
    }
});

function showTab(tabName) {
    document.querySelectorAll('.sl-all-tabs .sl-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.classList.add('active');
    document.getElementById('tabInput').value = tabName;
    document.getElementById('transactions-content').style.display = tabName === 'transactions' ? 'block' : 'none';
    document.getElementById('summary-content').style.display = tabName === 'summary' ? 'block' : 'none';
}

function submitForm() {
    document.getElementById('filterForm').submit();
}
</script>
@endpush