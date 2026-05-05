@extends('layouts.panel')

@section('page_title', $dashboard['role_title'] ?? 'Employee Dashboard')

@section('_content')
@include('dashboard.partials.styles')
@include('dashboard.partials.employee-dashboard')
@endsection
