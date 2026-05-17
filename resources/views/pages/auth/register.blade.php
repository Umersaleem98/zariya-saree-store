<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h2>Register</h2>

<form method="POST" action="{{ route('register') }}">

    @csrf

    <input type="text" name="name" placeholder="Full Name">

    <br><br>

    <input type="email" name="email" placeholder="Email">

    <br><br>

    <input type="password" name="password" placeholder="Password">

    <br><br>

    <input type="password" name="password_confirmation" placeholder="Confirm Password">

    <br><br>

    <button type="submit">
        Register
    </button>

</form>

<br>

<a href="{{ route('login') }}">
    Already have account?
</a>

</body>
</html>