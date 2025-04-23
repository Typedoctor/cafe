@extends('manager.layout')

@section('title', 'Manager Dashboard')

@section('content')
<h1>Manager Dashboard</h1>

<div class="dashboard-container">
    <a href="/reports?tab=profit" class="box-link">
        <div class="dashboard-box">Income</div> 
    </a>
    <a href="/reports?tab=loss" class="box-link">
        <div class="dashboard-box">Loss from thrown items<div style="color:red;">₱{{ number_format($totalLoss, 2) }}</div> </div>
    </a>
    <a href="/reports?tab=profit" class="box-link">
        <div class="dashboard-box">Revenue</div>
    </a>
    <!-- Graph Box for Sales Analytics -->
    <a href="/reports" class="box-link">
        <div class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="dashboard-salesbox">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </a>
    <a href="/transactions" class="box-link">
        <div class="dashboard-box-product">
            <h4>Top Selling Products</h4>
            <div class="top-selling-scroll-container">
            <table class="top-selling-table">
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
    <a href="/products" class="box-link">
        <div class="dashboard-box-lowstock">
            <h4>Low Stock Alerts</h4>
            <div class="low-stock-scroll-container">
                <table class="low-stock-table">
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
                            <td>{{ $product->quantity }}</td>
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
                    max: {{ $maxY }}, // Dynamic max value
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