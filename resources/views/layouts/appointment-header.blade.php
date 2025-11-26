<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CTU Guidance Appointment')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>[x-cloak] { display: none !important; }</style>

    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased">

    <!-- Header/Navbar -->
    <header class="bg-yellow-500 shadow-md px-4 sm:px-6 py-4" x-data="{ dropdownOpen: false }">
        <div class="flex justify-between items-center">
            <!-- Logo + Title -->
            <div class="flex items-center justify-center sm:justify-start gap-2 text-center sm:text-left">
                <img src="{{ asset('img/logoo.png') }}" alt="CTU Logo" class="h-10 w-10 object-contain">
                <h1 class="text-xl font-bold text-white">CTU Guidance Appointment</h1>
            </div>

            <!-- Dropdown Button (mobile + desktop) -->
            <div class="relative">
                <button
                    @click="dropdownOpen = !dropdownOpen"
                    class="flex items-center gap-2 px-4 py-2 bg-sky-500 hover:bg-sky-400 text-white font-semibold rounded-md shadow-md transition-all duration-300 ease-in-out transform hover:scale-105">
                    <span>{{ explode(' ', Auth::user()->name)[0] }}</span>
                    <svg :class="dropdownOpen ? 'rotate-180' : ''"
                        class="ml-1 w-4 h-4 transition-transform duration-300 ease-in-out"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Unified Dropdown Menu -->
                <div x-show="dropdownOpen"
                    x-cloak
                    @click.away="dropdownOpen = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    class="absolute right-0 mt-2 w-44 sm:w-44 w-36 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                    
                    <!-- Dashboard -->
                    <a href="{{ route('student.dashboard') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-yellow-100 hover:text-yellow-700 transition rounded-t-md">
                        <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l9-9 9 9M4 10v10h5v-6h6v6h5V10" />
                        </svg>
                        Dashboard
                    </a>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-2 w-full px-4 py-2 text-sm font-medium text-gray-700 hover:bg-yellow-100 hover:text-yellow-700 transition rounded-b-md">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 12h-9.5m7.5 3l3-3-3-3m-5-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h5a2 2 0 002-2v-1" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>


    <!-- Page Content -->
    <main class="p-6">
        @yield('content')
    </main>

    @yield('modals')
    @yield('scripts')

    <!-- Flash Messages -->
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#3085d6',
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
                confirmButtonColor: '#d33',
            });
        </script>
    @endif

</body>
</html>
