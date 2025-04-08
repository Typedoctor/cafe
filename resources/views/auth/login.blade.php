<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background-color: #f5f5f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            padding: 0 10%;
        }
        .container {
            display: flex;
            justify-content: center; /* Align elements side by side */
            align-items: center; /* Ensure vertical alignment */
            height: 627px; /* Match the form's height */
        }
        #title{
            position: static;
            top: 0px;
            left: 10px;
        }
        .title-box {
            font-size: 80px;
            font-weight: bold;
            width: 473px; /* Same width as form */
            height: 627px; /* Same height as form */
            display: flex;
            align-items: center; /* Vertically center text */
            justify-content: center; /* Horizontally center text */
            background-color: transparent;
        }

        h1 {
            font-size: 60px;
            margin: 0;
            line-height: 1.2;
            color: #10394f;
        }

        .login-container {
            background: #ffffff;
            width: 440px;
            height: 550px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        label {
            font-size: 16px;
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input {
            width: 374px;
            height: 86px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 0 25px;
            font-size: 16px;
        }

        button {
            position: relative;
            left: 22px;
            width: 374px;
            height: 86px;
            background: #10394f;
            color: white;
            font-size: 22px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }

        [style*="color: red"] {
            font-size: 14px;
            text-align: center;
            margin: -10px 0 20px 0;
        }

        input::placeholder {
            color: #a0a0a0;
        }
    </style>
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