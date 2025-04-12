<!DOCTYPE html>
<html lang="en">
<head>
    
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="title-box">
        <h1>CAFE INVENTORY<br>MANAGEMENT</h1>
    </div>

    <div class="login-container">
        @if ($errors->any())
        <p style="color: red">{{ $errors->first('error') }}</p>
        @endif
        
        <form action="{{ route('login.process') }}" method="POST">
            @csrf
            <label for="username">Username</label>
            <input type="text" name="name" id="username" placeholder="Enter username here" required>
            
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter password here" required>
            
            <button type="submit">LOGIN</button>
        </form>
    </div>
</body>
</html>