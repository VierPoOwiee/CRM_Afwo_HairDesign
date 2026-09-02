<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Afwo</title>
    <link rel="preconnect" href="https://api.fontshare.com">
    <link href="https://api.fontshare.com/v2/css?f[]=general-sans@400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-text-primary antialiased min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm px-4">
        <div class="mb-8 text-center">
            <a href="/" class="text-3xl font-bold tracking-tight text-text-primary">
                Afwo<span class="text-accent">.</span>
            </a>
            <p class="mt-2 text-sm text-text-muted">Masuk ke akun Anda</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-card p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-danger/20 bg-danger-bg px-4 py-3 text-sm text-danger">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-text-secondary">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-colors duration-150">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-text-secondary">Password</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-colors duration-150">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember"
                           class="h-4 w-4 rounded border-gray-300 text-accent focus:ring-accent/30">
                    <label for="remember" class="ml-2 text-sm text-text-secondary">Ingat saya</label>
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-dark-hover">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</body>
</html>
