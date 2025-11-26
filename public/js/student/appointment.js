// Use the variables from Blade
let servicesData = window.servicesData || [];
let staffData = window.staffData || [];

let selectedCategoryId = null;
let selectedCategoryName = null; // <-- add this
let selectedServiceId = null;
let selectedStaffId = null;

    //Category Step
    function selectCategory(categoryId) {
        selectedCategoryId = categoryId;
        document.getElementById('finalCategory').value = selectedCategoryId;

        let category = window.categoriesData.find(cat => cat.id == categoryId);

        // Use "title" instead of "name"
        selectedCategoryName = category ? category.title : 'N/A';

        console.log("Selected category:", selectedCategoryName);

        updateProgressBar(1);
        goToServices(categoryId);
    }

    //pang update sa progress bar
    function updateProgressBar(stepNumber) {
        const totalSteps = 5;
        const percentage = (stepNumber / totalSteps) * 100;

        document.getElementById('progressBar').style.width = percentage + '%';
        document.getElementById('currentStep').textContent = stepNumber;
    }

    //step indicator
    function setActiveStep(stepNumber) {
        for (let i = 1; i <= 5; i++) {
            const step = document.querySelector(`#stepIndicator${i} .step-circle`);
            if (i === stepNumber) {
                step.classList.add('bg-yellow-500', 'text-white');
                step.classList.remove('text-gray-400', 'border-gray-400');
            } else {
                step.classList.remove('bg-yellow-500', 'text-white');
                step.classList.add('text-gray-400', 'border-gray-400');
            }
        }
    }

    // Go to the services step
    function goToServices(categoryId) {
        selectedCategoryId = categoryId;
        const finalCategoryInput = document.getElementById('finalCategory');
        if (finalCategoryInput) {
            finalCategoryInput.value = selectedCategoryId;
        }

        const services = servicesData;
        const container = document.getElementById('servicesContainer');

        container.innerHTML = services.length === 0 
            ? `<p class="text-red-600">No services available.</p>`
            : services.map(service => `
                <button 
                    class="w-full text-left p-6 bg-white rounded-xl shadow-md hover:shadow-xl transition duration-300 border border-gray-300" 
                    onclick="goToStaff('${service.id}')">
                    <h3 class="text-xl font-semibold text-gray-800">${service.title}</h3>
                    <p class="text-base text-gray-600 mt-2">${service.excerpt || service.body || 'No description'}</p>
                </button>
            `).join('');

        document.getElementById('step-category').classList.add('hidden');
        document.getElementById('step-service').classList.remove('hidden');
        setActiveStep(2);
        updateProgressBar(2);
    }

    // Go to the staff selection step
    function goToStaff(serviceId) {
        selectedServiceId = serviceId;
        document.getElementById('finalService').value = selectedServiceId;

        const staff = staffData[serviceId] || [];
        const container = document.getElementById('staffContainer');

        container.innerHTML = staff.length === 0
            ? `<p class="text-gray-500">No professionals available for this service.</p>`
            : staff.map(user => `
                <button type="button" 
                    class="w-full text-left p-6 bg-white rounded-xl shadow-md hover:shadow-xl transition duration-300 border border-gray-300 overflow-hidden"
                    onclick="goToAdditionalInfo('${user.id}')">
                    <h3 class="text-xl font-semibold text-gray-800">${user.name ?? 'Unnamed'}</h3>
                    <p class="text-base text-gray-600 mt-2">${user.email ?? 'No email'}</p>
                </button>
            `).join('');

        document.getElementById('step-service').classList.add('hidden');
        document.getElementById('step-staff').classList.remove('hidden');
        setActiveStep(3);
        updateProgressBar(3);
    }


    // Update number of members based on group type
    function updateNumberOfMembers() {
        const groupType = document.getElementById('groupType').value;
        const numMembersInput = document.getElementById('numMembers');
        if (groupType === 'solo') {
            numMembersInput.value = 1;
            numMembersInput.min = 1;
            numMembersInput.max = 1;
        } else if (groupType === 'family') {
            numMembersInput.value = 2;
            numMembersInput.min = 2;
            numMembersInput.max = 5;
        } else if (groupType === 'friend') {
            numMembersInput.value = 2;
            numMembersInput.min = 2;
            numMembersInput.max = 12;
        }
    }

    function goToAdditionalInfo(staffId) {
        if (!staffId) {
            console.error("Staff ID missing");
            Swal.fire({
                icon: 'error',
                title: 'No Staff Selected',
                text: 'Please choose a staff before continuing.',
                confirmButtonColor: '#e3342f'
            });
            return;
        }
        
        selectedStaffId = staffId;
        document.getElementById('finalStaff').value = selectedStaffId;

        document.getElementById('finalCategory').value = selectedCategoryId;
        document.getElementById('finalService').value = selectedServiceId;

        // Make sure these fields are set before moving to the next step
        document.getElementById('groupType').value = document.getElementById('groupType').value;
        document.getElementById('numMembers').value = document.getElementById('numMembers').value;
        document.getElementById('description').value = document.getElementById('description').value;

        document.getElementById('step-staff').classList.add('hidden');
        document.getElementById('step-additional-info').classList.remove('hidden');
        setActiveStep(4);
        updateProgressBar(4);

        updateNumberOfMembers();
        // restrictDateByMonthStaffAndHolidays(staffId);
    }

    function validateAndGoToDateTime() {
        const groupTypeEl = document.getElementById('groupType');
        const numMembersEl = document.getElementById('numMembers');
        const descriptionEl = document.getElementById('description');

        if (!groupTypeEl || !numMembersEl || !descriptionEl) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Form Elements',
                text: 'Please complete the group details section before continuing.',
                confirmButtonColor: '#e3342f'
            });
            return;
        }

        const groupType = groupTypeEl.value;
        const numMembers = numMembersEl.value;
        const description = descriptionEl.value;
        const minMembers = numMembersEl.min;
        const maxMembers = numMembersEl.max;

        console.log("Before DateTime validation:");
        console.log("groupType:", groupType);
        console.log("numMembers:", numMembers);
        console.log("description:", description);

        if ((groupType === 'solo' && numMembers !== "1") ||
            (groupType === 'family' && (numMembers < 2 || numMembers > 5)) ||
            (groupType === 'friend' && (numMembers < 2 || numMembers > 12))) {

            Swal.fire({
                icon: 'error',
                title: 'Invalid Number of Members',
                text: `For the "${groupType}" group, the number of members must be between ${minMembers} and ${maxMembers}.`,
                confirmButtonColor: '#e3342f'
            });

            document.getElementById('submitBtn').disabled = true;
        } else {
            document.getElementById('finalCategory').value = selectedCategoryId;
            document.getElementById('finalService').value = selectedServiceId;
            document.getElementById('finalStaff').value = selectedStaffId;

            document.getElementById('hiddenGroupType').value = groupType;
            document.getElementById('hiddenNumMembers').value = numMembers;
            document.getElementById('hiddenDescription').value = description;

            document.getElementById('submitBtn').disabled = false;
            goToDateTime();
        }
    }

    // Go to date/time selection step
    function goToDateTime() {
        document.getElementById('step-additional-info').classList.add('hidden');
        document.getElementById('step-datetime').classList.remove('hidden');
        setActiveStep(5);
        updateProgressBar(5);

        // Show loader after the DOM transition
        setTimeout(() => {
            restrictDateByMonthStaffAndHolidays(selectedStaffId);
        }, 200);
    }

    // Restrict date input based on staff availability
    function restrictDateInputByStaffAvailability(staffId) {
        fetch(`/student/staff-days?staff_id=${staffId}`)
            .then(response => response.json())
            .then(data => {
                const allowedDays = data.days;
                const allowedIndexes = allowedDays.map(day =>
                    ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'].indexOf(day.toLowerCase())
                );

                const dateInput = document.getElementById('dateSelector');
                dateInput.onchange = () => {
                    const selectedDate = new Date(dateInput.value);
                    const selectedDay = selectedDate.getDay();
                    if (!allowedIndexes.includes(selectedDay)) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'warning',
                            title: 'This staff is unavailable on that day',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        dateInput.value = '';
                        document.getElementById('availableSlots').innerHTML = '<option value="">Invalid day selected</option>';
                    } else {
                        loadAvailableSlots();
                    }
                };
            });
    }

    // Load available slots for the selected date
    function loadAvailableSlots() {
        const staffId = document.getElementById('finalStaff').value;
        const dateInput = document.getElementById('dateSelector');
        const date = dateInput.value;
        const slotSelect = document.getElementById('availableSlots');

        slotSelect.innerHTML = '<option value="">Loading...</option>';

        if (!staffId || !date) {
            slotSelect.innerHTML = '<option value="">Please select a staff and date</option>';
            return;
        }

        fetch(`/student/availability?staff_id=${staffId}&date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (!data || data.length === 0) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'No available slots for this date',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                slotSelect.innerHTML = '<option value="">No available slots</option>';
                return;
            }

            slotSelect.innerHTML = data.map(slot => {
                const slotTime = Date.parse(slot.datetime);
                const isPast = slotTime < new Date();
                const isAvailable = !slot.booked && !isPast;

                return `<option value="${isAvailable ? slot.datetime : ''}" 
                    ${isAvailable ? '' : 'disabled'} 
                    style="${isAvailable ? '' : 'color: #ccc;'}">
                    ${slot.range} 
                    ${slot.booked ? '(Fully Booked)' : isPast ? '(Past)' : ''}</option>`;  
            }).join('');
        })
        .catch(error => {
            console.error('Error fetching available slots:', error);
            slotSelect.innerHTML = '<option value="">Error loading slots</option>';
        });
    }

    async function restrictDateByMonthStaffAndHolidays(staffId) {
        const dateInput = document.getElementById('dateSelector');
        if (!dateInput || !staffId) return;

        // Show the loading overlay while fetching data
        const loader = document.createElement('div');
        loader.className = 'fp-loader-overlay';
        loader.innerHTML = `
            <div class="fp-spinner"></div>
            <p>Loading available dates...</p>
        `;
        document.body.appendChild(loader);

        const today = new Date();
        const year = today.getFullYear();
        const month = today.getMonth();
        const lastDayOfMonth = new Date(year, month + 1, 0);

        try {
            // Fetch data in parallel
            const [holidaysRes, staffDaysRes, bookedRes] = await Promise.all([
                fetch('/student/special-days/json'),
                fetch(`/student/staff-days?staff_id=${staffId}`),
                fetch(`/student/availability-dates?staff_id=${staffId}`)
            ]);

            // Parse JSON
            let holidays = await holidaysRes.json();
            let staffData = await staffDaysRes.json();
            let booked = await bookedRes.json();

            // Normalize data
            holidays = holidays.map(h => (typeof h === 'string' ? h : h.date));
            const staffAllowedDays = Array.isArray(staffData.days)
                ? staffData.days.map(d => d.toLowerCase()) // if it's an array like ["monday", "tuesday"]
                : Object.keys(staffData.days || {}).map(d => d.toLowerCase()); // if it's an object like { monday: {...}, tuesday: {...} }
            const bookedDates = booked.map(b => (typeof b === 'string' ? b : b.date));

            console.log('=== Debug Info ===');
            console.log('Staff allowed days:', staffAllowedDays);
            console.log('Holiday dates:', holidays);
            console.log('Booked dates:', bookedDates);

            // Initialize Flatpickr
            flatpickr(dateInput, {
                dateFormat: "Y-m-d",
                minDate: today,
                maxDate: lastDayOfMonth,
                disableMobile: true,
                disable: [
                    function (date) {
                        const formatted = date.getFullYear() + '-' +
                            String(date.getMonth() + 1).padStart(2, '0') + '-' +
                            String(date.getDate()).padStart(2, '0');
                        const weekdays = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
                        const dayName = weekdays[date.getDay()];

                        // Prevent past dates
                        const today = new Date();
                        today.setHours(0,0,0,0);
                        if (date < today) return true;

                        // Ensure the staff works this day
                        const isStaffWorking = staffAllowedDays.includes(dayName);

                        // Strictly disable holidays and booked dates
                        const isHoliday = holidays.includes(formatted.trim());
                        const isBooked = bookedDates.includes(formatted.trim());

                        if (!isStaffWorking) return true;
                        if (isHoliday) return true;
                        if (isBooked) return true;

                        return false;
                    }
                ],

                onChange: function(selectedDates, dateStr, instance) {
                    if (holidays.includes(dateStr)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Holiday',
                            text: 'This date is a holiday or special day.',
                            confirmButtonColor: '#e3342f'
                        });
                        instance.clear();
                        return;
                    }

                    if (bookedDates.includes(dateStr)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Fully Booked',
                            text: 'This date is already fully booked for this staff.',
                            confirmButtonColor: '#e3342f'
                        });
                        instance.clear();
                        return;
                    }

                    loadAvailableSlots();
                }
            });
            console.log("Flatpickr ready for:", staffAllowedDays);


        } catch (error) {
            console.error('Error initializing Flatpickr:', error);
        } finally {
            // Always remove loader when done
            loader.remove();
        }
    }

    function goBackToCategory() {
        document.getElementById('step-service').classList.add('hidden');
        document.getElementById('step-category').classList.remove('hidden');
        setActiveStep(1);
        updateProgressBar(1);
    }

    function goBackToService() {
        document.getElementById('step-staff').classList.add('hidden');
        document.getElementById('step-service').classList.remove('hidden');
        setActiveStep(2);
        updateProgressBar(2);
    }

    function goBackToStaff() {
        document.getElementById('step-additional-info').classList.add('hidden');
        document.getElementById('step-staff').classList.remove('hidden');
        setActiveStep(3);
        updateProgressBar(3);
    }

    function goBackToAdditionalInfo() {
        document.getElementById('step-datetime').classList.add('hidden');
        document.getElementById('step-additional-info').classList.remove('hidden');
        setActiveStep(4);
        updateProgressBar(4);
    }

    function resetAppointmentForm(e) {
        if (e) e.target.reset();

        selectedCategoryId = null;
        selectedServiceId = null;
        selectedStaffId = null;

        document.getElementById('finalCategory').value = '';
        document.getElementById('finalService').value = '';
        document.getElementById('finalStaff').value = '';
        document.getElementById('hiddenGroupType').value = '';
        document.getElementById('hiddenNumMembers').value = '';
        document.getElementById('hiddenDescription').value = '';
        document.getElementById('hiddenDate').value = '';
        document.getElementById('hiddenTime').value = '';

        document.getElementById('servicesContainer').innerHTML = '';
        document.getElementById('staffContainer').innerHTML = '';
        document.getElementById('availableSlots').innerHTML = '<option value="">Select a date</option>';

        document.getElementById('step-datetime').classList.add('hidden');
        document.getElementById('step-additional-info').classList.add('hidden');
        document.getElementById('step-staff').classList.add('hidden');
        document.getElementById('step-service').classList.add('hidden');
        document.getElementById('step-category').classList.remove('hidden');

        setActiveStep(1);
        updateProgressBar(1);
    }


    document.getElementById('appointmentForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        // Set the hidden input values from visible or selected inputs
        document.getElementById('finalCategory').value = selectedCategoryId;
        document.getElementById('finalService').value = selectedServiceId;
        document.getElementById('finalStaff').value = selectedStaffId;

        const groupType = document.getElementById('groupType')?.value;
        const numMembers = document.getElementById('numMembers')?.value;
        const description = document.getElementById('description')?.value;

        document.getElementById('hiddenGroupType').value = groupType;
        document.getElementById('hiddenNumMembers').value = numMembers;
        document.getElementById('hiddenDescription').value = description;

        const date = document.getElementById('dateSelector').value;
        const time = document.getElementById('availableSlots').value;

        if (!date || !time || !groupType) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Information',
                text: 'Please make sure all required fields are filled out.',
                confirmButtonColor: '#e3342f'
            });
            return;
        }

        document.getElementById('hiddenDate').value = date;
        const onlyTime = new Date(time).toTimeString().slice(0, 5);
        document.getElementById('hiddenTime').value = onlyTime;

        const datetime = `${date} ${time}`;

        try {
            // Check for duplicate booking
            const response = await fetch(`/student/check-duplicate?datetime=${datetime}`);
            const data = await response.json();

            if (data.exists) {
                Swal.fire({
                    icon: 'error',
                    title: 'Duplicate Booking',
                    text: 'You have already booked an appointment for this date and time.',
                    confirmButtonColor: '#e3342f'
                });
                return;
            }

            // Submit the form
            const formData = new FormData(this);
            const submitResponse = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (submitResponse.status === 422) {
                const errorData = await submitResponse.json();
                const errorMessages = errorData.errors
                    ? Object.values(errorData.errors).flat().join('\n')
                    : errorData.error || 'Validation failed.';

                Swal.fire({
                    icon: 'error',
                    title: 'This date is unavailable due to certain reasons:',
                    text: errorMessages,
                    confirmButtonColor: '#e3342f'
                });
            } else if (submitResponse.ok) {
                const result = await submitResponse.json();

                // IDs from hidden inputs
                const categoryId = document.getElementById('finalCategory').value;
                const serviceId = document.getElementById('finalService').value;
                const staffId = document.getElementById('finalStaff').value;

                const groupType = document.getElementById('hiddenGroupType').value;
                const numMembers = document.getElementById('hiddenNumMembers').value;
                const description = document.getElementById('hiddenDescription').value;
                const date = document.getElementById('hiddenDate').value;
                const time = document.getElementById('hiddenTime').value;

                // Look up names
                const svc = window.servicesData?.find(s => s.id == serviceId);

                const categoryName = selectedCategoryName || 'N/A';

                const serviceName = svc?.title || 'N/A';

                // Flatten grouped staff into one array
                const rawStaff = window.staffData || {};
                const staffArray = Array.isArray(rawStaff) ? rawStaff : Object.values(rawStaff).flat();
                const staffObj = staffArray.find(s => s.id == staffId);

                const staffName = staffObj?.user?.name || staffObj?.name || 'Any available counselor';


                // Build details table
                let detailsMessage = `
                    <div style="text-align:left; margin-top:10px;">
                        <table style="width:100%; border-collapse: collapse; font-size:14px;">
                            <tr>
                                <td style="padding:6px; border:1px solid #ddd;"><b>Category</b></td>
                                <td style="padding:6px; border:1px solid #ddd;">${categoryName}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px; border:1px solid #ddd;"><b>Service</b></td>
                                <td style="padding:6px; border:1px solid #ddd;">${serviceName}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px; border:1px solid #ddd;"><b>Staff</b></td>
                                <td style="padding:6px; border:1px solid #ddd;">${staffName}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px; border:1px solid #ddd;"><b>Group Type</b></td>
                                <td style="padding:6px; border:1px solid #ddd;">${groupType || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px; border:1px solid #ddd;"><b>Members</b></td>
                                <td style="padding:6px; border:1px solid #ddd;">${numMembers || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px; border:1px solid #ddd;"><b>Description</b></td>
                                <td style="padding:6px; border:1px solid #ddd;">${description || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px; border:1px solid #ddd;"><b>Date</b></td>
                                <td style="padding:6px; border:1px solid #ddd;">${date}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px; border:1px solid #ddd;"><b>Time</b></td>
                                <td style="padding:6px; border:1px solid #ddd;">${time}</td>
                            </tr>
                        </table>
                    </div>
                `;
                Swal.fire({
                    icon: 'success',
                    title: 'Booking Successful',
                    html: detailsMessage,
                    width: 600,
                    confirmButtonText: 'OK',
                    showDenyButton: true,
                    denyButtonText: 'Send to Email',
                    confirmButtonColor: '#4caf50',
                    denyButtonColor: '#007bff'
                }).then((swalResult) => {
                    if (swalResult.isConfirmed) {
                        if (result.redirect) {
                            window.location.href = result.redirect;
                        } else {
                            resetAppointmentForm(e); // ✅ reset when OK is clicked
                        }
                    } else if (swalResult.isDenied) {
                        fetch(`/appointments/${result.appointment_id}/send-email`, {
                            method: 'POST',
                            headers: { 
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            Swal.fire('Sent!', 'Appointment details have been emailed.', 'success')
                            .then(() => {
                                resetAppointmentForm(e); // ✅ reset after email sent successfully
                            });
                        })
                        .catch(err => {
                            Swal.fire('Error', 'Failed to send email.', 'error');
                        });
                    }
                });
            } else {
                const errorText = await submitResponse.text();
                console.error('Unexpected response:', errorText);

                Swal.fire({
                    icon: 'error',
                    title: 'Booking Failed',
                    text: 'There was an error while submitting the appointment.',
                    confirmButtonColor: '#e3342f'
                });
            }
        } catch (error) {
            console.error('Unexpected error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong. Please try again later.',
                confirmButtonColor: '#e3342f'
            });
        }
    });
