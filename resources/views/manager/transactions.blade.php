@extends('manager.layout')

@section('title', 'Transaction_cashier')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <div class="trn-header">Transactions</div>

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
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#transactionsTable').DataTable({
                searching: true,
                paging: true,
                ordering: true
            });
        });
    </script>
@endpush
