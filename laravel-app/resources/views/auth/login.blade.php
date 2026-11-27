@extends('layouts.app')

@section('title', 'Log in')

@section('content')
<form class="form card" method="post" action="{{ route('login.store') }}">@csrf
    <p class="eyebrow">WELCOME BACK</p><h2>Sign in to CTUKolek!</h2>
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    <label>Student ID or email</label><input name="login" value="{{ old('login') }}" autocomplete="username" required autofocus>
    <label>Password</label><input type="password" name="password" autocomplete="current-password" required>
    <button class="full" type="submit">Log in</button>
    <p class="muted">New here? <a href="{{ route('register') }}">Create a student account</a></p>
</form>
@endsection
