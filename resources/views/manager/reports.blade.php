
@extends('manager.layout')

@section('title', 'Manager Reports')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dmg-tabs { display: flex; margin-bottom: 20px; }
        .dmg-tab { padding: 10px 20px; cursor: pointer; border-bottom: 2px solid transparent; }
        .dmg-tab.active { border-bottom: 2px solid #007bff; font-weight: bold; }
        .loss-sub-tabs { display: flex; margin-bottom: 20px; }
        .loss-sub-tab { padding: 10px 20px; cursor: pointer; border-bottom: 2px solid transparent; }
        .loss-sub-tab.active { border-bottom: 2px solid #007bff; font-weight: bold; }
    </style>
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
        <table class="rep-table rep-table-striped" id="transactionsTable">
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
                        <td>No transactions found for this period.</td>
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
            <div class="rep-section-title">List of Damaged Items</div>
            <div class="dmg-tabs">
                <div class="dmg-tab active" data-status="all">All</div>
                <div class="dmg-tab" data-status="Successfully Returned">Successfully Returned</div>
                <div class="dmg-tab" data-status="Marked as Loss">Marked as Loss</div>
            </div>
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
    // Initialize DataTables for Transactions Table
    $('#transactionsTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: true, targets: '_all' }
        ]
    });

    // Initialize DataTables for Trash Table
    $('#trashTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: true, targets: '_all' }
        ]
    });

    // Initialize DataTables for Damaged Products Table
    const damagedTable = $('#damagedProductsTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: true, targets: '_all' }
        ]
    });

    // Tab functionality for damaged products
    document.querySelectorAll('#damaged-table .dmg-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('#damaged-table .dmg-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const status = this.dataset.status;
            damagedTable.column(7).search(status === 'all' ? '' : status).draw();
        });
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
