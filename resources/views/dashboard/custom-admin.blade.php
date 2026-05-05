@extends('layouts.panel')

@section('page_title', $dashboard['role_title'] ?? 'Custom Admin Dashboard')

@section('_content')
@include('dashboard.partials.styles')
@include('dashboard.partials.role-dashboard')
@endsection
