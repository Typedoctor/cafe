@extends('manager.layout')

@section('title', 'Sales Log')

@section('content')
<head>
    <link rel="stylesheet" href="{{ asset('css/manager-sales.css') }}">
</head>

<div class="sl-header">Sales Log</div>


<!-- All Sales Logs -->
<div class="sl-section">
    <div class="sl-section-title">All Sales Transactions</div>
    <div class="sl-table-scroll-container">
        <table class="sl-table sl-table-striped" id="salesLogTable">
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
                    <tr>
                        <td>{{ $log->order_id ?? 'N/A' }}</td>
                        <td>{{ $log->product_name }}</td>
                        <td>{{ $log->quantity }}</td>
                        <td>₱{{ number_format($log->unit_price, 2) }}</td>
                        <td>₱{{ number_format($log->total_price, 2) }}</td>
                        <td>{{ $log->customer_name ?? 'Unknown' }}</td>
                        <td>{{ $log->order_type ?? 'N/A' }}</td>
                        <td>{{ $log->status ?? 'N/A' }}</td>
                        <td>{{ $log->special_instructions ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d h:i A') }}</td>
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
<!-- Sales Summary -->
<div class="sl-section">
    <div class="sl-section-title">Sales Summary by Product</div>
    <div class="sl-table-scroll-container">
        <table class="sl-table sl-table-striped" id="salesSummaryTable">
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
                        <td>{{ $summary->product_name }}</td>
                        <td>{{ $summary->total_quantity_sold }}</td>
                        <td>₱{{ number_format($summary->total_revenue, 2) }}</td>
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

<!-- Include DataTables CSS and JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTables for Sales Summary
        $('#salesSummaryTable').DataTable({
            searching: false,
            paging: false,
            ordering: true,
            language: {
                emptyTable: "No sales recorded yet."
            }
        });

        // Initialize DataTables for All Sales Logs
        $('#salesLogTable').DataTable({
            searching: true,
            paging: true,
            ordering: true,
            language: {
                emptyTable: "No sales logs found."
            }
        });
    });
</script>
@endsection