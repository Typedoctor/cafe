<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/manager-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manager-inventory.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manager-reports.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manager-damaged.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manager-user.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manager-add-to-shelf.css') }}">
    <link rel="stylesheet" href="{{ asset('css/transaction.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manager-audit-logs.css') }}">
    <script src="https://kit.fontawesome.com/2952e58222.js" crossorigin="anonymous"></script>
    @stack('styles')
</head>
<body>
<div class="sidebar">
    <ul>
        <li><a href="{{ route('manager.dashboard') }}" class="active " ><i class="fa-solid fa-grip"></i>Dashboard</a></li>
        <li><a href="{{ route('products.index') }}"><i class="fa-solid fa-box"></i>Inventory</a></li>
        <li><a href="{{ route('add-to-shelf.index') }}"><i class="fa-solid fa-boxes-stacked"></i>Add to Shelves</a></li>
        <li><a href="{{ route('damaged-products.index') }}"><i class="fa-sharp fa-solid fa-dolly"></i>Damaged Items</a></li>
        <li><a href="{{ route('reports.index') }}"><i class="fa-solid fa-chart-line" ></i>Reports</a></li>
        <li><a href="{{ route('manage_users.index') }}"><i class="fa-solid fa-users"></i>Manage Users</a></li>
        <li><a href="{{ route('transactions.index') }}"><i class="fa-solid fa-receipt" ></i><span>Transactions</span></a></li>
        <li><a href="{{ route('audit.index') }}"><i class="fa-solid fa-history"></i><span>Audit Logs</span></a></li>
        <li><a href="{{ route('logout') }}"><i class="fa-solid fa-sign-out-alt"></i><span>Logout</span></a></li>
    </ul>
</div>

<div class="content">
    @yield('content')
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const links = document.querySelectorAll(".sidebar ul li a");
        const currentPage = window.location.pathname;

        links.forEach(link => {
            if (link.href.includes(currentPage)) {
                link.classList.add("active");
            } else {
                link.classList.remove("active");
            }

            link.addEventListener("click", function () {
                links.forEach(l => l.classList.remove("active")); 
                link.classList.add("active"); 
            });
        });
    });
</script>
@stack('scripts')
</body>
</html>