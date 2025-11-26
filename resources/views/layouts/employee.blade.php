<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css">

    <!-- Sweet Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .employee-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background-color: #0f172a;
            color: white;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            padding-top: 1rem;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            border-right: 1px solid #1e293b;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .sidebar-header {
            text-align: center;
            font-weight: bold;
            font-size: 1.25rem;
            margin-bottom: 2rem;
        }

        .sidebar a {
            display: block;
            padding: 0.75rem 1rem;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 0.375rem;
            margin: 0.25rem 0;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .sidebar a:hover {
            background-color: #1e293b;
            color: white;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .navbar-custom {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        main {
            padding: 1.5rem;
            overflow-y: auto;
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: true }">
    <div class="employee-layout">
        <!-- Sidebar -->
        <div class="sidebar"
             x-show="sidebarOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-full">
             
            <div class="sidebar-header flex items-center justify-center space-x-3 px-3">
                <img src="{{ asset('img/logoo.png') }}"
                    class="h-10 w-20 object-contain rounded-full" alt="Employee Logo" />
                <span class="text-xl font-semibold whitespace-nowrap">
                    Councilor Panel
                </span>
            </div>

            <a href="{{ route('employee.dashboard') }}" class="flex items-center gap-2 text-gray-700 hover:text-indigo-600">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l9-9 9 9M4 10v10h5v-6h6v6h5V10" />
                        </svg>
                        <span>Dashboard</span>
                    </span>
                </a>

                <a href="{{ route('employee.profile.edit') }}" class="flex items-center gap-2 text-gray-700 hover:text-indigo-600">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5.121 17.804A9.953 9.953 0 0012 20c2.21 0 4.246-.722 5.879-1.941M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Profile</span>
                    </span>
                </a>

                <a href="{{ route('employee.availability.edit') }}" class="flex items-center gap-2 text-gray-700 hover:text-indigo-600">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm7-5v-3m0 0H9m3 0h3" />
                        </svg>
                        <span>Availability</span>
                    </span>
                </a>

        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation Bar -->
            <div class="navbar-custom flex items-center justify-between">
                <!-- Sidebar Toggle + Title -->
                <div class="flex items-center space-x-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-700 text-2xl focus:outline-none">
                        ☰
                    </button>
                    <span class="font-semibold text-gray-700 text-lg hidden md:inline">Councilor Dashboard</span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                        Log Out
                    </button>
                </form>
            </div>

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @stack('scripts')
    @yield('js')
</body>
</html>
