<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ $branding['favicon_url'] ?? asset('favicon.ico') }}" type="image/x-icon">
    <title>{{ $branding['company_name'] ?? config('app.name', 'OrboOne HRMS') }}</title>

    @include('partials.theme.branding-vars')

    <!-- Scripts -->
    <!-- <script src="{{ asset('js/app.js') }}" defer></script> -->
     <script src="{{ asset('js/app.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
     <!-- Styles -->
     <link href="{{ asset('css/app.css') }}" rel="stylesheet">
     <link href="{{ asset('css/orbosis-modals.css') }}" rel="stylesheet">
     <link href="{{ asset('css/orbosis-premium.css') }}" rel="stylesheet">

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

    <!-- Global Form Loader -->
    <style>
        .global-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(75, 0, 232, 0.2);
            border-radius: 50%;
            border-top-color: var(--orb-primary);
            animation: global-spin 1s ease-in-out infinite;
        }
        @keyframes global-spin {
            to { transform: rotate(360deg); }
        }
    </style>
    <div id="global-form-loader" style="display: none; position: fixed; inset: 0; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center; flex-direction: column;">
        <div class="global-spinner"></div>
        <div style="margin-top: 15px; font-weight: 800; color: var(--orb-primary); font-size: 15px;">Processing, please wait...</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loader = document.getElementById('global-form-loader');

            // Hook into standard form submissions
            document.addEventListener('submit', function(e) {
                // If the submission event was already cancelled (e.g. by a confirm dialog returning false), do not show loader or disable buttons
                if (e.defaultPrevented) {
                    return;
                }

                const form = e.target;

                // Respect HTML5 form validation before showing loader
                if (form.checkValidity && !form.checkValidity()) {
                    return;
                }

                // Show loader with context-aware text
                if (loader) {
                    const loaderText = loader.querySelector('div:last-child');
                    const hasFiles = Array.from(form.querySelectorAll('input[type="file"]')).some(input => input.files.length > 0);
                    
                    if (loaderText) {
                        loaderText.innerText = hasFiles ? 'Uploading files, please wait...' : 'Processing, please wait...';
                    }
                    loader.style.display = 'flex';
                }

                // Disable submit buttons to prevent double-submit
                const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                setTimeout(() => {
                    submitBtns.forEach(btn => {
                        if (!btn.disabled) {
                            btn.dataset.wasDisabled = 'false';
                            btn.disabled = true;
                            btn.style.opacity = '0.7';
                            btn.style.cursor = 'not-allowed';
                        }
                    });
                }, 10); // Small delay allows the button's name/value to be submitted
            });

            // Handle jQuery AJAX
            if (typeof window.jQuery !== 'undefined') {
                window.jQuery(document).ajaxSend(function(event, jqXHR, ajaxOptions) {
                    if (ajaxOptions.type && ajaxOptions.type.toUpperCase() !== 'GET') {
                        if (loader) loader.style.display = 'flex';
                    }
                });
                window.jQuery(document).ajaxComplete(function(event, jqXHR, ajaxOptions) {
                    if (ajaxOptions.type && ajaxOptions.type.toUpperCase() !== 'GET') {
                        if (loader) loader.style.display = 'none';
                    }
                });
            }

            // Handle Axios AJAX
            if (typeof window.axios !== 'undefined') {
                window.axios.interceptors.request.use(function (config) {
                    if (config.method && config.method.toUpperCase() !== 'GET') {
                        if (loader) loader.style.display = 'flex';
                    }
                    return config;
                });
                window.axios.interceptors.response.use(function (response) {
                    if (response.config && response.config.method && response.config.method.toUpperCase() !== 'GET') {
                        if (loader) loader.style.display = 'none';
                    }
                    return response;
                }, function (error) {
                    if (error.config && error.config.method && error.config.method.toUpperCase() !== 'GET') {
                        if (loader) loader.style.display = 'none';
                    }
                    return Promise.reject(error);
                });
            }
        });

        // Hide loader and re-enable buttons when page is restored from bfcache (Back/Forward cache)
        window.addEventListener('pageshow', function(event) {
            const loader = document.getElementById('global-form-loader');
            if (loader) loader.style.display = 'none';
            
            document.querySelectorAll('form button[type="submit"], form input[type="submit"]').forEach(btn => {
                if (btn.dataset.wasDisabled === 'false') {
                    btn.disabled = false;
                    btn.style.opacity = '';
                    btn.style.cursor = '';
                    delete btn.dataset.wasDisabled;
                }
            });
        });

        // Global DataTables responsive fix: 
        // Move outer scrollable wrappers to wrap only the table itself to prevent double scrollbars
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.jQuery !== 'undefined') {
                window.jQuery(document).on('init.dt', function(e, settings) {
                    var $table = window.jQuery(e.target);
                    var $wrapper = $table.closest('.dataTables_wrapper');
                    if (!$wrapper.length) return;
                    
                    var $parent = $wrapper.parent();
                    
                    if ($parent.hasClass('table-responsive') || 
                        $parent.hasClass('leave-table-responsive') || 
                        $parent.hasClass('ep-table-wrap') || 
                        $parent.hasClass('orb-table-wrap')) {
                        
                        var classes = $parent.attr('class');
                        $wrapper.unwrap();
                        
                        // Prevent wrapping multiple times if table is re-initialized
                        if (!$table.parent().hasClass('table-responsive') && 
                            !$table.parent().hasClass('leave-table-responsive') &&
                            !$table.parent().hasClass('ep-table-wrap') &&
                            !$table.parent().hasClass('orb-table-wrap')) {
                            $table.wrap('<div class="' + classes + ' w-100" style="overflow-x: auto;"></div>');
                        }
                    }
                });
            }
        });
    </script>

    @yield('script')
    @stack('scripts')
</body>
</html>