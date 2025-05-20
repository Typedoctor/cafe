<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title')</title>

    <!--FONT AWESOME-->
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!--GOOGLE FONTS-->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Play&display=swap" rel="stylesheet"> 
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cashier-dashboard.css') }}">
    <script src="https://kit.fontawesome.com/2952e58222.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <!-- Sidebar (Navbar) -->
        <div class="sidebar">
            <ul> 
                <img src="{{ url('images/logo.png') }}" alt="Profile Picture" class="sidebar-logo">
  
                <li><a href="{{ route('order.index') }}" ><i class="fa-solid fa-mug-hot"></i>Manage orders</a></li>
                <li><a href="{{ route('cashier-transactions.index') }}" ><i class="fa-solid fa-receipt"></i><span>Transactions</a></li>
                <li><a href="{{ route('spoilage.index') }}"><i class="fa-solid fa-trash"></i>Record Spoiled Items</a></li>
                <li><a href="{{ route('logout') }}"><i class="fa-solid fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>c
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>

<footer>
    <div class="footer">
        <div class="row">
            <a href="#"><i class="fa fa-facebook"></i></a>
            <a href="#"><i class="fa fa-instagram"></i></a>
            <a href="#"><i class="fa fa-twitter"></i></a>
            <a href="#"><i class="fa fa-reddit"></i></a>
            <a href="#"><i class="fa fa-linkedin"></i></a>
            
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
