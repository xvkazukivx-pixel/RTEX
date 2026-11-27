<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — RTEX (Sistem Iuran RT &amp; RW)</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/rtex-logo.png') }}">
    <script>
        (function() {
            var savedTheme = localStorage.getItem('rtex_theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ file_exists(public_path('assets/css/style.css')) ? filemtime(public_path('assets/css/style.css')) : '2.1.0' }}">
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['src/css/app.css', 'src/js/app.js'])
    @endif
    @stack('styles')
</head>
<body class="role-{{ auth()->check() ? auth()->user()->role : 'guest' }}">
<div class="app-shell">
    @include('layouts.sidebar')
    <button type="button" class="sidebar-backdrop" data-sidebar-backdrop aria-label="Tutup menu" hidden></button>

    <main class="main-content">
        <div class="topbar">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="sidebar-toggle" data-sidebar-toggle aria-expanded="false" aria-controls="sidebar-nav">
                    <span aria-hidden="true">☰</span>
                    <span data-sidebar-toggle-label>Menu</span>
                </button>
                <button type="button" class="theme-toggle-btn" id="themeToggleBtn" aria-label="Ganti mode terang/gelap" title="Ganti mode terang/gelap">
                    <span id="themeToggleIcon">🌙</span>
                </button>
            </div>
            <div>
                <h1 class="page-title">@yield('page_title', $pageTitle ?? 'Dashboard')</h1>
                @if(!empty($pageSubtitle))
                    <div class="page-subtitle">{{ $pageSubtitle }}</div>
                @else
                    @hasSection('page_subtitle')
                        <div class="page-subtitle">@yield('page_subtitle')</div>
                    @endif
                @endif
            </div>
            <div class="topbar-date">📅 {{ format_tanggal(now()) }}</div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-4" style="border-radius:10px;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mb-4" style="border-radius:10px;">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-4" style="border-radius:10px;">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}"></script>
@stack('scripts')
</body>
</html>
