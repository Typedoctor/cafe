@extends('cashier.layout')

@section('title', 'Transaction_cashier')

@section('content')
<head>
    <link rel="stylesheet" href="{{ asset('css/transaction.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
</head>


<!-- Search and Filter Container -->
<div class="trn-search-filter-container">
    <form id="transactionFilterForm" method="GET" action="{{ route('cashier-transactions.index') }}">
        <div class="trn-filter-row">
            <div class="trn-filter-box">
                @php
                    $selectedMonth = request()->has('month') ? request('month') : now()->month;
                    $selectedYear = request()->has('year') ? request('year') : now()->year;
                @endphp
                <select name="month" class="trn-month-filter">
                    <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>All Months</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (string)$selectedMonth === (string)$m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year" class="trn-year-filter">
                    <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>All Years</option>
                    @for ($y = now()->year; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ (string)$selectedYear === (string)$y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
                <a href="{{ route('cashier-transactions.index', ['month' => now()->month, 'year' => now()->year]) }}" class="trn-reset-btn">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Transaction Table -->
<div class="trn-table-container active" id="transaction-table">
    <div class="trn-section-title">Transaction List</div>
    <div class="trn-table-scroll-container">
        <table class="trn-inventory-table trn-table-striped" id="transactionsTable">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Customer Name</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summarizedTransactions as $transaction)
                    <tr class="trn-transaction-row" data-transaction="{{ json_encode([
                        "transaction_id" => $transaction->transaction_id ?? "N/A",
                        "customer_name" => $transaction->customer_name ?? "Unknown",
                        "product_names" => $transaction->product_names ?? "",
                        "product_quantities" => $transaction->product_quantities ?? "", // Add this line
                        "special_instructions" => $transaction->special_instructions ?? "N/A",
                        "order_type" => $transaction->order_type ?? "N/A",
                        "status" => $transaction->status ?? "N/A",
                        "total_quantity" => (int) ($transaction->total_quantity ?? 0),
                        "money_received" => (float) ($transaction->money_received ?? 0),
                        "change" => (float) ($transaction->change ?? 0),
                        "total_price" => (float) ($transaction->total_price ?? 0),
                        "created_at" => isset($transaction->created_at) && $transaction->created_at ? \Carbon\Carbon::parse($transaction->created_at)->format("M j Y / g:i A") : "N/A",
                        "product_summary" => $transaction->product_summary ?? [],
                    ]) }}">
                        <td>{{ $transaction->transaction_id ?? "N/A" }}</td>
                        <td>{{ $transaction->customer_name ?? "Unknown" }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>No transactions found</td>
                        <td>-</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Receipt Modal -->
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

<!-- Include DataTables CSS and JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        const table = $('#transactionsTable');
        const hasTransactionRows = table.find('tbody tr.trn-transaction-row').length > 0;

        if (hasTransactionRows) {
            table.DataTable({
                searching: true,
                paging: true,
                ordering: true,
                info: true,
                lengthChange: true,
                pageLength: 10,
                language: {
                    emptyTable: "No transactions found for this period."
                }
            });
        } else {
            table.addClass('no-datatables');
        }

        // Handle row click to show modal
        $(document).on('click', '.trn-transaction-row', function() {
            const transaction = $(this).data('transaction');
            // Use product_summary for product list
            let productsHtml = '<ul class="trn-product-list">';
            if (transaction.product_summary && Object.keys(transaction.product_summary).length > 0) {
                for (const [product, qty] of Object.entries(transaction.product_summary)) {
                    productsHtml += `<li>${product} <span style="color:#888;">(x${qty})</span></li>`;
                }
            } else {
                productsHtml += '<li>No products listed</li>';
            }
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

        // Print modal
        $('#printModal').on('click', function() {
            window.print();
        });

        // Close modal
        $('#closeModal').on('click', function() {
            $('#receiptModal').removeClass('active').delay(300).queue(function(next) {
                $(this).css('display', 'none');
                next();
            });
        });

        // Close modal when clicking outside
        $(document).on('click', '.trn-modal-overlay', function(e) {
            if (e.target === this) {
                $('#receiptModal').removeClass('active').delay(300).queue(function(next) {
                    $(this).css('display', 'none');
                    next();
                });
            }
        });

        // Auto-submit on select change
        $('.trn-month-filter, .trn-year-filter').on('change', function() {
            $('#transactionFilterForm').submit();
        });

        // Filter button submit
        $('.trn-filter-btn').on('click', function(e) {
            e.preventDefault();
            $('#transactionFilterForm').submit();
        });
    });
</script>
@endsection
