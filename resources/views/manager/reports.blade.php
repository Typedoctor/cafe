
@extends('manager.layout')

@section('title', 'Manager Reports')

@section('content')
<div class="header">Reports</div>

<!-- All Tabs (Profit, Loss, Daily, Monthly, Yearly) -->
<form id="timePeriodForm" action="{{ route('reports.index') }}" method="GET">
    <div class="all-tabs">
        <div class="tab profit {{ $tab === 'profit' ? 'active' : '' }}" onclick="showTab('profit')">PROFIT</div>
        <div class="tab loss {{ $tab === 'loss' ? 'active' : '' }}" onclick="showTab('loss')">LOSS</div>
        <div class="gap"></div>
        <div class="time-tab {{ $period === 'daily' ? 'active' : '' }}" onclick="changeTimePeriod('daily')">Daily</div>
        <div class="time-tab {{ $period === 'monthly' ? 'active' : '' }}" onclick="changeTimePeriod('monthly')">Monthly</div>
        <div class="time-tab {{ $period === 'yearly' ? 'active' : '' }}" onclick="changeTimePeriod('yearly')">Yearly</div>
    </div>
    <input type="hidden" name="period" id="periodInput" value="{{ $period }}">
    <input type="hidden" name="tab" id="tabInput" value="{{ $tab }}">
</form>

<!-- Profit Content -->
<div id="profit-content" style="display: {{ $tab === 'profit' ? 'block' : 'none' }};">
    <div class="metrics">
        <div class="metric-box">
            <div class="metric-title">Profit</div>
            <div class="metric-value profit">P9,876</div>
        </div>
        <div class="metric-box">
            <div class="metric-title">Loss from thrown items</div>
            <div class="metric-value loss">₱{{ number_format($totalLoss, 2) }}</div>
        </div>
    </div>

    <div class="table-container active" id="profit-table">
        <div class="section-title">List of sales</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Unit Price</th>
                    <th>Quantity Sold</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1001</td>
                    <td>Premium Coffee</td>
                    <td>Beverages</td>
                    <td>P120</td>
                    <td>45</td>
                    <td>P5,400</td>
                </tr>
                <tr>
                    <td>1002</td>
                    <td>Chocolate Cake</td>
                    <td>Desserts</td>
                    <td>P85</td>
                    <td>32</td>
                    <td>P2,720</td>
                </tr>
                <tr>
                    <td>1003</td>
                    <td>Sandwich</td>
                    <td>Meals</td>
                    <td>P65</td>
                    <td>27</td>
                    <td>P1,755</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Loss Content -->
<div id="loss-content" style="display: {{ $tab === 'loss' ? 'block' : 'none' }};">
    <div class="metrics">
        <div class="metric-box">
            <div class="metric-title">Total Loss</div>
            <div class="metric-value loss">₱{{ number_format($totalLoss, 2) }}</div>
        </div>
        <div class="metric-box">
            <div class="metric-title">Items Thrown</div>
            <div class="metric-value">{{ $trashCount }}</div>
        </div>
    </div>

    <div class="table-container active" id="loss-table">
        <div class="section-title">List of thrown away items</div>
        <table class="inventory-table">
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
        // Update active tab styling
        document.querySelectorAll('.all-tabs .tab').forEach(tab => {
            tab.classList.remove('active');
        });
        event.target.classList.add('active');

        // Update hidden tab input
        document.getElementById('tabInput').value = tabName;

        // Show/hide content
        if (tabName === 'profit') {
            document.getElementById('profit-content').style.display = 'block';
            document.getElementById('loss-content').style.display = 'none';
        } else {
            document.getElementById('profit-content').style.display = 'none';
            document.getElementById('loss-content').style.display = 'block';
        }
    }

    function changeTimePeriod(period) {
        // Update active time-tab styling
        document.querySelectorAll('.all-tabs .time-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        event.target.classList.add('active');

        // Update hidden period input and submit form
        document.getElementById('periodInput').value = period;
        document.getElementById('timePeriodForm').submit();
    }
</script>
@endsection
