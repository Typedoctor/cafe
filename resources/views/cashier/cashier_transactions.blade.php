@extends('cashier.layout')

@section('title', 'Transaction')

@section('content')
    <div class="header">Transactions</div>

    <!-- Search and Filter Form -->
    <form id="transactionFilterForm" action="{{ route('transactions.index') }}" method="GET" class="search-filter-form">
        <div class="trn-search-filter-container">
            <!-- Single Row for Search, Filters, Buttons, and Export -->
            <div class="filter-row">
                <!-- Search Box -->
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search by customer name..." value="{{ request('search') }}"
                           autocomplete="off">
                </div>
                <!-- Filter Box -->
                <div class="filter-box">
                    <!-- Month Filter -->
                    <select class="month-filter" name="month">
                        <option value="all" {{ request('month') === 'all' ? 'selected' : '' }}>All Months</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                    <!-- Year Filter -->
                    <select class="year-filter" name="year">
                        <option value="all" {{ request('year') === 'all' ? 'selected' : '' }}>All Years</option>
                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <!-- Filter and Reset Buttons -->
                    
                    <a href="{{ route('transactions.index') }}" class="btn reset-btn">Reset</a>
                    <!-- Export Button -->
                    <a  class="btn export-btn">Export to CSV</a>
                </div>
            </div>
        </div>
    </form>

    <!-- Transaction Table -->
    <div class="table-container active" id="transaction-table">
        <div class="section-title">Transaction List</div>
        <div class="table-scroll-container">
            <table class="inventory-table table-striped">
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
                            <td>{{ $transaction->special_instruction ?? 'N/A' }}</td>
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
        <!-- Pagination Links -->
        <div style="margin-top: 20px;">
            {{ $summarizedTransactions->appends(request()->query())->links() }}
        </div>
    </div>

    <script>
        // Handle select changes
        document.querySelectorAll('.month-filter, .year-filter').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('transactionFilterForm').submit();
            });
        });
    </script>
@endsection