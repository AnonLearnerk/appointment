<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <style>
        .layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background-color: #1e293b;
            color: white;
            flex-shrink: 0;
            padding-top: 1rem;
        }

        .sidebar a {
            display: block;
            padding: 1rem;
            color: white;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #334155;
        }

        .main {
            flex: 1;
            overflow-y: auto;
            background-color: #f1f5f9;
        }

        .navbar {
            background-color: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        main {
            padding: 1.5rem;
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }">
    <div class="layout">
        <!-- Overlay for mobile -->
        <div x-show="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
            x-transition.opacity></div>
        <!-- Sidebar -->
        <div class="sidebar fixed z-40 inset-y-0 left-0 w-64 bg-slate-800 text-white transform transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0 lg:z-auto"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            x-transition>
            <div class="text-center font-bold text-xl mb-6">Student Panel</div>
            <nav class="space-y-2">
                <a href="{{ route('student.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l9-9 9 9M4 10v10h5v-6h6v6h5V10" />
                        </svg>
                        <span>Dashboard</span>
                    </span>
                </a>

                <a href="{{ route('student.profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5.121 17.804A9.953 9.953 0 0012 20c2.21 0 4.246-.722 5.879-1.941M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Profile</span>
                    </span>
                </a>
            </nav>
        </div>

        <!-- Main content -->
        <div class="main w-full">
            <!-- Navbar -->
            <div class="navbar flex justify-between items-center bg-white px-6 py-4 shadow-md rounded-b-xl">
                <div class="flex items-center gap-4">
                    <!-- Burger Menu Button -->
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="text-indigo-600 focus:outline-none lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Book Appointment Link -->
                    <a href="{{ route('student.appointments.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm sm:text-base font-semibold bg-indigo-100 text-black rounded-lg border border-indigo-300 hover:bg-indigo-200 transition-all duration-200">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 4h10a2 2 0 012 2v11a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z" />
                        </svg>
                        <span class="sm:inline">Book</span>
                    </a>

                </div>

                @php
                    use Illuminate\Support\Facades\Storage;
                @endphp

                <!-- User Dropdown -->
                <div class="relative">
                    <!-- Dropdown Toggle -->
                    <button id="user-menu-button" type="button"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 bg-gray-100 hover:bg-gray-200 transition"
                        onclick="document.getElementById('user-dropdown').classList.toggle('hidden')">
                        
                        <!-- User Image / Initial -->
                        @if (Auth::user()->image)
                            <img src="{{ Storage::url(Auth::user()->image) }}"
                                alt="Profile"
                                class="w-8 h-8 rounded-full object-cover border">
                        @else
                            <div class="w-8 h-8 rounded-full bg-indigo-500 text-gray flex items-center justify-center font-semibold border">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif


                        <!-- User Name -->
                        <span class="font-semibold text-gray-700">
                            {{ Auth::user()->name }}
                        </span>

                        <!-- Dropdown Arrow -->
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div id="user-dropdown" 
                        class="hidden absolute right-0 mt-2 bg-white border rounded-lg shadow-lg z-20">
                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-2 w-full px-4 py-2 rounded-md font-semibold text-white bg-red-600 hover:bg-red-500 transition-colors duration-300">
                                <!-- Logout Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                    class="w-5 h-5" 
                                    fill="none" 
                                    viewBox="0 0 24 24" 
                                    stroke="currentColor" 
                                    stroke-width="2">
                                    <path stroke-linecap="round" 
                                        stroke-linejoin="round" 
                                        d="M15 12H3m12 0l-4-4m4 4l-4 4m7-9V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2v-2" />
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
    <script>
        document.addEventListener("click", function (event) {
            const button = document.getElementById("user-menu-button");
            const menu = document.getElementById("user-dropdown");
            if (!button.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add("hidden");
            }
        });
    </script>
</body>
</html>
