<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/manager-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inventory.css') }}">
    <script src="https://kit.fontawesome.com/2952e58222.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="sidebar">
        <ul>
            <li><a href="{{ route('dashboard') }}" class="active"><i class="fa-solid fa-house"></i>Home</a></li>
            <li><a href="{{ route('products.index') }}"><i class="fa-solid fa-book"></i>Inventory</a></li>
            <li><a href="{{ route('reports.index') }}"><i class="fa-solid fa-chart-line"></i>Reports</a></li>
            <li><a href="{{ route('manage_users.index') }}"><i class="fa-solid fa-users"></i>Manage Users</a></li>
            <li><a href="{{ route('transactions.index') }}"><i class="fa-solid fa-receipt"></i><span>Transactions</span></a></li>
            <li><a href="{{ route('logout') }}"><i class="fa-solid fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </div>

    <!-- Page ine para ma upod an navbar h page if mag balhin balhin -->
    <div class="content">
        @yield('content')
        
    </div>
</body>
</html>

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

        // Add click event to update active class dynamically
        link.addEventListener("click", function () {
            links.forEach(l => l.classList.remove("active")); 
            link.classList.add("active"); 
        });
    });
});
</script>
