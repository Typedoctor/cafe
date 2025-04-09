<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/manager-dashboard.css') }}">
</head>
<body>
    <div class="sidebar">
        <ul>
            <li><a href="{{ route('dashboard') }}" class="active">Home</a></li>
            <li><a href="{{ route('products.index') }}">Inventory</a></li>
            <li><a href="{{ route('reports.index') }}">Reports</a></li>
            <li><a href="{{ route('manage_users.index') }}">Manage Users</a></li>
            <li><a href="{{ route('logout') }}">Logout</a></li>
        </ul>
    </div>

    <!-- Page ine para ma upod an navbar h page if mag balhin balhin -->
    <div class="content">
        @yield('dashboard')
        @yield('product')
        @yield('reports')
        @yield('manage_users')
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
            links.forEach(l => l.classList.remove("active")); // Remove active class from all
            link.classList.add("active"); // Add active class to clicked link
        });
    });
});
</script>
