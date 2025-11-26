<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Appointment Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <img src="{{ asset('img/logo.png') }}" alt="CTU Logo" style="height: 80px;">
    <h2>Appointment Confirmation – CTU Danao Guidance Service</h2>

    <p>Dear {{ $appointment->user->name }},</p>

    <p>Greetings from the Guidance Office of Cebu Technological University – Danao Campus!</p>

    <p>
        We are pleased to formally confirm your appointment for a guidance service session. Below are the details of your booking:
    </p>

    <h3>📌 Appointment Details</h3>
    <ul>
        <li><strong>Service:</strong> {{ $appointment->service->title }}</li>
        <li><strong>Date:</strong> {{ \Carbon\Carbon::parse($appointment->booking_date)->format('F d, Y') }}</li>
        <li><strong>Time:</strong> {{ \Carbon\Carbon::createFromFormat('H:i:s', $appointment->booking_time)->format('g:i A') }}</li>
        <li><strong>Location:</strong> Guidance Office, Admin Bldg., 2nd Floor, CTU – Danao Campus</li>
    </ul>

    <p>
        If you have any questions or need to make changes to your booking, please do not hesitate to contact us at 
        <a href="mailto:ctuguidanceoffice@gmail.com">ctuguidanceoffice@gmail.com</a> or visit the Guidance Office during office hours.
    </p>

    <p>
        To make your next session even more convenient, you may book another appointment here: 
        <a href="https://your-booking-link.com">https://your-booking-link.com</a>
    </p>

    <p>
        Thank you for taking the initiative to prioritize your well-being and personal development. We look forward to meeting with you!
    </p>

    <p>
        Your satisfaction is important to us. We would greatly appreciate it if you could spare a few moments to share your feedback. 
        Your insights will help us improve and serve you better:
        <a href="https://your-feedback-form-link.com">https://your-feedback-form-link.com</a>
    </p>

    <p>Warm regards, <br>
    CTU – Danao Guidance Office</p>
</body>
</html>

