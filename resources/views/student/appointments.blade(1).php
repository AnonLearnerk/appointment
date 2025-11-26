@extends('layouts.appointment-header')

@section('title', 'Appointment Form')

<style>
    body { margin: 0; }
    .background-blur {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background-image: url('{{ asset('img/bg.jpg') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        filter: blur(8px);
        z-index: -1;
    }
    .login-box {
        backdrop-filter: blur(8px);
        background-color: rgba(255, 255, 255, 0.85);
    }
    /* 🔄 Flatpickr Loader Styles */
    .fp-loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.35);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        color: #fff;
        font-family: system-ui, sans-serif;
        font-size: 1rem;
    }

    .fp-spinner {
        border: 4px solid #ffffff55;
        border-top: 4px solid #ffffff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin-bottom: 10px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<div class="background-blur"></div>

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8 max-w-4xl mx-auto relative z-10">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-4 sm:p-6 space-y-6">
        
        <div class="bg-gray-100 border border-gray-200 rounded-xl p-4 sm:p-6 shadow-inner text-center">
            <h2 class="text-xl sm:text-3xl font-bold text-gray-700">Book an Appointment</h2>
            <p class="text-sm text-gray-500 mt-1">Follow the steps below to complete your booking</p>
        </div>

        @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            });
        </script>
        @endif

        <div class="hidden sm:flex flex-wrap justify-center items-center gap-2 text-sm text-center mb-6">
            @for ($i = 1; $i <= 5; $i++)
                <div class="flex items-center gap-1" id="stepIndicator{{ $i }}">
                    <div class="step-circle w-8 h-8 rounded-full border-2 flex items-center justify-center font-semibold {{ $i === 1 ? 'text-white bg-yellow-500' : 'text-gray-400 border-gray-500' }}">{{ $i }}</div>
                    <span class="font-semibold text-gray-800 hidden sm:inline">
                        {{ ['Category', 'Service', 'Staff', 'Additional Info', 'Date & Time'][$i - 1] }}
                    </span>
                </div>
                @if ($i < 5)
                    <div class="text-gray-400">—</div>
                @endif
            @endfor
        </div>

        <div class="sm:hidden mb-4">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progressBar" class="bg-yellow-500 h-2 rounded-full transition-all duration-300" style="width: 20%;"></div>
            </div>
            <p class="text-xs text-gray-600 text-center mt-1">Step <span id="currentStep">1</span> of 5</p>
        </div>

        <div class="space-y-6">
            {{-- Step 1: Category --}}
            <div id="step-category">
                <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4 text-center">Select a Category</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse ($categories as $category)
                        <div onclick="selectCategory('{{ $category['id'] }}')"
                             class="p-4 bg-white rounded-xl border hover:shadow-lg cursor-pointer transition border-gray-300">
                            <h4 class="text-base font-semibold text-gray-800">{{ $category['title'] }}</h4>
                            <p class="text-sm text-gray-600 mt-2 break-words">{{ $category['body'] ?? 'No description available.' }}</p>
                        </div>
                    @empty
                        <p class="text-red-600">No categories available at the moment.</p>
                    @endforelse
                </div>
            </div>

            {{-- Step 2: Service --}}
            <div id="step-service" class="hidden">
                <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4 text-center">Choose a Service</h3>
                <div id="servicesContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($services as $service)
                        <div onclick="goToStaff('{{ $service['id'] }}')"
                             class="p-4 bg-white rounded-xl border hover:shadow-lg cursor-pointer transition border-gray-300">
                            <h4 class="text-base font-semibold text-gray-800 truncate">{{ $service['title'] }}</h4>
                            <p class="text-sm text-gray-600 mt-2 break-words line-clamp-4">{{ $service['body'] ?? 'No description available.' }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4">
                    <button onclick="goBackToCategory()" class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">&larr; Back</button>
                </div>
            </div>

            {{-- Step 3: Staff --}}
            <div id="step-staff" class="hidden">
                <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4 text-center">Choose a Counselor</h3>
                <div id="staffContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
                <div class="pt-4">
                    <button onclick="goBackToService()" class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">&larr; Back</button>
                </div>
            </div>

            {{-- Step 4: Additional Info --}}
            <div id="step-additional-info" class="hidden">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4 text-center">Additional Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="groupType" class="block text-sm font-medium text-gray-700 mb-1">Classification</label>
                        <select id="groupType" name="group_type" class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-yellow-500" onchange="updateNumberOfMembers()" required>
                            <option value="solo">Solo</option>
                            <option value="family">Family</option>
                            <option value="friend">Friend</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                    <div>
                        <label for="numMembers" class="block text-sm font-medium text-gray-700 mb-1">Number of Member/s</label>
                        <input type="number" id="numMembers" name="num_members" min="1" max="1" class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-yellow-500" required>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-yellow-500" placeholder="Provide additional information (optional)"></textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between gap-4 pt-4">
                    <button type="button" onclick="goBackToStaff()" class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">&larr; Back</button>
                    <button type="button" onclick="validateAndGoToDateTime()" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Next</button>
                </div>
            </div>

            {{-- Step 5: Date & Time --}}
            <form id="appointmentForm" action="{{ route('student.appointments.store') }}" method="POST">
                @csrf
                <div id="step-datetime" class="hidden">
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4 text-center">Pick a Date & Time</h3>

                    <input type="hidden" name="category_id" id="finalCategory">
                    <input type="hidden" id="finalService" name="service_id">
                    <input type="hidden" id="finalStaff" name="staff_id">
                    <input type="hidden" id="hiddenGroupType" name="group_type">
                    <input type="hidden" id="hiddenNumMembers" name="num_members">
                    <input type="hidden" id="hiddenDescription" name="description">
                    <input type="hidden" id="hiddenDate" name="date">
                    <input type="hidden" id="hiddenTime" name="time">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="dateSelector" class="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
                                <input type="text" id="dateSelector" name="date"
                                class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-yellow-500"
                                placeholder="Select a date"
                                required>
                        </div>
                        <div>
                            <label for="availableSlots" class="block text-sm font-medium text-gray-700 mb-1">Time Slot</label>
                            <select name="datetime" id="availableSlots" required class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-yellow-500">
                                <option value="">Select a date first</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:justify-between gap-4 pt-4">
                        <button type="button" onclick="goBackToAdditionalInfo()" class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">&larr; Back</button>
                        <button type="submit" id="submitBtn" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" disabled>Confirm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- 🔄 Global Flatpickr Loader -->
<div id="flatpickrLoader" class="fp-loader-overlay" style="display:none;">
    <div class="fp-spinner"></div>
    <span>Loading available dates...</span>
</div>
@endsection

@section('scripts')
<script src="{{ asset('flatpickr/flatpickr.js') }}"></script>
<link rel="stylesheet" href="{{ asset('flatpickr/flatpickr.min.css') }}">

<script>
    window.categoriesData = @json($categories ?? []);
    window.servicesData = @json($services ?? []);
    window.staffData = @json($staffGroupedByService ?? []);
</script>
<script src="{{ asset('js/student/appointment.js') }}"></script>
@endsection
