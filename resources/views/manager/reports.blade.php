@extends('manager.layout')

@section('title', 'Manager Reports')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/manager-reports.css') }}">
    <link rel="stylesheet" href="{{ asset('css/transaction.css') }}">
@endpush

@section('content')

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
            <option value="all" {{ request('month', now()->month) === 'all' ? 'selected' : '' }}>All Months</option>
            @for ($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                </option>
            @endfor
        </select>
        <select class="rep-year-filter" name="year" onchange="submitForm()">
            <option value="all" {{ request('year', now()->year) === 'all' ? 'selected' : '' }}>All Years</option>
            @for ($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>
    <input type="hidden" name="period" id="periodInput" value="{{ $period }}">
    <input type="hidden" name="tab" id="tabInput" value="{{ $tab }}">
    <input type="hidden" name="subtab" id="subtabInput" value="{{ $subtab ?? ($tab === 'profit' ? 'all-transactions' : 'thrown') }}">
</form>

<div id="profit-content" style="display: {{ $tab === 'profit' ? 'block' : 'none' }};">
    <div class="rep-metrics">
        <div class="rep-metric-box">
            <div class="rep-metric-title">Revenue</div>
            <div class="rep-metric-value rep-profit">₱{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Total Quantity Sold</div>
            <div class="rep-metric-value">{{ $totalQuantity }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Loss from spoiled and damaged items</div>
            <div class="rep-metric-value rep-loss">₱{{ number_format($totalLoss, 2) }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Profit</div>
            <div class="rep-metric-value rep-profit">₱{{ number_format($totalRevenue - $totalLoss, 2) }}</div>
        </div>
    </div>
    <div id="all-transactions-sub-content" style="display: {{ $subtab === 'all-transactions' ? 'block' : 'none' }};">
        <div class="inv-table-container" id="transaction-table">
            <div class="profit-sub-tabs">
                <div class="profit-sub-tab {{ $subtab === 'all-transactions' ? 'active' : '' }}" data-subtab="all-transactions" onclick="showProfitSubTab('all-transactions')">Transactions</div>
                <div class="profit-sub-tab {{ $subtab === 'sales-log' ? 'active' : '' }}" data-subtab="sales-log" onclick="showProfitSubTab('sales-log')">All Sales Log</div>
                <div class="profit-sub-tab {{ $subtab === 'summary' ? 'active' : '' }}" data-subtab="summary" onclick="showProfitSubTab('summary')">Sales Summary by Products</div>
            </div>
            <div class="rep-section-title">Transaction Details</div>
            <table class="rep-table rep-table-striped" id="transactionsTable">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Customer Name</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summarizedTransactions as $transaction)
                        <tr class="rep-transaction-row" data-transaction='{{ json_encode([
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
    <div id="sales-log-sub-content" style="display: {{ $subtab === 'sales-log' ? 'block' : 'none' }};">
        <div class="inv-table-container" id="salesLogTableContainer">
            <div class="profit-sub-tabs">
                <div class="profit-sub-tab {{ $subtab === 'all-transactions' ? 'active' : '' }}" data-subtab="all-transactions" onclick="showProfitSubTab('all-transactions')">Transactions</div>
                <div class="profit-sub-tab {{ $subtab === 'sales-log' ? 'active' : '' }}" data-subtab="sales-log" onclick="showProfitSubTab('sales-log')">All Sales Log</div>
                <div class="profit-sub-tab {{ $subtab === 'summary' ? 'active' : '' }}" data-subtab="summary" onclick="showProfitSubTab('summary')">Sales Summary by Products</div>
            </div>
            <div class="rep-section-title">Sales Details</div>
            <table class="rep-table rep-table-striped" id="salesLogTable">
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
                        <tr class="rep-transaction-row" data-transaction='{{ json_encode([
                            "order_id" => $log->order_id ?? "N/A",
                            "product_name" => $log->product_name ?? "N/A",
                            "quantity" => $log->quantity ?? 0,
                            "unit_price" => $log->unit_price ?? 0,
                            "total_price" => $log->total_price ?? 0,
                            "customer_name" => $log->customer_name ?? "Unknown",
                            "order_type" => $log->order_type ?? "N/A",
                            "status" => $log->status ?? "N/A",
                            "special_instructions" => $log->special_instructions ?? "N/A",
                            "created_at" => $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format("F j Y / g:i A") : "N/A"
                        ]) }}'>
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
    <div id="summary-sub-content" style="display: {{ $subtab === 'summary' ? 'block' : 'none' }};">
        <div class="inv-table-container" id="salesSummaryTableContainer">
            <div class="profit-sub-tabs">
                <div class="profit-sub-tab {{ $subtab === 'all-transactions' ? 'active' : '' }}" data-subtab="all-transactions" onclick="showProfitSubTab('all-transactions')">Transactions</div>
                <div class="profit-sub-tab {{ $subtab === 'sales-log' ? 'active' : '' }}" data-subtab="sales-log" onclick="showProfitSubTab('sales-log')">All Sales Log</div>
                <div class="profit-sub-tab {{ $subtab === 'summary' ? 'active' : '' }}" data-subtab="summary" onclick="showProfitSubTab('summary')">Sales Summary by Products</div>
            </div>
            <div class="rep-section-title">Sales Summary by Product</div>
            <table class="rep-table rep-table-striped" id="salesSummaryTable">
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
</div>

<div class="trn-modal-overlay" id="receiptModal">
    <div class="trn-receipt-modal">
        <div class="trn-receipt-header" id="receiptModalHeader"></div>
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
            <div class="rep-metric-title">Spoiled Items</div>
            <div class="rep-metric-value">{{ $trashCount }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Damaged Items</div>
            <div class="rep-metric-value">{{ $damagedCount }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Total loss from spoiled items</div>
            <div class="rep-metric-value rep-loss">₱{{ number_format($trashLoss, 2) }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Total loss from damaged items</div>
            <div class="rep-metric-value rep-loss">₱{{ number_format($damagedLoss, 2) }}</div>
        </div>
    </div>
    <div id="thrown-content" style="display: {{ $subtab === 'thrown' || ($tab === 'loss' && !$subtab) ? 'block' : 'none' }};">
        <div class="inv-table-container" id="trash-table">
            <div class="loss-sub-tabs">
                <div class="loss-sub-tab {{ $subtab === 'thrown' || ($tab === 'loss' && !$subtab) ? 'active' : '' }}" data-subtab="thrown" onclick="showLossSubTab('thrown')">Spoiled Items</div>
                <div class="loss-sub-tab {{ $subtab === 'damaged' ? 'active' : '' }}" data-subtab="damaged" onclick="showLossSubTab('damaged')">Damaged Items</div>
            </div>
            <div class="rep-section-title">List of Spoiled Items</div>
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
                        <td>No spoils items found for this period.</td>
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
            <div class="loss-sub-tabs">
                <div class="loss-sub-tab {{ $subtab === 'thrown' || ($tab === 'loss' && !$subtab) ? 'active' : '' }}" data-subtab="thrown" onclick="showLossSubTab('thrown')">Spoiled Items</div>
                <div class="loss-sub-tab {{ $subtab === 'damaged' ? 'active' : '' }}" data-subtab="damaged" onclick="showLossSubTab('damaged')">Damaged Items</div>
            </div>
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
                    <tr data-id="{{ $damagedProduct->id }}">
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
    // Initialize transactionsTable
    const transactionsTable = $('#transactionsTable');
    const hasTransactionRows = transactionsTable.find('tbody tr').length > 0 && !transactionsTable.find('tbody tr td[colspan]').length;
    if (hasTransactionRows) {
        transactionsTable.DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            autoWidth: false,
            columnDefs: [{ orderable: true, targets: '_all' }]
        });
    } else {
        transactionsTable.addClass('no-datatables');
    }

    // Initialize salesLogTable
    const salesLogTable = $('#salesLogTable');
    const hasSalesLogRows = salesLogTable.find('tbody tr').length > 0 && !salesLogTable.find('tbody tr td[colspan]').length;
    if (hasSalesLogRows) {
        salesLogTable.DataTable({
            pageLength: 10,
            responsive: true,
            order: [[9, 'desc']],
            autoWidth: false,
            columnDefs: [{ orderable: true, targets: '_all' }]
        });
    } else {
        salesLogTable.addClass('no-datatables');
    }

    // Initialize salesSummaryTable
    const salesSummaryTable = $('#salesSummaryTable');
    const hasSalesSummaryRows = salesSummaryTable.find('tbody tr').length > 0 && !salesSummaryTable.find('tbody tr td[colspan]').length;
    if (hasSalesSummaryRows) {
        salesSummaryTable.DataTable({
            pageLength: 10,
            responsive: true,
            order: [[1, 'desc']],
            autoWidth: false,
            columnDefs: [{ orderable: true, targets: '_all' }]
        });
    } else {
        salesSummaryTable.addClass('no-datatables');
    }

    // Initialize trashTable
    const trashTable = $('#trashTable');
    const hasTrashRows = trashTable.find('tbody tr').length > 0 && !trashTable.find('tbody tr td[colspan]').length;
    if (hasTrashRows) {
        trashTable.DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            autoWidth: false,
            columnDefs: [{ orderable: true, targets: '_all' }]
        });
    } else {
        trashTable.addClass('no-datatables');
    }

    // Initialize damagedProductsTable
    const damagedTable = $('#damagedProductsTable');
    const hasDamagedRows = damagedTable.find('tbody tr').length > 0 && !damagedTable.find('tbody tr td[colspan]').length;
    if (hasDamagedRows) {
        damagedTable.DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            autoWidth: false,
            columnDefs: [{ orderable: true, targets: '_all' }]
        });
    } else {
        damagedTable.addClass('no-datatables');
    }

    // Transaction row click handler for transactionsTable and salesLogTable
    $(document).on('click', '.rep-transaction-row', function() {
        const transaction = $(this).data('transaction');
        let detailsHtml;
        let modalTitle;

        if (transaction.transaction_id) { // transactionsTable
            modalTitle = 'Transaction Receipt';
            const products = transaction.product_names ? transaction.product_names.split(',').map(product => product.trim()) : [];
            let productsHtml = '<ul class="trn-product-list">';
            productsHtml += products.length > 0 ? products.map(product => `<li>${product}</li>`).join('') : '<li>No products listed</li>';
            productsHtml += '</ul>';
            detailsHtml = `
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
        } else { // salesLogTable
            modalTitle = 'Sales Receipt';
            detailsHtml = `
                <p><strong>Order ID:</strong> <span>${transaction.order_id}</span></p>
                <p><strong>Product Name:</strong> <span>${transaction.product_name}</span></p>
                <p><strong>Quantity:</strong> <span>${transaction.quantity}</span></p>
                <p><strong>Unit Price:</strong> <span>₱${parseFloat(transaction.unit_price || 0).toFixed(2)}</span></p>
                <p><strong>Total Price:</strong> <span>₱${parseFloat(transaction.total_price || 0).toFixed(2)}</span></p>
                <p><strong>Customer Name:</strong> <span>${transaction.customer_name}</span></p>
                <p><strong>Order Type:</strong> <span>${transaction.order_type}</span></p>
                <p><strong>Status:</strong> <span>${transaction.status}</span></p>
                <p><strong>Special Instructions:</strong></p>
                <div class="trn-special-instructions">${transaction.special_instructions}</div>
                <p><strong>Date:</strong> <span>${transaction.created_at}</span></p>
            `;
        }
        $('#receiptModalHeader').text(modalTitle);
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

    // Initialize print buttons
    setupPrintButtons();
});

function printTable(tableId, tableTitle) {
    const table = document.getElementById(tableId);
    if (!table) return;

    // Collect metrics based on the table being printed
    let metricsHtml = '';
    if (['transactionsTable', 'salesLogTable', 'salesSummaryTable'].includes(tableId)) {
        // Profit tab metrics
        const revenue = document.querySelector('#profit-content .rep-metric-box:nth-child(1) .rep-metric-value').textContent;
        const quantitySold = document.querySelector('#profit-content .rep-metric-box:nth-child(2) .rep-metric-value').textContent;
        const loss = document.querySelector('#profit-content .rep-metric-box:nth-child(3) .rep-metric-value').textContent;
        const profit = document.querySelector('#profit-content .rep-metric-box:nth-child(4) .rep-metric-value').textContent;
        metricsHtml = `
            <div class="rep-metrics">
                <div class="rep-metric-box">
                    <div class="rep-metric-title">Revenue</div>
                    <div class="rep-metric-value rep-profit">${revenue}</div>
                </div>
                <div class="rep-metric-box">
                    <div class="rep-metric-title">Total Quantity Sold</div>
                    <div class="rep-metric-value">${quantitySold}</div>
                </div>
                <div class="rep-metric-box">
                    <div class="rep-metric-title">Loss from spoiled and damaged items</div>
                    <div class="rep-metric-value rep-loss">${loss}</div>
                </div>
                <div class="rep-metric-box">
                    <div class="rep-metric-title">Profit</div>
                    <div class="rep-metric-value rep-profit">${profit}</div>
                </div>
            </div>
        `;
    } else if (['trashTable', 'damagedProductsTable'].includes(tableId)) {
        // Loss tab metrics
        const totalLoss = document.querySelector('#loss-content .rep-metric-box:nth-child(1) .rep-metric-value').textContent;
        const spoiledItems = document.querySelector('#loss-content .rep-metric-box:nth-child(2) .rep-metric-value').textContent;
        const damagedItems = document.querySelector('#loss-content .rep-metric-box:nth-child(3) .rep-metric-value').textContent;
        const spoiledLoss = document.querySelector('#loss-content .rep-metric-box:nth-child(4) .rep-metric-value').textContent;
        const damagedLoss = document.querySelector('#loss-content .rep-metric-box:nth-child(5) .rep-metric-value').textContent;
        metricsHtml = `
            <div class="rep-metrics">
                <div class="rep-metric-box">
                    <div class="rep-metric-title">Total Loss</div>
                    <div class="rep-metric-value rep-loss">${totalLoss}</div>
                </div>
                <div class="rep-metric-box">
                    <div class="rep-metric-title">Spoiled Items</div>
                    <div class="rep-metric-value">${spoiledItems}</div>
                </div>
                <div class="rep-metric-box">
                    <div class="rep-metric-title">Damaged Items</div>
                    <div class="rep-metric-value">${damagedItems}</div>
                </div>
                <div class="rep-metric-box">
                    <div class="rep-metric-title">Total loss from spoiled items</div>
                    <div class="rep-metric-value rep-loss">${spoiledLoss}</div>
                </div>
                <div class="rep-metric-box">
                    <div class="rep-metric-title">Total loss from damaged items</div>
                    <div class="rep-metric-value rep-loss">${damagedLoss}</div>
                </div>
            </div>
        `;
    }

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>${tableTitle}</title>
            <style>
                body { 
                    font-family: 'Poppins', Arial, sans-serif; 
                    margin: 10mm; 
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    box-sizing: border-box;
                }
                .table-container {
                    display: flex;
                    justify-content: center;
                    width: 100%;
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    table-layout: fixed; 
                    font-size: 14px; 
                    box-sizing: border-box;
                }
                th, td { 
                    border: 1px solid #ddd; 
                    padding: 8px; 
                    text-align: left; 
                    overflow: hidden; 
                    white-space: normal; 
                    overflow-wrap: break-word; 
                    box-sizing: border-box;
                }
                th { 
                    background-color: #10394f; 
                    color: white; 
                    font-weight: bold; 
                }
                tr:nth-child(even) { 
                    background-color: #f9f9f9; 
                }
                h2 { 
                    text-align: center; 
                    color: #333; 
                    font-size: 18px; 
                    margin-bottom: 15px; 
                }
                /* Metrics styles */
                .rep-metrics {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                    width: 100%;
                    max-width: ${tableId === 'salesLogTable' || tableId === 'damagedProductsTable' ? '960px' : '800px'};
                }
                .rep-metric-box {
                    flex: 1;
                    margin: 0 10px;
                    padding: 20px;
                    background-color: #fff;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    text-align: center;
                }
                .rep-metric-title {
                    font-size: 18px;
                    font-weight: 500;
                    color: #555;
                    margin-bottom: 10px;
                }
                .rep-metric-value {
                    font-size: 24px;
                    font-weight: 600;
                }
                .rep-metric-value.rep-profit {
                    color: #4CAF50;
                }
                .rep-metric-value.rep-loss {
                    color: #dc3545;
                }
                /* Specific styles for ID columns */
                table th:nth-child(1), table td:nth-child(1) { 
                    word-break: break-all; 
                    hyphens: auto;
                }
                /* Column widths for transactionsTable */
                ${tableId === 'transactionsTable' ? `
                    table th:nth-child(1), table td:nth-child(1) { width: 100px; } /* Transaction ID */
                    table th:nth-child(2), table td:nth-child(2) { width: 200px; } /* Customer Name */
                ` : ''}
                /* Column widths for salesLogTable */
                ${tableId === 'salesLogTable' ? `
                    table th:nth-child(1), table td:nth-child(1) { width: 60px; } /* Order ID */
                    table th:nth-child(2), table td:nth-child(2) { width: 120px; } /* Product Name */
                    table th:nth-child(3), table td:nth-child(3) { width: 50px; } /* Quantity */
                    table th:nth-child(4), table td:nth-child(4) { width: 60px; } /* Unit Price */
                    table th:nth-child(5), table td:nth-child(5) { width: 60px; } /* Total Price */
                    table th:nth-child(6), table td:nth-child(6) { width: 100px; } /* Customer Name */
                    table th:nth-child(7), table td:nth-child(7) { width: 60px; } /* Order Type */
                    table th:nth-child(8), table td:nth-child(8) { width: 60px; } /* Status */
                    table th:nth-child(9), table td:nth-child(9) { width: 120px; } /* Special Instructions */
                    table th:nth-child(10), table td:nth-child(10) { width: 100px; } /* Date */
                ` : ''}
                /* Column widths for salesSummaryTable */
                ${tableId === 'salesSummaryTable' ? `
                    table th:nth-child(1), table td:nth-child(1) { width: 200px; } /* Product Name */
                    table th:nth-child(2), table td:nth-child(2) { width: 100px; } /* Total Quantity Sold */
                    table th:nth-child(3), table td:nth-child(3) { width: 100px; } /* Total Revenue */
                ` : ''}
                /* Column widths for trashTable */
                ${tableId === 'trashTable' ? `
                    table th:nth-child(1), table td:nth-child(1) { width: 40px; } /* ID */
                    table th:nth-child(2), table td:nth-child(2) { width: 120px; } /* Product Name */
                    table th:nth-child(3), table td:nth-child(3) { width: 100px; } /* Category */
                    table th:nth-child(4), table td:nth-child(4) { width: 50px; } /* Quantity */
                    table th:nth-child(5), table td:nth-child(5) { width: 120px; } /* Reason */
                    table th:nth-child(6), table td:nth-child(6) { width: 60px; } /* Total Loss */
                ` : ''}
                /* Column widths for damagedProductsTable */
                ${tableId === 'damagedProductsTable' ? `
                    table th:nth-child(1), table td:nth-child(1) { width: 40px; } /* ID */
                    table th:nth-child(2), table td:nth-child(2) { width: 110px; } /* Product Name */
                    table th:nth-child(3), table td:nth-child(3) { width: 50px; } /* Quantity */
                    table th:nth-child(4), table td:nth-child(4) { width: 60px; } /* Price per Item */
                    table th:nth-child(5), table td:nth-child(5) { width: 60px; } /* Total Cost */
                    table th:nth-child(6), table td:nth-child(6) { width: 90px; } /* Reason */
                    table th:nth-child(7), table td:nth-child(7) { width: 90px; } /* Supplier */
                    table th:nth-child(8), table td:nth-child(8) { width: 70px; } /* Status */
                    table th:nth-child(9), table td:nth-child(9) { width: 90px; } /* Reported At */
                    table th:nth-child(10), table td:nth-child(10) { width: 90px; } /* Return Date */
                    table th:nth-child(11), table td:nth-child(11) { width: 90px; } /* Return Notes */
                ` : ''}
                @media print {
                    @page { 
                        size: A4 landscape; 
                        margin: 10mm; 
                    }
                    body { 
                        margin: 0; 
                        -webkit-print-color-adjust: exact; 
                        print-color-adjust: exact; 
                    }
                    .table-container { 
                        display: flex;
                        justify-content: center;
                        width: 100%;
                    }
                    table { 
                        page-break-inside: auto; 
                        width: 100% !important; 
                        max-width: ${tableId === 'salesLogTable' || tableId === 'damagedProductsTable' ? '960px' : '800px'}; 
                    }
                    th, td { 
                        font-size: 12px; 
                        padding: 6px; 
                    }
                    h2 { 
                        font-size: 16px; 
                    }
                    .rep-metrics {
                        page-break-after: avoid;
                    }
                    .rep-metric-box {
                        flex: 1;
                        margin: 0 5px;
                        padding: 15px;
                    }
                    .rep-metric-title {
                        font-size: 16px;
                    }
                    .rep-metric-value {
                        font-size: 20px;
                    }
                }
            </style>
        </head>
        <body>
            <h2>${tableTitle}</h2>
            ${metricsHtml}
            <div class="table-container">
                ${table.outerHTML}
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.onload = function() {
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    };
}

function setupPrintButtons() {
    const tables = [
        { id: 'transactionsTable', title: 'Transaction Details', containerId: 'transaction-table' },
        { id: 'salesLogTable', title: 'Sales Details', containerId: 'salesLogTableContainer' },
        { id: 'salesSummaryTable', title: 'Sales Summary by Product', containerId: 'salesSummaryTableContainer' },
        { id: 'trashTable', title: 'List of Thrown Away Items', containerId: 'trash-table' },
        { id: 'damagedProductsTable', title: 'List of Marked as Loss Items', containerId: 'damaged-table' }
    ];

    tables.forEach(table => {
        const container = document.getElementById(table.containerId);
        if (container) {
            const printButton = document.createElement('button');
            printButton.className = 'trn-modal-print';
            printButton.innerText = 'Print Table';
            printButton.style.marginBottom = '10px';
            printButton.style.padding = '8px 16px';
            printButton.style.cursor = 'pointer';
            printButton.onclick = () => printTable(table.id, table.title);
            const sectionTitle = container.querySelector('.rep-section-title');
            if (sectionTitle) {
                container.insertBefore(printButton, sectionTitle);
            } else {
                container.prepend(printButton);
            }
        }
    });
}

function showTab(tabName) {
    // Update active tab styling
    document.querySelectorAll('.rep-all-tabs .rep-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`.rep-tab[onclick="showTab('${tabName}')"]`).classList.add('active');

    // Update hidden input and display content
    document.getElementById('tabInput').value = tabName;
    document.getElementById('profit-content').style.display = tabName === 'profit' ? 'block' : 'none';
    document.getElementById('loss-content').style.display = tabName === 'loss' ? 'block' : 'none';

    // Set default sub-tab for the selected tab
    const currentSubTab = document.getElementById('subtabInput').value;
    if (tabName === 'profit') {
        showProfitSubTab(currentSubTab && ['all-transactions', 'sales-log', 'summary'].includes(currentSubTab) ? currentSubTab : 'all-transactions');
    } else if (tabName === 'loss') {
        showLossSubTab(currentSubTab && ['thrown', 'damaged'].includes(currentSubTab) ? currentSubTab : 'thrown');
    }

    // Submit form to update server-side state
    submitForm();
}

function showProfitSubTab(subTabName) {
    // Remove active class from all profit sub-tabs
    document.querySelectorAll('.profit-sub-tabs .profit-sub-tab').forEach(tab => {
        tab.classList.remove('active');
    });

    // Add active class to all instances of the selected sub-tab
    document.querySelectorAll(`.profit-sub-tab[data-subtab="${subTabName}"]`).forEach(tab => {
        tab.classList.add('active');
    });

    // Update hidden input and display content
    document.getElementById('subtabInput').value = subTabName;
    document.getElementById('all-transactions-sub-content').style.display = subTabName === 'all-transactions' ? 'block' : 'none';
    document.getElementById('sales-log-sub-content').style.display = subTabName === 'sales-log' ? 'block' : 'none';
    document.getElementById('summary-sub-content').style.display = subTabName === 'summary' ? 'block' : 'none';
}

function showLossSubTab(subTabName) {
    // Remove active class from all loss sub-tabs
    document.querySelectorAll('.loss-sub-tabs .loss-sub-tab').forEach(tab => {
        tab.classList.remove('active');
    });

    // Add active class to all instances of the selected sub-tab
    document.querySelectorAll(`.loss-sub-tab[data-subtab="${subTabName}"]`).forEach(tab => {
        tab.classList.add('active');
    });

    // Update hidden input and display content
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
    submitForm();
}

function submitForm() {
    document.getElementById('timePeriodForm').submit();
}
</script>
@endpush