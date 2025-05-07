
@extends('cashier.layout')

@section('title', 'Transaction_cashier')

@section('content')
<head>
    <link rel="stylesheet" href="{{ asset('css/transaction.css') }}">
</head>

<div class="trn-header">Transactions</div>

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
                    <tr class="trn-transaction-row" data-transaction='{{ json_encode([
                        "transaction_id" => $transaction->transaction_id ?? "N/A",
                        "customer_name" => $transaction->customer_name ?? "Unknown",
                        "product_names" => $transaction->product_names ?? "",
                        "special_instructions" => $transaction->special_instructions ?? null,
                        "order_type" => $transaction->order_type ?? null,
                        "status" => $transaction->status ?? null,
                        "total_quantity" => $transaction->total_quantity ?? 0,
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

<!-- Receipt Modal -->
<div class="trn-modal-overlay" id="receiptModal">
    <div class="trn-receipt-modal">
        <div class="trn-receipt-header">Transaction Receipt</div>
        <div class="trn-receipt-details" id="receiptDetails">
            <!-- Details will be populated by JavaScript -->
        </div>
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
        // Initialize DataTables
        $('#transactionsTable').DataTable({
            searching: true,
            paging: true,
            ordering: true,
            language: {
                emptyTable: "No transactions found for this period."
            }
        });

        // Handle row click to show modal
        $(document).on('click', '.trn-transaction-row', function() {
            const transaction = $(this).data('transaction');
            console.log("Transaction data:", transaction); // Debug to check data

            // Split product names into an array and create a list, handling null/empty cases
            const products = transaction.product_names ? transaction.product_names.split(',').map(product => product.trim()) : [];
            let productsHtml = '<ul class="trn-product-list">';
            productsHtml += products.length > 0 ? products.map(product => `<li>${product}</li>`).join('') : '<li>No products listed</li>';
            productsHtml += '</ul>';

            // Populate receipt details
            const detailsHtml = `
                <p><strong>Transaction ID:</strong> <span>${transaction.transaction_id}</span></p>
                <p><strong>Customer Name:</strong> <span>${transaction.customer_name || 'N/A'}</span></p>
                <p><strong>Completed Order at:</strong> <span>${transaction.created_at || 'N/A'}</span></p>
                <p><strong>Products Ordered:</strong> ${productsHtml}</p>
                <p><strong>Special Instructions:</strong></p>
                <div class="trn-special-instructions">${transaction.special_instructions || 'N/A'}</div>
                <p><strong>Order Type:</strong> <span>${transaction.order_type || 'N/A'}</span></p>
                <p><strong>Status:</strong> <span>${transaction.status || 'N/A'}</span></p>
                <p><strong>Total Quantity:</strong> <span>${transaction.total_quantity || 0}</span></p>
                <p class="trn-total"><strong>Total Price:</strong> <span>₱${parseFloat(transaction.total_price || 0).toFixed(2)}</span></p>
            `;
            $('#receiptDetails').html(detailsHtml);

            // Show modal with animation
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

        // Handle select changes for month and year filters
        $('.trn-month-filter, .trn-year-filter').on('change', function() {
            $('#transactionFilterForm').submit();
        });
    });
</script>
@endsection
