@extends('manager.layout')

@section('title', 'Manager Reports')

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
            <option value="all">All Months</option>
            @for ($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                </option>
            @endfor
        </select>
        <select class="rep-year-filter" name="year" onchange="submitForm()">
            <option value="all">All Years</option>
            @for ($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>
    <input type="hidden" name="period" id="periodInput" value="{{ $period }}">
    <input type="hidden" name="tab" id="tabInput" value="{{ $tab }}">
</form>

<div id="profit-content" style="display: {{ $tab === 'profit' ? 'block' : 'none' }};">
    <div class="rep-metrics">
        <div class="rep-metric-box">
            <div class="rep-metric-title">Revenue</div>
            <div class="rep-metric-value rep-profit">₱{{ number_format($revenue, 2) }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Loss from thrown items</div>
            <div class="rep-metric-value rep-loss">₱{{ number_format($totalLoss, 2) }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Profit</div>
            <div class="rep-metric-value rep-profit">₱{{ number_format($revenue-$totalLoss, 2) }}</div>
        </div>
    </div>
    <div class="inv-table-container" id="transaction-table">
        <div class="rep-section-title">Transaction List</div>
        <table class="rep-table rep-table-striped">
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
                @forelse ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->product_name }}</td>
                        <td>{{ $transaction->customer_name }}</td>
                        <td>{{ $transaction->special_instructions ?: 'N/A' }}</td>
                        <td>{{ $transaction->order_type }}</td>
                        <td>{{ $transaction->status }}</td>
                        <td>{{ $transaction->quantity }}</td>
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

<div id="loss-content" style="display: {{ $tab === 'loss' ? 'block' : 'none' }};">
    <div class="rep-metrics">
        <div class="rep-metric-box">
            <div class="rep-metric-title">Total Loss</div>
            <div class="rep-metric-value rep-loss">₱{{ number_format($totalLoss, 2) }}</div>
        </div>
        <div class="rep-metric-box">
            <div class="rep-metric-title">Items Thrown</div>
            <div class="rep-metric-value">{{ $trashCount }}</div>
        </div>
    </div>
    <div class="inv-table-container" id="loss-table">
        <div class="rep-section-title">List of thrown away items</div>
        <table class="rep-table">
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
                    <td colspan="6">No items found for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
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
@endsection