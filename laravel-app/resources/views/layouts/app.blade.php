<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shop') | CTUKolek!</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body>
<header class="bar"><div class="wrap bar-in">
    <a class="brand" href="{{ route('home') }}"><span class="brand-mark">CK</span><span>CTU<span class="brand-light">Kolek!</span></span></a>
    <nav class="main-nav">
        <a href="{{ route('home') }}">Shop</a>
        @auth
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a href="{{ route('admin.products.index') }}">Products</a>
                <a href="{{ route('admin.courses.index') }}">Courses</a>
                <a href="{{ route('admin.orders.index') }}">Orders</a>
            @else
                <a href="{{ route('orders.index') }}">My orders</a>
                <a href="{{ route('cart.index') }}">Cart <span class="nav-count">{{ array_sum(session('cart', [])) }}</span></a>
            @endif
        @endauth
    </nav>
    <div class="nav-account">
        @auth
            <span class="nav-user">{{ auth()->user()->full_name }}</span>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="nav-logout" type="submit">Log out</button></form>
        @else
            <a href="{{ route('login') }}">Log in</a>
            <a class="nav-register" href="{{ route('register') }}">Create account</a>
        @endauth
    </div>
</div></header>
<main class="wrap">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->has('stock'))<div class="err">{{ $errors->first('stock') }}</div>@endif
    @yield('content')
</main>
<footer class="site-footer"><div class="wrap footer-inner"><span>CTU<span class="footer-brand">Kolek!</span></span><small>College pride, made to wear.</small><small>© {{ date('Y') }} CTUKolek!</small></div></footer>
</body>
</html>
