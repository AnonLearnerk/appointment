async function confirmCancel(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to cancel this appointment.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, cancel it',
        cancelButtonText: 'No, keep it'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                // ✅ Use route from Blade
                const url = window.cancelUrl.replace(':id', id);

                const response = await fetch(url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        "X-Requested-With": "XMLHttpRequest"   // ✅ tells Laravel it's AJAX
                    },
                    body: JSON.stringify({})
                });

                // ✅ Handle JSON or unexpected redirect
                const contentType = response.headers.get("content-type");
                let data;

                if (contentType && contentType.includes("application/json")) {
                    data = await response.json();
                } else {
                    throw new Error("Unexpected response (maybe session expired or server error).");
                }

                if (!response.ok) throw new Error(data.message || "Request failed");

                Swal.fire('Cancelled!', data.message, 'success');

                // ✅ Update table row inline
                const row = document.querySelector(`#appt-${id}`);
                if (row) {
                    row.querySelector(".status-cell").textContent = "Cancelled";
                    row.querySelector(".status-cell").className = "px-4 py-2 border text-red-600 font-medium";
                    row.querySelector(".actions-cell").innerHTML = '<span class="text-gray-400 text-sm italic">N/A</span>';
                }

            } catch (error) {
                Swal.fire('Error!', error.message || 'Something went wrong. Please try again.', 'error');
                console.error(error);
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const tableWrapper  = document.getElementById("appointmentsTable");
    const searchInput   = document.getElementById("searchInput");
    const serviceFilter = document.getElementById("filter-service");
    const statusFilter  = document.getElementById("filter-status");

    async function applyFilters(url = window.appointmentsUrl) {
        const params = new URLSearchParams();

        if (searchInput && searchInput.value) params.set("search", searchInput.value);
        if (serviceFilter.value) params.set("service", serviceFilter.value);
        if (statusFilter.value) params.set("status", statusFilter.value);

        try {
            const response = await fetch(url + "?" + params.toString(), {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            if (!response.ok) throw new Error("Failed to fetch appointments");

            const html = await response.text();
            tableWrapper.innerHTML = html;

            // Rebind pagination after content reload
            bindPagination();
        } catch (error) {
            console.error(error);
            Swal.fire("Error!", "Could not refresh appointments. Try again.", "error");
        }
    }

    function bindPagination() {
        tableWrapper.querySelectorAll(".pagination a").forEach(link => {
            link.addEventListener("click", e => {
                e.preventDefault();
                applyFilters(link.href.split("?")[0] || window.appointmentsUrl);
            });
        });
    }

    // Trigger filters
    serviceFilter.addEventListener("change", () => applyFilters());
    statusFilter.addEventListener("change", () => applyFilters());
    if (searchInput) {
        searchInput.addEventListener("keypress", e => {
            if (e.key === "Enter") applyFilters();
        });
    }

    // Initial bind
    bindPagination();
});
