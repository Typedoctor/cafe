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
                <li><a href="{{ route('order.index') }}" >Manage orders</a></li>
                <li><a >Record Transactions</a></li>
                <li><a href="{{ route('trash.index') }}">Record Thrown Items</a></li>
                <li><a href="{{ route('logout') }}">Logout</a></li>
            </ul>
        </div>
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>
</html>
