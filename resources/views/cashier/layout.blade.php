<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="container">
        <!-- Sidebar (Navbar) -->
        <div class="sidebar">
            <ul>
                <li><a href="{{ route('dashboard') }}">Home</a></li>
                @if(Auth::user()->privilege === 'manager')
                <li><a href="{{ route('products.index') }}">Inventory</a></li>
                <li><a href="{{ route('reports') }}">Reports</a></li>
                <li><a href="{{ route('users.manage') }}">Manage Users</a></li>
                @endif

                @if(Auth::user()->privilege === 'cashier')
                <li><a href="{{ route('transactions.record') }}">Record Transactions</a></li>
                <li><a href="{{ route('items.thrown') }}">Record Thrown Items</a></li>
                @endif

                <li><a href="{{ route('logout') }}">Logout</a></li>
            </ul>
        </div>
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>
</html>
