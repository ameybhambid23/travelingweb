// Admin Dashboard Charts
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboardCharts();
    initializeEventListeners();
});

function initializeDashboardCharts() {
    // Bookings Chart
    const bookingsCtx = document.getElementById('bookingsChart');
    if (bookingsCtx) {
        fetch('api/get_booking_stats.php')
            .then(response => response.json())
            .then(data => {
                new Chart(bookingsCtx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Bookings',
                            data: data.values,
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
    }

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        fetch('api/get_revenue_stats.php')
            .then(response => response.json())
            .then(data => {
                new Chart(revenueCtx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Revenue',
                            data: data.values,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgb(54, 162, 235)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
    }
}

function initializeEventListeners() {
    // Add User Form
    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the user');
            });
        });
    }

    // Add Hotel Form
    const addHotelForm = document.getElementById('addHotelForm');
    if (addHotelForm) {
        addHotelForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the hotel');
            });
        });
    }
}

// Edit User
function editUser(userId) {
    fetch(`api/get_user.php?id=${userId}`)
        .then(response => response.json())
        .then(user => {
            // Populate edit form with user data
            document.getElementById('editUserId').value = user.id;
            document.getElementById('editFirstName').value = user.first_name;
            document.getElementById('editLastName').value = user.last_name;
            document.getElementById('editEmail').value = user.email;
            
            // Show edit modal
            const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching user data');
        });
}

// Edit Hotel
function editHotel(hotelId) {
    fetch(`api/get_hotel.php?id=${hotelId}`)
        .then(response => response.json())
        .then(hotel => {
            // Populate edit form with hotel data
            document.getElementById('editHotelId').value = hotel.id;
            document.getElementById('editHotelName').value = hotel.name;
            document.getElementById('editLocation').value = hotel.location;
            document.getElementById('editPrice').value = hotel.price_per_night;
            document.getElementById('editDescription').value = hotel.description;
            
            // Show edit modal
            const editModal = new bootstrap.Modal(document.getElementById('editHotelModal'));
            editModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching hotel data');
        });
}

// Handle Booking Status Change
function updateBookingStatus(bookingId, status) {
    fetch('api/update_booking_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            booking_id: bookingId,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the booking status');
    });
}

// Export Data
function exportData(type) {
    window.location.href = `api/export.php?type=${type}`;
}

// Generate Report
function generateReport(type, startDate, endDate) {
    fetch('api/generate_report.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            type: type,
            start_date: startDate,
            end_date: endDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Display report data in the UI
            const reportContainer = document.getElementById('reportContainer');
            reportContainer.innerHTML = data.html;
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while generating the report');
    });
}
