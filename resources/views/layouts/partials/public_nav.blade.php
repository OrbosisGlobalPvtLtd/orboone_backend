<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ url('/') }}">
            <span style="color:#4B00E8;">OrboOne</span>
            <span class="text-muted" style="font-size:0.9rem;">HRMS</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar" aria-controls="publicNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="publicNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a href="{{ url('/#announcements') }}" class="nav-link">Announcements</a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/#recruitments') }}" class="nav-link">Recruitments</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-lg-center">
                @guest
                    @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="btn btn-sm px-3" href="{{ route('login') }}"
                               style="background:#4B00E8;color:#fff;border-radius:10px;">
                                Login
                            </a>
                        </li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown"
                           class="nav-link dropdown-toggle fw-semibold"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-haspopup="true"
                           aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdown" style="border-radius:14px;">
                            <a class="dropdown-item" href="{{ route('dashboard') }}">
                                Dashboard
                            </a>

                            <a class="dropdown-item"
                               href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Logout
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>