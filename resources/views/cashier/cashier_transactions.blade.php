@extends('cashier.layout')

@section('title', 'Transaction_cashier')

@section('content')
    <div class="trn-header">Transactions</div>

    <!-- Search and Filter Form -->
    <form id="transactionFilterForm" action="{{ route('cashier.cashier_transactions') }}" method="GET" class="trn-search-filter-form">
        <div class="trn-search-filter-container">
            <!-- Single Row for Search, Filters, Buttons, and Export -->
            <div class="trn-filter-row">
                <!-- Search Box -->
                <div class="trn-search-box">
                    <input type="text" name="search" placeholder="Search by customer name..." value="{{ request('search') }}"
                           autocomplete="off">
                </div>
                <!-- Filter Box -->
                <div class="trn-filter-box">
                    <!-- Month Filter -->
                    <select class="trn-month-filter" name="month">
                        <option value="all" {{ request('month') === 'all' ? 'selected' : '' }}>All Months</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                    <!-- Year Filter -->
                    <select class="trn-year-filter" name="year">
                        <option value="all" {{ request('year') === 'all' ? 'selected' : '' }}>All Years</option>
                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <!-- Filter and Reset Buttons -->
                    <a href="{{ route('cashier.cashier_transactions') }}" class="trn-btn trn-reset-btn">Reset</a>
                    <!-- Export Button -->
                    <a href="{{ route('cashier.transactions.export') . (request()->query() ? '?' . http_build_query(request()->query()) : '') }}" 
                       class="trn-btn trn-export-btn">Export to Excel</a>
                </div>
            </div>
        </div>
    </form>

    <!-- Transaction Table -->
    <div class="trn-table-container active" id="transaction-table">
        <div class="trn-section-title">Transaction List</div>
        <div class="trn-table-scroll-container">
            <table class="trn-inventory-table trn-table-striped">
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

    <script>
        // Handle select changes
        document.querySelectorAll('.trn-month-filter, .trn-year-filter').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('transactionFilterForm').submit();
            });
        });
    </script>
@endsection