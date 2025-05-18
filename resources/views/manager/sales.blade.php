@extends('manager.layout')

@section('title', 'Sales Log')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/sales-log.css') }}">
@endpush

@section('content')
<form id="filterForm" action="{{ route('sales.index') }}" method="GET">
    <div class="sl-all-tabs">
        <div class="sl-tab sl-transactions {{ $tab === 'transactions' ? 'active' : '' }}" onclick="showTab('transactions')">ALL TRANSACTIONS</div>
        <div class="sl-tab sl-summary {{ $tab === 'summary' ? 'active' : '' }}" onclick="showTab('summary')">SALES SUMMARY</div>
    </div>
    @php
        $selectedMonth = request()->has('month') ? request('month') : now()->month;
        $selectedYear = request()->has('year') ? request('year') : now()->year;
    @endphp
    <div class="sl-filter-box">
        <select class="sl-month-filter" name="month" onchange="submitForm()">
            <option value="" {{ $selectedMonth === '' ? 'selected' : '' }}>All Months</option>
            @for ($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ (string)$selectedMonth === (string)$m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
            @endfor
        </select>
        <select class="sl-year-filter" name="year" onchange="submitForm()">
            <option value="" {{ $selectedYear === '' ? 'selected' : '' }}>All Years</option>
            @for ($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}" {{ (string)$selectedYear === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="button" class="sl-reset-btn" onclick="resetFilters()">Reset</button>
    </div>
    <input type="hidden" name="tab" id="tabInput" value="{{ $tab }}">
</form>

<!-- Sale Details Modal -->
<div class="sl-modal-overlay" id="saleDetailsModal">
    <div class="sl-sale-modal">
        <div class="sl-sale-header">Sale Details</div>
        <div class="sl-sale-details" id="saleDetails"></div>
        <div class="sl-modal-buttons">
            <button class="sl-modal-close" id="closeSaleModal">Close</button>
        </div>
    </div>
</div>

<div id="transactions-content" style="display: {{ $tab === 'transactions' ? 'block' : 'none' }};">
    <div class="sl-metrics">
        <div class="sl-metric-box">
            <div class="sl-metric-title">Total sales</div>
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
                    <tr class="sl-sale-row" data-sale="{{ json_encode([
                        'order_id' => $log->order_id ?? 'N/A',
                        'product_name' => $log->product_name ?? 'N/A',
                        'quantity' => $log->quantity ?? 0,
                        'unit_price' => number_format($log->unit_price ?? 0, 2),
                        'total_price' => number_format($log->total_price ?? 0, 2),
                        'customer_name' => $log->customer_name ?? 'Unknown',
                        'order_type' => $log->order_type ?? 'N/A',
                        'status' => $log->status ?? 'N/A',
                        'special_instructions' => $log->special_instructions ?? 'N/A',
                        'created_at' => $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('M j Y / g:i A'): 'N/A'
                    ]) }}">
                        <td>{{ $log->order_id ?? 'N/A' }}</td>
                        <td>{{ $log->product_name ?? 'N/A' }}</td>
                        <td>{{ $log->quantity ?? 0 }}</td>
                        <td>₱{{ number_format($log->unit_price ?? 0, 2) }}</td>
                        <td>₱{{ number_format($log->total_price ?? 0, 2) }}</td>
                        <td>{{ $log->customer_name ?? 'Unknown' }}</td>
                        <td>{{ $log->order_type ?? 'N/A' }}</td>
                        <td>{{ $log->status ?? 'N/A' }}</td>
                        <td>{{ $log->special_instructions ?? 'N/A' }}</td>
                        <td>{{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format("M j Y / g:i A") : 'N/A' }}</td>
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

    // Handle row click to show sale details modal
    $(document).on('click', '.sl-sale-row', function(e) {
        const sale = $(this).data('sale');
        const detailsHtml = `
            <p><strong>Order ID:</strong> <span>${sale.order_id}</span></p>
            <p><strong>Product Name:</strong> <span>${sale.product_name}</span></p>
            <p><strong>Quantity:</strong> <span>${sale.quantity}</span></p>
            <p><strong>Unit Price:</strong> <span>₱${sale.unit_price}</span></p>
            <p><strong>Total Price:</strong> <span>₱${sale.total_price}</span></p>
            <p><strong>Customer Name:</strong> <span class="sl-customer-text">${sale.customer_name}</span></p>
            <p><strong>Order Type:</strong> <span>${sale.order_type}</span></p>
            <p><strong>Status:</strong> <span>${sale.status}</span></p>
            <p><strong>Special Instructions:</strong></p>
            <div class="sl-instructions-text">${sale.special_instructions}</div>
            <p><strong>Date:</strong> <span>${sale.created_at}</span></p>
        `;
        $('#saleDetails').html(detailsHtml);
        $('#saleDetailsModal').css('display', 'flex').addClass('active');
        $('body').addClass('no-scroll'); // Disable page scrolling
    });

    // Close sale details modal
    $('#closeSaleModal').on('click', function() {
        $('#saleDetailsModal').removeClass('active').delay(300).queue(function(next) {
            $(this).css('display', 'none');
            $('body').removeClass('no-scroll'); // Restore page scrolling
            next();
        });
    });

    // Close sale details modal when clicking outside
    $(document).on('click', '.sl-modal-overlay', function(e) {
        if (e.target === this) {
            $('#saleDetailsModal').removeClass('active').delay(300).queue(function(next) {
                $(this).css('display', 'none');
                $('body').removeClass('no-scroll'); // Restore page scrolling
                next();
            });
        }
    });
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

function resetFilters() {
    const now = new Date();
    const month = now.getMonth() + 1; // JS months are 0-based
    const year = now.getFullYear();
    document.querySelector('.sl-month-filter').value = month;
    document.querySelector('.sl-year-filter').value = year;
    document.getElementById('filterForm').submit();
}
</script>
@endpush
@endsection
