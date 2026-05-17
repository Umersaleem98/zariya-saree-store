<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
</head>

<body>

    <h2>Login</h2>

    @if (session('error'))
        <p>{{ session('error') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}">

        @csrf

        <input type="email" name="email" placeholder="Email">

        <br><br>

        <input type="password" name="password" placeholder="Password">

        <br><br>

        <button type="submit">
            Login
        </button>

    </form>

    <br>

    <a href="{{ route('register') }}">
        Create Account
    </a>

</body>

</html>
