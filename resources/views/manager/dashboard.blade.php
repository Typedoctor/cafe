@extends('manager.layout')

@section('title', 'Manager Dashboard')

@section('content')
<h1>Manager Dashboard</h1>

<form class="dash-filter-content" id="filterForm" method="GET" action="{{ route('manager.dashboard') }}">
    <select class="dash-period-filter" name="period" onchange="submitForm()">
        <option value="daily" {{ old('period', session('filter_period', request('period', 'daily'))) == 'daily' ? 'selected' : '' }}>Daily</option>
        <option value="monthly" {{ old('period', session('filter_period', request('period', 'daily'))) == 'monthly' ? 'selected' : '' }}>Monthly</option>
        <option value="yearly" {{ old('period', session('filter_period', request('period', 'daily'))) == 'yearly' ? 'selected' : '' }}>Yearly</option>
    </select>
    <select class="dash-month-filter" name="month" onchange="submitForm()">
        <option value="all" {{ old('month', session('filter_month', request('month', 'all'))) == 'all' ? 'selected' : '' }}>All Months</option>
        @for ($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ old('month', session('filter_month', request('month', 'all'))) == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
            </option>
        @endfor
    </select>
    <select class="dash-year-filter" name="year" onchange="submitForm()">
        <option value="all" {{ old('year', session('filter_year', request('year', 'all'))) == 'all' ? 'selected' : '' }}>All Years</option>
        @for ($y = now()->year; $y >= now()->year - 5; $y--)
            <option value="{{ $y }}" {{ old('year', session('filter_year', request('year', 'all'))) == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
    </select>
    <button type="button" class="dash-reset-filter" onclick="resetFilters()">Reset Filters</button>
</form>

<div class="dash-container">
    <a href="/reports?tab=profit" class="dash-box-link">
        <div class="dash-box">Revenue <div class="dash-content-box-revenue">₱{{ number_format($revenue, 2) }}</div></div> 
    </a>
    <a href="/reports?tab=loss" class="dash-box-link">
        <div class="dash-box">Loss from thrown items<div class="dash-content-box-loss">₱{{ number_format($totalLoss, 2) }}</div> </div>
    </a>
    <a href="/reports?tab=profit" class="dash-box-link">
        <div class="dash-box">Profit<div class="dash-content-box-profit">₱{{ number_format($revenue-$totalLoss, 2) }}</div></div>
    </a>
    <a href="/reports" class="dash-box-link">
        <div class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="dash-salesbox">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </a>
    <a href="/transactions" class="dash-box-link">
        <div class="dash-box-product">
            <h4>Top Selling Products</h4>
            <div class="dash-top-selling-scroll-container">
            <table class="dash-top-selling-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topSellingProducts as $item)
                        <tr>
                            <td>{{ $item->product->product_name ?? 'Unknown Product' }}</td>
                            <td>{{ $item->total_quantity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">No top-selling products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </a>
    <a href="/products" class="dash-box-link">
        <div class="dash-box-lowstock">
            <h4>Low Stock Alerts</h4>
            <div class="dash-low-stock-scroll-container">
                <table class="dash-low-stock-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock Left</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lowStockProducts as $product)
                            <tr>
                                <td>{{ $product->product_name }}</td>
                                <td style="color:red;">{{ $product->quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">No low stock.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </a>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function submitForm() {
    document.getElementById('filterForm').submit();
}
function resetFilters() {
    document.querySelector('.dash-period-filter').value = 'daily';
    document.querySelector('.dash-month-filter').value = 'all';
    document.querySelector('.dash-year-filter').value = 'all';
    const form = document.getElementById('filterForm');
    const resetInput = document.createElement('input');
    resetInput.type = 'hidden';
    resetInput.name = 'reset';
    resetInput.value = 'true';
    form.appendChild(resetInput);
    form.submit();
}

document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesData = @json($salesData);
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.labels,
            datasets: salesData.datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: {{ $maxY }},
                    ticks: {
                        font: {
                            size: 12
                        }
                    },
                    title: {
                        display: true,
                        text: 'Units Sold'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 14
                        }
                    },
                    title: {
                        display: true,
                        text: 'Time Period'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
});
</script>