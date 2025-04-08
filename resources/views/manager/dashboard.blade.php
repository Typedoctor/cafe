@extends('manager.layout')

@section('title', 'Manager Dashboard')

@section('content')
<h1>Manager Dashboard</h1>

<div class="dashboard-container">
    <div class="dashboard-box">Total Sales</div>
    <div class="dashboard-box">Products in Stock</div>
    <!-- Graph Box for Sales Analytics -->
<div class="graph-box">
    <h2>Sales Analytics</h2>
    <canvas id="salesChart"></canvas>
</div>
    <div class="dashboard-box">Low Stock Alerts</div>
    <div class="dashboard-box">Recent Transactions</div>
</div>



@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar', // Column chart type
            data: {
                labels: ['shabu', 'juana', 'coke'],
                datasets: [{
                    label: 'Sales (Units)',
                    data: [1200, 900, 700], // Replace with dynamic data
                    backgroundColor: ['#10394f', '#0d2c3a', '#007bff'],
                    borderRadius: 5 // Rounded corners
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 1000, // Limit height for better visibility
                        ticks: {
                            font: {
                                size: 10
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
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    });
</script>

