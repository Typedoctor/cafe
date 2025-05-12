@extends('manager.layout')

@section('title', 'Manager Reports')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/manager-reports.css') }}">
    <link rel="stylesheet" href="{{ asset('css/transaction.css') }}">
@endpush

@section('content')
<div class="rep-header">Reports</div>

<form id="timePeriodForm" action="{{ route('reports.index') }}" method="GET">
    <div class="rep-all-tabs">
        <div class="rep-tab rep-profit {{ $tab === 'profit' ? 'active' : '' }}" onclick="showTab('profit')">PROFIT</div>
        <div class="rep-tab rep-loss {{ $tab === 'loss' ? 'active' : '' }}" onclick="showTab('loss')">LOSS</div>
        <div class="rep-gap"></div>
        <div class="rep-time-tab {{ $period === 'daily' ? 'active' : '' }}" onclick="changeTimePeriod('daily')">Daily</div>
        <div class="rep-time-tab {{ $period === 'monthly' ? 'active' : '' }}" onclick="changeTimePeriod('monthly')">Monthly</div>
        <div class="rep-time-tab {{ $period === 'yearly' ? 'active' : '' }}" onclick="changeTimePeriod('yearly')">Yearly</div>
    </div>
    <div class="rep-filter-box">
        <select class="rep-month-filter" name="month" onchange="submitForm()">
            @for ($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                </option>
            @endfor
        </select>
        <select class="rep-year-filter" name="year" onchange="submitForm()">
            @for ($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>
    <input type="hidden" name="period" id="periodInput" value="{{ $period }}">
    <input type="hidden" name="tab" id="tabInput" value="{{ $tab }}">
    <input type="hidden" name="subtab" id="subtabInput" value="{{ $subtab }}">
</form>

<div id="profit-content" style="display: {{ $tab === 'profit' ? 'block' : 'none' }};">
    <div class="rep-metrics">
        <div class="rep-metric-box">
            <div class="rep-metric-title">Revenue</div>
            <div class="rep-metric-value rep-profit">₱{{ number_format($revenue, 2) }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Loss from thrown and damaged items</div>
            <div class="rep-metric-value rep-loss">₱{{ number_format($totalLoss, 2) }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Profit</div>
            <div class="rep-metric-value rep-profit">₱{{ number_format($revenue - $totalLoss, 2) }}</div>
        </div>
    </div>
    <div class="inv-table-container" id="transaction-table">
        <div class="rep-section-title">Transaction List</div>
        <table class="trn-inventory-table trn-table-striped" id="transactionsTable">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Customer Name</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summarizedTransactions as $transaction)
                    <tr class="trn-transaction-row" data-transaction='{{ json_encode([
                        "transaction_id" => $transaction->transaction_id ?? "N/A",
                        "customer_name" => $transaction->customer_name ?? "Unknown",
                        "product_names" => $transaction->product_names ?? "",
                        "special_instructions" => $transaction->special_instructions ?? null,
                        "order_type" => $transaction->order_type ?? null,
                        "status" => $transaction->status ?? null,
                        "total_quantity" => $transaction->total_quantity ?? 0,
                        "money_received" => $transaction->money_received ?? 0,
                        "change" => $transaction->change ?? 0,
                        "total_price" => $transaction->total_price ?? 0,
                        "created_at" => \Carbon\Carbon::parse($transaction->created_at)->format("F j Y / g:i A")
                    ]) }}'>
                        <td>{{ $transaction->transaction_id ?? "N/A" }}</td>
                        <td>{{ $transaction->customer_name ?? "Unknown" }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No transactions found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="trn-modal-overlay" id="receiptModal">
    <div class="trn-receipt-modal">
        <div class="trn-receipt-header">Transaction Receipt</div>
        <div class="trn-receipt-details" id="receiptDetails"></div>
        <div class="trn-modal-buttons">
            <button class="trn-modal-print" id="printModal">Print</button>
            <button class="trn-modal-close" id="closeModal">Close</button>
        </div>
    </div>
</div>

<div id="loss-content" style="display: {{ $tab === 'loss' ? 'block' : 'none' }};">
    <div class="rep-metrics">
        <div class="rep-metric-box">
            <div class="rep-metric-title">Total Loss</div>
            <div class="rep-metric-value rep-loss">₱{{ number_format($totalLoss, 2) }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Thrown Items</div>
            <div class="rep-metric-value">{{ $trashCount }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Damaged Items</div>
            <div class="rep-metric-value">{{ $damagedCount }}</div>
        </div>
    </div>
    <div class="loss-sub-tabs">
        <div class="loss-sub-tab {{ $subtab === 'thrown' ? 'active' : '' }}" data-subtab="thrown" onclick="showLossSubTab('thrown')">Thrown Items</div>
        <div class="loss-sub-tab {{ $subtab === 'damaged' ? 'active' : '' }}" data-subtab="damaged" onclick="showLossSubTab('damaged')">Damaged Items</div>
    </div>
    <div id="thrown-content" style="display: {{ $subtab === 'thrown' ? 'block' : 'none' }};">
        <div class="inv-table-container" id="trash-table">
            <div class="rep-section-title">List of Thrown Away Items</div>
            <table class="rep-table" id="trashTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Reason</th>
                         <th>Total Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trashes as $trash)
                    <tr>
                        <td>{{ $trash->id }}</td>
                        <td>{{ $trash->product_name }}</td>
                        <td>{{ $trash->category }}</td>
                        <td>{{ $trash->quantity }}</td>
                        <td>{{ $trash->reason }}</td>
                        <td>₱{{ number_format($trash->total_loss, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td>No thrown items found for this period.</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div id="damaged-content" style="display: {{ $subtab === 'damaged' ? 'block' : 'none' }};">
        <div class="inv-table-container" id="damaged-table">
            <div class="rep-section-title">List of Marked as Loss Items</div>
            <table class="rep-table" id="damagedProductsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price per Item</th>
                        <th>Total Cost</th>
                        <th>Reason</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Reported At</th>
                        <th>Return Date</th>
                        <th>Return Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($damagedProducts as $damagedProduct)
                    <tr>
                        <td>{{ $damagedProduct->id }}</td>
                        <td>{{ $damagedProduct->product_name }}</td>
                        <td>{{ $damagedProduct->quantity }}</td>
                        <td>₱{{ number_format($damagedProduct->price_per_item, 2) }}</td>
                        <td>₱{{ number_format($damagedProduct->total_cost, 2) }}</td>
                        <td>{{ $damagedProduct->reason }}</td>
                        <td>{{ $damagedProduct->supplier }}</td>
                        <td>{{ $damagedProduct->status }}</td>
                        <td>{{ $damagedProduct->reported_at->format('F j Y/ g:i A') }}</td>
                        <td>{{ $damagedProduct->return_date ? $damagedProduct->return_date->format('F j Y/ g:i A') : '-' }}</td>
                        <td>{{ $damagedProduct->return_notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td>No damaged items found for this period.</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    const transactionsTable = $('#transactionsTable');
    const hasTransactionRows = transactionsTable.find('tbody tr').length > 0 && !transactionsTable.find('tbody tr td[colspan]').length;
    
    if (hasTransactionRows) {
        transactionsTable.DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: true, targets: '_all' }
            ]
        });
    } else {
        transactionsTable.addClass('no-datatables');
    }

    const trashTable = $('#trashTable');
    const hasTrashRows = trashTable.find('tbody tr').length > 0 && !trashTable.find('tbody tr td[colspan]').length;
    
    if (hasTrashRows) {
        trashTable.DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: true, targets: '_all' }
            ]
        });
    } else {
        trashTable.addClass('no-datatables');
    }

    const damagedTable = $('#damagedProductsTable');
    const hasDamagedRows = damagedTable.find('tbody tr').length > 0 && !damagedTable.find('tbody tr td[colspan]').length;
    
    if (hasDamagedRows) {
        damagedTable.DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: true, targets: '_all' }
            ]
        });
    } else {
        damagedTable.addClass('no-datatables');
    }

    $(document).on('click', '.trn-transaction-row', function() {
        const transaction = $(this).data('transaction');
        const products = transaction.product_names ? transaction.product_names.split(',').map(product => product.trim()) : [];
        let productsHtml = '<ul class="trn-product-list">';
        productsHtml += products.length > 0 ? products.map(product => `<li>${product}</li>`).join('') : '<li>No products listed</li>';
        productsHtml += '</ul>';

        const detailsHtml = `
            <p><strong>Transaction ID:</strong> <span>${transaction.transaction_id}</span></p>
            <p><strong>Customer Name:</strong> <span>${transaction.customer_name || 'N/A'}</span></p>
            <p><strong>Completed Order at:</strong> <span>${transaction.created_at || 'N/A'}</span></p>
            <p><strong>Products Ordered:</strong> ${productsHtml}</p>
            <p><strong>Special Instructions:</strong></p>
            <div class="trn-special-instructions">${transaction.special_instructions || 'N/A'}</div>
            <p><strong>Order Type:</strong> <span>${transaction.order_type || 'N/A'}</span></p>
            <p><strong>Status:</strong> <span>${transaction.status || 'N/A'}</span></p>
            <p><strong>Money Received:</strong> <span>₱${parseFloat(transaction.money_received || 0).toFixed(2)}</span></p>
            <p><strong>Change:</strong> <span>₱${parseFloat(transaction.change || 0).toFixed(2)}</span></p>
            <p><strong>Total Quantity of Orders:</strong> <span>${transaction.total_quantity || 0}</span></p>
            <p class="trn-total"><strong>Total Price:</strong> <span>₱${parseFloat(transaction.total_price || 0).toFixed(2)}</span></p>
        `;
        $('#receiptDetails').html(detailsHtml);

        $('#receiptModal').css('display', 'flex').addClass('active');
    });

    $('#printModal').on('click', function() {
        window.print();
    });

    $('#closeModal').on('click', function() {
        $('#receiptModal').removeClass('active').delay(300).queue(function(next) {
            $(this).css('display', 'none');
            next();
        });
    });

    $(document).on('click', '.trn-modal-overlay', function(e) {
        if (e.target === this) {
            $('#receiptModal').removeClass('active').delay(300).queue(function(next) {
                $(this).css('display', 'none');
                next();
            });
        }
    });
});

function showTab(tabName) {
    document.querySelectorAll('.rep-all-tabs .rep-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.classList.add('active');
    document.getElementById('tabInput').value = tabName;
    if (tabName === 'profit') {
        document.getElementById('profit-content').style.display = 'block';
        document.getElementById('loss-content').style.display = 'none';
    } else {
        document.getElementById('profit-content').style.display = 'none';
        document.getElementById('loss-content').style.display = 'block';
    }
}

function showLossSubTab(subTabName) {
    document.querySelectorAll('.loss-sub-tabs .loss-sub-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.classList.add('active');
    document.getElementById('subtabInput').value = subTabName;
    document.getElementById('thrown-content').style.display = subTabName === 'thrown' ? 'block' : 'none';
    document.getElementById('damaged-content').style.display = subTabName === 'damaged' ? 'block' : 'none';
}

function changeTimePeriod(period) {
    document.querySelectorAll('.rep-all-tabs .rep-time-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.classList.add('active');
    document.getElementById('periodInput').value = period;
    document.getElementById('timePeriodForm').submit();
}

function submitForm() {
    document.getElementById('timePeriodForm').submit();
}
</script>
@endpush