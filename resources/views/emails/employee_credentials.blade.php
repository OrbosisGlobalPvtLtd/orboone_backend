@php
$loginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login');
@endphp

@extends('emails.layouts.enterprise')

@section('content')
<span class="badge">Employee Account</span>
<h2 style="margin:0 0 8px;">Welcome, {{ $name }}</h2>
<p style="margin:0 0 12px;color:#475467;">Your {{ branding_name() }} account has been created.</p>
<table class="meta">
    <tr><td class="label">Email</td><td class="value">{{ $email }}</td></tr>
    <tr><td class="label">Employee Code</td><td class="value">{{ $empid }}</td></tr>
    <tr><td class="label">Temporary Password</td><td class="value">{{ $password }}</td></tr>
    <tr><td class="label">Login URL</td><td class="value">{{ $loginUrl }}</td></tr>
</table>
<div class="btn-wrap">
    <a class="btn" href="{{ $loginUrl }}">Access Account</a>
</div>
<p style="margin:10px 0 0;color:#475467;font-size:13px;">
    Login with the temporary password. You will be asked to change your password after login.
</p>
@endsection
