@extends('emails.layouts.enterprise')

@section('content')
<span class="badge">Password Reset OTP</span>
<h2 style="margin:0 0 8px;">Your OTP Code</h2>
<p style="margin:0 0 14px;color:#475467;">Use this OTP to reset your password. It expires in 10 minutes.</p>
<div style="font-size:30px;letter-spacing:7px;font-weight:800;color:#0b5fff;background:#edf4ff;border-radius:10px;padding:14px;text-align:center;">
    {{ $otp }}
</div>
<p style="margin:14px 0 0;color:#667085;font-size:13px;">Never share this OTP with anyone.</p>
@endsection

