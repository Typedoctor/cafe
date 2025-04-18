<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cashier-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cashier-trash.css') }}">
    <script src="https://kit.fontawesome.com/2952e58222.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <!-- Sidebar (Navbar) -->
        <div class="sidebar">
            <ul>   
                <li><a href="{{ route('order.index') }}" ><i class="fa-solid fa-mug-hot"></i>Manage orders</a></li>
                <li><a ><i class="fa-solid fa-receipt"></i><span>Record Transactions</a></li>
                <li><a href="{{ route('trash.index') }}"><i class="fa-solid fa-trash"></i>Record Thrown Items</a></li>
                <li><a href="{{ route('logout') }}"><i class="fa-solid fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
        <div class="content">
            @yield('content')
        </div>
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
