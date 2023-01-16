<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
    <title>Login</title>
    </head>
    <body class="antialiased">
       
        <form action="{{ url('/log-logout') }}" method="POST" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; gap: 10px" >
            @csrf
            
            <input type="submit" value="submit">
        </form>
    </body>
</html>
