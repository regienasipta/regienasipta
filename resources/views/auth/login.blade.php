@extends('auth.layouts')

@section('content')

<h1>Login</h1>
<a href="{{ route('register') }}">Daftar</a>
<br><br>
<form action="{{ route('authenticate') }}" method="post">
  @csrf 
  <label>Email Addres</label><br>
  <input type="email" id="email" name="email" value="{{ old('email')}}"><br><br>
  <label>Password</label><br>
  <input type="password" id="password" name="password"><br><br>
  <input type="submit" value="login">
</form>

@endsection