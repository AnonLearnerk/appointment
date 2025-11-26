<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Guidance Appointment System</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 100px; /* Adjust based on actual header height */
        }



        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Instrument Sans', Arial, sans-serif;
        }

        body {
            color: #000; /* Change default text color */
            margin: 0;
            font-family: 'Instrument Sans', Arial, sans-serif;
        }

        header {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color: rgba(233, 94, 41);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: background-color 0.3s ease;
        }

        .logo {
            height: 60px;
            width: auto;
        }

        nav a {
            margin-left: 15px;
            text-decoration: none;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        nav a:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }

        main#home {
            position: relative;
            background: url("{{ asset('img/background.png') }}") center center / cover no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            overflow: hidden; /* keep blur inside */
        }

        main#home::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: inherit; /* same background image */
            filter: blur(6px); /* adjust blur */
            transform: scale(1.1); /* avoid blur edge cutoff */
            z-index: 0;
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background: rgba(0, 0, 0, 0.5); /* darkens image for readability */
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            padding: 2em;
           
            max-width: 800px;
            animation: fadeIn 1.5s ease-in-out;
        }

        .hero-content h1 {
            font-size: 2.8em;
            font-weight: 700;
            margin-bottom: 0.5em;
        }

        .hero-content p {
            font-size: 1.2em;
            margin: 0.5em 0;
        }

        .hero-content .cta-button {
            margin-top: 1.5em;
            padding: 0.75em 1.5em;
            background-color: #f59e0b; /* Tailwind amber-500 */
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .hero-content .cta-button:hover {
            background-color: #d97706; /* Tailwind amber-600 */
            transform: scale(1.05);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        footer {
            background-color: rgb(144, 145, 148);
            text-align: center;
            padding: 1em;
            font-size: .5em;
        }

        .hero-content {
            text-align: center; /* centers the heading and paragraphs */
        }

        .hero-content {
            text-align: center; /* centers the title */
        }

        .info-item {
            display: flex;        /* makes icon + text stay together */
            align-items: center;  /* aligns vertically */
            justify-content: center; /* centers as a group */
            gap: 8px;             /* space between icon and text */
            margin: 6px 0;        /* spacing between rows */
        }

        .info-item ion-icon {
            font-size: 1.2em; /* adjust size if needed */
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }

            nav a {
                margin-left: 0;
                margin-top: 10px;
                display: inline-block;
            }

            .hero-content {
                padding: 1.5em;
                margin-top: 1em;
                width: 90%;
            }

            .hero-content h1 {
                font-size: 1.5em;
            }
        }
    </style>
</head>

<body>
   <header class="fixed top-0 left-0 w-full bg-amber-600/90 backdrop-blur-sm text-white px-6 py-4 flex flex-wrap items-center justify-between shadow-md z-50">
        <div class="flex items-center space-x-4">
            <img src="{{ asset('img/logoo.png') }}" alt="CTU Logo" class="h-12 w-auto">
            <span class="font-bold text-xl">CTU Guidance</span>
        </div>

        <nav class="flex items-center space-x-4">
            <a href="#home" class="hover:text-yellow-300 transition-colors">Home</a>
            <a href="#services" class="hover:text-yellow-300 transition-colors">Services</a>
            <a href="#counselors" class="hover:text-yellow-300 transition-colors">Counselors</a>

            @auth
                @php
                    $user = Auth::user();
                @endphp

                @if ($user->user_type === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="bg-white text-amber-700 font-semibold px-4 py-2 rounded hover:bg-gray-200 transition-all">Dashboard</a>
                @elseif ($user->user_type === 'client')
                    <a href="{{ route('student.dashboard') }}" class="bg-white text-amber-700 font-semibold px-4 py-2 rounded hover:bg-gray-200 transition-all">Dashboard</a>
                @elseif ($user->user_type === 'employee')
                    <a href="{{ route('employee.dashboard') }}" class="bg-white text-amber-700 font-semibold px-4 py-2 rounded hover:bg-gray-200 transition-all">Dashboard</a>
                @else
                    @php
                        $user = Auth::user();
                        $dashboardRoute = '#'; // default

                        if ($user->user_type === 'admin') {
                            $dashboardRoute = url('/admin/dashboard');
                        } elseif ($user->user_type === 'employee') {
                            $dashboardRoute = url('/employee/dashboard');
                        } elseif ($user->user_type === 'client') {
                            $dashboardRoute = url('/student/dashboard');
                        }
                    @endphp

                    <a href="{{ $dashboardRoute }}" class="bg-white text-amber-700 font-semibold px-4 py-2 rounded hover:bg-gray-200 transition-all">
                        Dashboard
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" 
                class="px-4 py-2 bg-blue-800 text-white font-bold rounded-lg shadow-md 
                        hover:bg-blue-600 hover:text-yellow-400 
                        transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                    Login
                </a>
            @endauth
        </nav>
    </header>
    
    <main id="home" class="hero">
        <div class="hero-content">
            <h1><span>CTU - Danao Campus Guidance Office</span></h1>

            <p class="info-item">
                <ion-icon name="navigate-outline"></ion-icon>
                <span>Admin Bldg., 2nd Floor, CTU - Danao Campus</span>
            </p>
            
            @if($admin)
                <p class="info-item">
                    <ion-icon name="call-outline"></ion-icon>
                    <span>{{ $admin->phone ?? 'No number available' }}</span>
                </p>
                <p class="info-item">
                    <ion-icon name="mail-outline"></ion-icon>
                    <span>{{ $admin->email ?? 'No email available' }}</span>
                </p>
            @else
                <p class="info-item">
                    <ion-icon name="call-outline"></ion-icon>
                    <span>Not Available</span>
                </p>
                <p class="info-item">
                    <ion-icon name="mail-outline"></ion-icon>
                    <span>Not Available</span>
                </p>
            @endif
        </div>
    </main>

    <section id="services" class="py-12 bg-gray-100 text-black">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-10">Our Services</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                @forelse($services as $service)
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="flex justify-center mb-4">
                            @if($service->image)
                                <img src="{{ asset('storage/' . $service->image) }}" 
                                    alt="{{ $service->title }}" 
                                    class="w-32 h-32 mx-auto rounded-full mb-4 object-cover">
                            @else
                                <div class="w-32 h-32 mx-auto rounded-full bg-gray-300 flex items-center justify-center text-3xl font-bold text-gray-700 mb-4">
                                    {{ strtoupper(substr($service->title, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <h3 class="text-xl font-semibold mb-2">{{ $service->title }}</h3>
                        <p class="text-gray-700">{{ $service->body }}</p>
                    </div>
                @empty
                    <p class="col-span-3 text-gray-500">No services available right now.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section id="counselors" class="py-12 bg-gray-100 text-black">
        <div class="max-w-6xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-6 text-center">Meet Our Counselors</h2>
            <div class="grid md:grid-cols-3 gap-8">
                @forelse($employees as $employee)
                    <div class="text-center bg-white p-6 rounded-lg shadow-md">
                        
                        @if(!empty($employee->image))
                            <img src="{{ asset('storage/' . $employee->image) }}" 
                                alt="{{ $employee->name }}" 
                                class="w-32 h-32 mx-auto rounded-full mb-4 object-cover">
                        @else
                            <div class="w-32 h-32 mx-auto rounded-full bg-gray-300 flex items-center justify-center text-3xl font-bold text-gray-700 mb-4">
                                {{ strtoupper(substr($employee->name, 0, 1)) }}
                            </div>
                        @endif

                        <h3 class="text-xl font-semibold">{{ $employee->name }}</h3>
                        <p class="text-gray-600">Counselor</p>
                        <p class="text-gray-700 mt-2 text-sm">{{ $employee->email }}</p>
                    </div>
                @empty
                    <p class="col-span-3 text-gray-500 text-center">No counselors available at the moment.</p>
                @endforelse
            </div>
        </div>
    </section>



    <footer>
        <p> © 2025 CTU Guidance System. All Rights Reserved</p>
    </footer>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.addEventListener('scroll', function () {
            const header = document.querySelector('header');
            if (window.scrollY > 10) {
                header.style.backgroundColor = 'rgba(245, 158, 11, 1)'; // fully opaque
            } else {
                header.style.backgroundColor = 'rgba(245, 158, 11, 0.95)'; // semi-transparent
            }
        });
    </script>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
