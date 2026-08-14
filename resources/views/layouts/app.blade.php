<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Data Pelanggan') — Afwo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">
    <header class="bg-white border-b border-gray-200">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2">
            <div class="flex min-w-0 items-center gap-3 sm:gap-8">
                <a href="{{ route('pelanggan.index') }}" class="shrink-0 text-lg font-bold tracking-tight">
                    Afwo<span class="text-violet-600">.</span>
                </a>
                <nav class="flex items-center gap-0.5 overflow-x-auto text-sm sm:gap-1">
                    <a href="{{ route('pelanggan.index') }}"
                       class="whitespace-nowrap rounded-md px-2 py-2 sm:px-3 {{ request()->routeIs('pelanggan.*') ? 'bg-violet-50 text-violet-700 font-medium' : 'text-gray-600 hover:text-gray-900' }}">
                        Data Pelanggan
                    </a>
                    <a href="{{ route('karyawan.index') }}"
                       class="whitespace-nowrap rounded-md px-2 py-2 sm:px-3 {{ request()->routeIs('karyawan.*') ? 'bg-violet-50 text-violet-700 font-medium' : 'text-gray-600 hover:text-gray-900' }}">
                        Karyawan
                    </a>
                    <a href="{{ route('layanan.index') }}"
                       class="whitespace-nowrap rounded-md px-2 py-2 sm:px-3 {{ request()->routeIs('layanan.*') ? 'bg-violet-50 text-violet-700 font-medium' : 'text-gray-600 hover:text-gray-900' }}">
                        Layanan
                    </a>
                </nav>
            </div>
            @if (request()->routeIs('karyawan.*'))
                <a href="{{ route('karyawan.create') }}"
                   class="inline-flex shrink-0 items-center gap-2 rounded-md bg-violet-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700 sm:px-4">
                    <span class="text-lg leading-none">+</span>
                    <span class="hidden sm:inline">Tambah Karyawan</span>
                </a>
            @elseif (request()->routeIs('layanan.*'))
                <a href="{{ route('layanan.create') }}"
                   class="inline-flex shrink-0 items-center gap-2 rounded-md bg-violet-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700 sm:px-4">
                    <span class="text-lg leading-none">+</span>
                    <span class="hidden sm:inline">Tambah Layanan</span>
                </a>
            @else
                <a href="{{ route('pelanggan.create') }}"
                   class="inline-flex shrink-0 items-center gap-2 rounded-md bg-violet-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700 sm:px-4">
                    <span class="text-lg leading-none">+</span>
                    <span class="hidden sm:inline">Tambah Pelanggan</span>
                </a>
            @endif
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-4 text-center text-xs text-gray-400">
            Afwo Website
        </div>
    </footer>
</body>
</html>
