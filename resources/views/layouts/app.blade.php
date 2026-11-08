<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SuaraNiaga')</title>
    <link rel="stylesheet" href="{{ asset('css/suaraniaga.css') }}">
</head>
<body>
    <a class="skip-link" href="#konten-utama">Lompat ke konten utama</a>

    <header>
        <nav aria-label="Navigasi utama">
            <a href="{{ url('/') }}">SuaraNiaga</a>
            @auth
                @if (auth()->user()->isSeller())
                    <a href="{{ url('/seller/dashboard') }}">Dasbor penjual</a>
                @endif
                <form method="POST" action="{{ url('/logout') }}">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            @else
                <a href="{{ url('/login') }}">Masuk</a>
                <a href="{{ url('/register') }}">Daftar</a>
            @endauth
        </nav>
    </header>

    <main id="konten-utama">
        @yield('content')
    </main>

    <footer>
        <p>SuaraNiaga &mdash; marketplace ramah tunanetra.</p>
    </footer>
</body>
</html>
