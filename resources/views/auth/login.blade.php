<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Online</title>
    <!-- Memuat Tailwind CSS dari CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="flex items-center justify-center min-h-screen">
        <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-xl shadow-lg">
            
            <!-- Header -->
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">Selamat Datang!</h1>
                <p class="mt-2 text-gray-600">Silakan login untuk melanjutkan.</p>
            </div>

            <!-- Form Login -->
            {{-- Mengarahkan form ke rute 'login.submit' dengan metode POST --}}
            <form class="space-y-6" action="{{ route('login.submit') }}" method="POST">
                
                {{-- Token CSRF untuk keamanan, wajib ada di setiap form Laravel --}}
                @csrf

                <!-- Input Email -->
                <div>
                    <label for="email" class="text-sm font-medium text-gray-700">Alamat Email</label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        required 
                        autocomplete="email"
                        {{-- Mengisi kembali email jika terjadi error validasi --}}
                        value="{{ old('email') }}"
                        class="w-full px-3 py-2 mt-1 text-gray-900 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="anda@email.com"
                    >
                    {{-- Menampilkan pesan error validasi untuk field 'email' --}}
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Input Password -->
                <div>
                    <label for="password" class="text-sm font-medium text-gray-700">Password</label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        required 
                        autocomplete="current-password"
                        class="w-full px-3 py-2 mt-1 text-gray-900 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="••••••••"
                    >
                    {{-- Menampilkan pesan error validasi untuk field 'password' --}}
                     @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Tombol Login -->
                <div>
                    <button 
                        type="submit"
                        class="w-full px-4 py-2 font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                    >
                        Login
                    </button>
                </div>
            </form>
            
            <!-- Link ke Halaman Registrasi -->
            <p class="text-sm text-center text-gray-600">
                Belum punya akun?
                <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Daftar di sini
                </a>
            </p>

        </div>
    </div>

</body>
</html>