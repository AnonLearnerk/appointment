<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Appointment Details</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { border: 1px solid #ccc; padding: 15px; border-radius: 8px; }
        .details p { margin: 5px 0; }
        .title { font-size: 18px; font-weight: bold; color: #2563eb; }
    </style>
</head>
<body>
    <div class="header">
        <h2 class="title">Appointment Confirmation – CTU Danao Guidance Service</h2>
    </div>

    <div class="details">
        <p><strong>Booking ID:</strong> {{ $appointment->booking_id }}</p>
        <p><strong>Category:</strong> {{ $appointment->category->title }}</p>
        <p><strong>Service:</strong> {{ $appointment->service->title }}</p>
        <p><strong>Staff:</strong> {{ $appointment->employee->user->name }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($appointment->booking_date)->format('F j, Y') }}</p>
        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($appointment->booking_time)->format('h:i A') }}</p>
        <p><strong>Group Type:</strong> {{ ucfirst($appointment->group_type) }}</p>
        <p><strong>No. of Members:</strong> {{ $appointment->num_members }}</p>
        @if($appointment->description)
            <p><strong>Notes:</strong> {{ $appointment->description }}</p>
        @endif
    </div>
</body>
</html>
