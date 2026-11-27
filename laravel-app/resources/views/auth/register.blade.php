@extends('layouts.app')

@section('title', 'Create account')

@section('content')
<form class="form card" method="post" action="{{ route('register.store') }}">@csrf
    <p class="eyebrow">JOIN THE CAMPUS COMMUNITY</p><h2>Create your account</h2>
    @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    <label>Full name</label><input name="full_name" value="{{ old('full_name') }}" autocomplete="name" required>
    <label>Student ID</label><input name="student_id" value="{{ old('student_id') }}" required>
    <label>School email</label><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
    <label>College</label><select name="college_id" required><option value="">Select your college</option>@foreach($colleges as $college)<option value="{{ $college->id }}" @selected((string)old('college_id') === (string)$college->id)>{{ $college->code }} — {{ $college->name }}</option>@endforeach</select>
    <label>Password</label><input type="password" name="password" autocomplete="new-password" minlength="8" required>
    <label>Confirm password</label><input type="password" name="password_confirmation" autocomplete="new-password" required>
    <button class="full" type="submit">Create account</button>
    <p class="muted">Already registered? <a href="{{ route('login') }}">Log in</a></p>
</form>
@endsection
