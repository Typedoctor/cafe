@extends('manager.layout')

@section('title', 'Manager Dashboard')

@section('content')
<h1 >Manager Dashboard</h1>

<div class="dashboard-container">
    <div class="dashboard-box">Income</div>
    <div class="dashboard-box">Loss</div>
    <div class="dashboard-box">Revenue</div>
    <!-- Graph Box for Sales Analytics -->
<div class="graph-box">
    <h2>Sales Analytics</h2>
    <canvas id="salesChart"></canvas>
</div>
    <div class="dashboard-lowstockbox">Low Stock Alerts</div>
    <div class="dashboard-topselling">Top selling products</div>
</div>



@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar', 
            data: {
                labels: ['shabu', 'juana', 'coke'],
                datasets: [{
                    label: 'Sales (Units)',
                    data: [1200, 900, 700], 
                    backgroundColor: ['#10394f', '#0d2c3a', '#007bff'],
                    borderRadius: 5 
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 1000,
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

