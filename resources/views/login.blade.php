<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
    <title>Login</title>
    </head>
    <body class="antialiased">
       
        <form action="{{ url('/log-login') }}" method="POST" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; gap: 10px" >
            @csrf
            Email: 
            <input type="text" name="email" id="">
            Password:
            <input type="password" name="password" id="">
            <input type="submit" value="submit">
        </form>
    </body>
</html>
