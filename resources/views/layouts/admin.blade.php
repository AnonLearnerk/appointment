<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    @vite(['resources/js/app.js', 'resources/css/app.css'])
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css">

    <!-- Sweet Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/alpine.min.js') }}" defer></script>

    <style>
        .admin-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background-color: #0f172a; /* darker for contrast */
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
<body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }">
    <div class="admin-layout">
        <!-- Overlay for mobile -->
        <div x-show="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
            x-transition.opacity></div>
        <!-- Sidebar -->
        <div class="sidebar fixed z-40 inset-y-0 left-0 w-64 bg-slate-800 text-white transform transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0 lg:z-auto"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            x-transition>
             
            <div class="sidebar-header flex items-center justify-center space-x-3 px-3">
                <img src="{{ asset('img/logoo.png') }}"
                    class="h-10 w-10 object-contain rounded-full" alt="Logo" />
                <span class="text-xl font-semibold line-clamp-1 max-w-[9rem]">
                    Admin Panel
                </span>
            </div>

            <nav class="space-y-2">
                <a href="{{ route('admin.dashboard') }}"class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l9-9 9 9M4 10v10h5v-6h6v6h5V10" />
                        </svg>
                        <span>Dashboard</span>
                    </span>
                </a>


                <a href="{{ route('admin.appointments.index') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 4h10a2 2 0 012 2v11a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z" />
                        </svg>
                        <span>All Appointments</span>
                    </span>
                </a>

                <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h18M9 7h12M9 11h12M9 15h12M3 19h18" />
                        </svg>
                        <span>Reports</span>
                    </span>
                </a>

                <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
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
            
            <div x-data="{ openDropdown: null }">
                    
                @php
                    $isCategoryRoute = request()->routeIs('admin.categories.*');
                    $isUserRoute = request()->routeIs('admin.users.*');
                    $isServiceRoute = request()->routeIs('admin.services.*');
                @endphp

                <div x-data="{ openCategory: {{ $isCategoryRoute ? 'true' : 'false' }} }">
                <!-- Categories -->
                    <a href="#" @click.prevent="openCategory = !openCategory"
                    class="flex items-center justify-between gap-2 text-indigo-700 hover:text-indigo-900 transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                            </svg>
                            <span>Categories</span>
                            <span x-text="openCategory ? '▲' : '▼'"></span>
                        </span>
                    </a>

                    <div x-show="openCategory" x-transition class="submenu">
                        <a href="{{ route('admin.categories.create') }}" class="flex items-center justify-between gap-2 text-indigo-700 hover:text-indigo-900 transition px-4 py-2">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Add Category</span>
                            </span>
                        </a>

                        <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>View Categories</span>
                            </span>     
                        </a>

                        <a href="{{ route('admin.categories.trashed') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" 
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4
                                        a1 1 0 011-1h4a1 1 0 011 1v3m4 0H5"/>
                                </svg>
                                <span>View Trashed Categories</span>
                            </span>     
                        </a>
                    </div>
                </div>


                <!-- Users -->
                <div x-data="{ openUser: {{ $isUserRoute ? 'true' : 'false' }} }">
                    <a href="#" @click.prevent="openUser = !openUser"
                        class="flex items-center justify-between gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">

                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5.121 17.804A9.953 9.953 0 0112 15c2.062 0 3.965.622 5.52 1.677M12 12a4 4 0 100-8 4 4 0 000 8z" />
                            </svg>
                            <span>Users</span>
                            <span x-text="openUser ? '▲' : '▼'"></span>
                        </span>
                    </a>

                    <div x-show="openUser" x-transition class="submenu">
                        <a href="{{ route('admin.users.create') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Add User</span>
                            </span>
                        </a>

                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                            <span class="flex items-center gap-2"> 
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>View Users</span>
                            </span>
                        </a>
                        <a href="{{ route('admin.users.trash') }}" class="flex items-center gap-2 px-4 py-2 text-red-700 hover:text-red-900 transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" 
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4
                                    a1 1 0 011-1h4a1 1 0 011 1v3m4 0H5"/>
                            </svg>
                            <span>View Trashed Users</span>
                        </span>
                    </a>
                    </div>
                </div>
            </div>

             <!-- Services -->
            <div x-data="{ openService: {{ $isServiceRoute ? 'true' : 'false' }} }">
                <a href="#" @click.prevent="openService = !openService" class="flex items-center justify-between gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 2H7a2 2 0 00-2 2v16a2 2 0 002 2h10a2 2 0 002-2V4a2 2 0 00-2-2h-2" />
                        </svg>
                        <span>Services</span>
                        <span x-text="openService ? '▲' : '▼'"></span>
                    </span>
                </a>

                <div x-show="openService" x-transition class="submenu">
                    <a href="{{ route('admin.services.create') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Add Service</span>
                        </span>
                    </a>

                    <a href="{{ route('admin.services.index') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>View Services</span>
                        </span>
                    </a>

                    <a href="{{ route('admin.services.trash') }}" class="flex items-center gap-2 px-4 py-2 text-red-700 hover:text-red-900 transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" 
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4
                                    a1 1 0 011-1h4a1 1 0 011 1v3m4 0H5"/>
                            </svg>
                            <span>View Trashed Services</span>
                        </span>
                    </a>
                </div>                        
            </div>

            <!-- Holidays -->
            <div x-data="{ openHoliday: {{ request()->routeIs('admin.special-days.*') ? 'true' : 'false' }} }">
                <a href="#" @click.prevent="openHoliday = !openHoliday"
                class="flex items-center justify-between gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 4h10a2 2 0 012 2v11a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z" />
                        </svg>
                        <span>Manage Holidays</span>
                        <span x-text="openHoliday ? '▲' : '▼'"></span>
                    </span>
                </a>
                <div x-show="openHoliday" x-transition class="submenu">
                    <a href="{{ route('admin.special-days.create') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Create Holiday</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.special-days.index') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>View Holidays</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.special-days.trash') }}" class="flex items-center gap-2 px-4 py-2 text-indigo-700 hover:text-indigo-900 transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" 
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4
                                    a1 1 0 011-1h4a1 1 0 011 1v3m4 0H5"/>
                            </svg>
                            <span>View Trashed Special Days</span>
                        </span>     
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation Bar -->
            <div class="navbar-custom flex items-center justify-between">
            <!-- Left Side: Sidebar Toggle + Title -->
            <div class="flex items-center space-x-3">
                <!-- Burger Menu Button -->
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="text-indigo-600 focus:outline-none lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                <span class="font-semibold text-gray-700 text-lg hidden md:inline">Admin Dashboard</span>
            </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-blue-600 text-white font-bold shadow px-4 py-2 rounded hover:bg-blue-700 transition">
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

    <!-- <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Firebase SDKs -->
<script type="module">
  // Import from CDN
  import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
  import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

  const firebaseConfig = {
    apiKey: "AIzaSyCg8HRgVgbRZgCP3lW7davk5YKU_hvo6yE",
    authDomain: "appointment-system-b9648.firebaseapp.com",
    databaseURL: "https://appointment-system-b9648-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "appointment-system-b9648",
    storageBucket: "appointment-system-b9648.appspot.com",
    messagingSenderId: "455658576755",
    appId: "1:455658576755:web:dff28be4f858fea7cc4306"
  };

  const app = initializeApp(firebaseConfig);
  const db = getDatabase(app);

  // Example: Fetch appointments
  const appointmentsRef = ref(db, "appointments");
  onValue(appointmentsRef, (snapshot) => {
    console.log(snapshot.val());
  });
</script>


    
    @stack('scripts')
    @yield('js')
</body>