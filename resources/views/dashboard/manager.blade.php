@extends('layouts.panel')

@section('page_title', data_get($dashboard, 'meta.title', 'Manager Dashboard'))

@section('_content')
    @include('dashboard.partials.premium-role-dashboard', ['dashboard' => $dashboard])
@endsection
