
@extends('manager.layout')

@section('title', 'Manager Dashboard')

@section('content')
<h1>Manager Dashboard</h1>


<div class="dashboard-container">
    <a href="/reports?tab=profit" class="box-link">
        <div class="dashboard-box">Income</div> 
    </a>
    <a href="/reports?tab=loss" class="box-link">
        <div class="dashboard-box">Loss from thrown items ₱{{ number_format($totalLoss, 2) }}</div>
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
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topSellingProducts as $product)
                    <tr>
                        <td>{{ $product->product_name }}</td>
                        <td>{{ $product->sales }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </>
    <a href="/products" class="box-link">
        <div class="dashboard-box-product">
            <h4>Low Stock Alerts</h4>
            <table class="table table-bordered">
                <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock Left</th>
                        </tr>
                </thead>
                    <tbody>
                        @foreach($lowStockProducts as $product)
                        <tr>
                            <td>{{ $product->product_name }}</td>
                            <td>{{ $product->quantity }}</td>
                        </tr>
                        @endforeach
                    </tbody>
            </table>
        </div>
    </a>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Shabu', 'Juana', 'Coke'],
            datasets: [{
                label: 'Inventory Status',
                data: [1200, 900, 700],
                backgroundColor: ['#10394f', '#0d2c3a', '#007bff'],
                borderRadius: 5,
                barPercentage: 0.5,
                categoryPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true, // Allow flexible sizing
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1500, // Adjusted max value
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 14
                        }
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
