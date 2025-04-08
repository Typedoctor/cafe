<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/manager-dashboard.css') }}">
</head>
<body>
    <div class="sidebar">
        <ul>
            <li><a href="{{ route('dashboard') }}">Home</a></li>
            <li><a href="{{ route('products.index') }}">Inventory</a></li>
            <li><a href="{{ route('reports') }}">Reports</a></li>
            <li><a href="{{ route('users.manage') }}">Manage Users</a></li>
            <li><a href="{{ route('logout') }}">Logout</a></li>
        </ul>
    </div>

    <div class="content">
        @yield('content')
    </div>
</body>
</html>
