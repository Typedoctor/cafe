<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!--FONT AWESOME-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!--GOOGLE FONTS-->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Play&display=swap" rel="stylesheet"> 
    <!--CSS-->
    <link rel="stylesheet" href="{{ asset('css/manager-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manager-sales.css') }}">
     <link rel="stylesheet" href="{{ asset('css/datatable.css') }}">
    <script src="https://kit.fontawesome.com/2952e58222.js" crossorigin="anonymous"></script>
    @stack('styles')
</head>
<body>
<div class="sidebar">
    <ul>
        <li>
        <img src="{{ url('images/logo.png') }}" alt="Profile Picture" class="sidebar-logo">


           
        </li>
        <li><a href="{{ route('manager.dashboard') }}" class="active " ><i class="fa-solid fa-grip"></i>Dashboard</a></li>
        <li><a href="{{ route('products.index') }}"><i class="fa-solid fa-box"></i>Inventory</a></li>
        <li><a href="{{ route('add-to-shelf.index') }}"><i class="fa-solid fa-boxes-stacked"></i>Add to Shelf</a></li>
        <li><a href="{{ route('damaged-products.index') }}"><i class="fa-sharp fa-solid fa-dolly"></i>Damaged Items</a></li>
        <li><a href="{{ route('reports.index') }}"><i class="fa-solid fa-chart-line" ></i>Reports</a></li>
        <li><a href="{{ route('manage_users.index') }}"><i class="fa-solid fa-users"></i>Manage Users</a></li>
        <li><a href="{{ route('transactions.index') }}"><i class="fa-solid fa-receipt" ></i><span>Transactions</span></a></li>
        <li><a href="{{ route('sales.index') }}"><i class="fa-solid fa-chart-simple"></i><span>Sales</span></a></li>
        <li><a href="{{ route('audit.index') }}"><i class="fa-solid fa-history"></i><span>Audit Logs</span></a></li>
        <li><a href="{{ route('logout') }}"><i class="fa-solid fa-sign-out-alt"></i><span>Logout</span></a></li>
    </ul>
</div>

<div class="content">
    @yield('content')
</div>

<footer>
    <div class="footer">
        <div class="row">
            <a href="#"><i class="fa fa-facebook"></i></a>
            <a href="#"><i class="fa fa-instagram"></i></a>
            <a href="#"><i class="fa fa-twitter"></i></a>
            <a href="#"><i class="fa fa-reddit"></i></a>
            <a href="#"><i class="fa fa-linkedin"></i></a>
            <a href="#"><i class="fa fa-youtube"></i></a>
        </div>

        <div class="row">
            <ul>
                <li><a href="#">Contact us</a></li>
                <li><a href="#">Our Services</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms & Conditions</a></li>
                <li><a href="#">Support</a></li>
            </ul>
        </div>

        <div class="row">
             CafeCost Copyright © 2025 CafeCost. All rights reserved. 
        </div>
    </div>
</footer>

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