<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Currency Converter')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Alpine --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-primary-600">💱</span>
                    <span class="ml-2 text-xl font-semibold text-gray-900">Currency Converter</span>
                </div>
                <div class="flex items-center space-x-4">
                    {{-- <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary-600 transition">Home</a> --}}
                    {{-- <a href="{{ route('history') }}" class="text-gray-700 hover:text-primary-600 transition">History</a> --}}
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-6 text-center text-gray-600 text-sm">
        <p>
            Built with Laravel 12 & Tailwind CSS • Powered by ExchangeRate-API
        </p>
        <p class="mt-2">
            &copy; 2026 Muhammad Anas. All Rights Reserved.
        </p>
    </footer>


    <!-- Toast Notification -->
    <div id="toast" class="hidden fixed bottom-4 right-4 shadow-xl rounded-lg p-4 max-w-sm z-50 transform transition-all duration-300">
        <div class="flex items-center">
            <svg id="toast-icon" class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <p id="toast-message" class="font-medium"></p>
        </div>
    </div>

    @stack('scripts')
</body>
</html>