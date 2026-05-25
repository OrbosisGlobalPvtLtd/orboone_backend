@extends('layouts.panel')

@section('page_title', data_get($dashboard, 'meta.title', 'Custom Admin Dashboard'))

@section('_content')
    @include('dashboard.partials.premium-role-dashboard', ['dashboard' => $dashboard])
@endsection
