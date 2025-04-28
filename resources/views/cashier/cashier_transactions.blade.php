@extends('cashier.layout')

@section('title', 'Transaction_cashier')

@section('content')
    <div class="trn-header">Transactions</div>

    <!-- Search and Filter Form -->
   

    <!-- Transaction Table -->
    <div class="trn-table-container active" id="transaction-table">
        <div class="trn-section-title">Transaction List</div>
        <div class="trn-table-scroll-container">
            <table class="trn-inventory-table trn-table-striped" id="transactionsTable">
                <thead>
                    <tr>
                        <th>Products Ordered</th>
                        <th>Customer Name</th>
                        <th>Special Instruction</th>
                        <th>Order Type</th>
                        <th>Status</th>
                        <th>Total Quantity</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summarizedTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->product_names }}</td>
                            <td>{{ $transaction->customer_name }}</td>
                            <td>{{ $transaction->special_instructions ?: 'N/A' }}</td>
                            <td>{{ $transaction->order_type }}</td>
                            <td>{{ $transaction->status }}</td>
                            <td>{{ $transaction->total_quantity }}</td>
                            <td>₱{{ number_format($transaction->total_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No transactions found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include DataTables CSS and JS -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#transactionsTable').DataTable({
                // Enable DataTables search
                searching: true,
                // Enable pagination
                paging: true,
                // Enable sorting
                ordering: true,
                // Add export buttons (Excel, etc.)
            
                // Optional: Customize language for empty table
                language: {
                    emptyTable: "No transactions found for this period."
                }
            });

            // Handle select changes for month and year filters
            $('.trn-month-filter, .trn-year-filter').on('change', function() {
                $('#transactionFilterForm').submit();
            });

          
        });
    </script>
@endsection