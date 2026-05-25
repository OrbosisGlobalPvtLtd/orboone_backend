@extends('layouts.panel')

@section('page_title', data_get($dashboard, 'meta.title', 'Operations Admin Dashboard'))

@section('_content')
    @include('dashboard.partials.premium-role-dashboard', ['dashboard' => $dashboard])
@endsection
