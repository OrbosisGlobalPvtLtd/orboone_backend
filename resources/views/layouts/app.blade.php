<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
       {{-- <title>{{ config('app.name', 'Laravel') }}</title> --}}
        <!-- Scripts -->
    <!-- <script src="{{ asset('js/app.js') }}" defer></script> -->
     <script src="{{ asset('js/app.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        *{
            box-sizing:border-box;
        }

        html, body{
            margin:0;
            padding:0;
            width:100%;
            min-height:100%;
            font-family:'Inter', sans-serif;
        }

        body{
            overflow-x:hidden;
        }

        a{
            text-decoration:none;
        }

        button{
            font-family:inherit;
        }
    </style>

    @yield('head')
    @stack('styles')
</head>
<body>
    @yield('content')

    @yield('script')
    @stack('scripts')
</body>
</html>