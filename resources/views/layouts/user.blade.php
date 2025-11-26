<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css">

    <!-- Sweet Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .admin-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background-color: #1e293b;
            color: white;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            padding-top: 1rem;
        }

        .sidebar-header {
            text-align: center;
            font-weight: bold;
            font-size: 1.25rem;
            margin-bottom: 2rem;
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
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="sidebar"
             x-show="sidebarOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-full">
             
            <div class="sidebar-header flex items-center justify-center space-x-2 px-4">
    <img src="{{ asset('img/logoo.png') }}" alt="" class="h-10 w-10 object-contain" />
    <span>CTU Guidance Office</span>
</div>

            <a href="{{ route('admin.dashboard') }}">🏠 Dashboard</a>
            <a href="#">📅 All Appointments</a>

            <div x-data="{ openDropdown: null }">
                
            @php
                $isCategoryRoute = request()->routeIs('admin.categories.*');
                $isUserRoute = request()->routeIs('admin.users.*');
                $isServiceRoute = request()->routeIs('admin.services.*');
            @endphp

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation Bar -->
            <div class="navbar-custom">
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-700 text-xl focus:outline-none">
                        ☰
                    </button>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                        🔌 Log Out
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('scripts')
</body>
