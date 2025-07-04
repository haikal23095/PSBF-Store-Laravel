<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Toko Online' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-indigo-600">TokoReverb</a>
                <div>
                    @auth
                        <span class="text-gray-800 mr-4">Halo, {{ Auth::user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="font-semibold text-gray-600 hover:text-gray-900">Logout</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main>
        {{-- Konten dari view spesifik akan dimasukkan di sini --}}
        @yield('content')
    </main>
    @vite(['resources/js/app.js'])
    <script>
        // Konfigurasi Echo untuk Reverb
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env('REVERB_APP_KEY') }}',
            wsHost: '{{ env('REVERB_HOST') }}',
            wsPort: {{ env('REVERB_PORT') }},
            wssPort: {{ env('REVERB_PORT') }},
            forceTLS: {{ env('REVERB_SCHEME') === 'https' ? 'true' : 'false' }},
            enabledTransports: ['ws', 'wss'],
        });
    </script>
    @stack('scripts')
</body>
</html>