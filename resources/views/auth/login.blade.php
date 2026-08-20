<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Afwo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm px-4">
        <div class="mb-8 text-center">
            <a href="/" class="text-3xl font-bold tracking-tight">
                Afwo<span class="text-violet-600">.</span>
            </a>
            <p class="mt-2 text-sm text-gray-500">Masuk ke akun Anda</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember"
                           class="h-4 w-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
                </div>

                <button type="submit"
                        class="w-full rounded-md bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</body>
</html>
