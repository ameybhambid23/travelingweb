// Main JavaScript file for TravelEase

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initializeSearchForm();
    loadFeaturedDestinations();
    loadSpecialOffers();
    initializeGoogleMaps();
});

// Search form handling
function initializeSearchForm() {
    const searchForm = document.querySelector('.search-container form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const searchParams = new URLSearchParams(formData);
            window.location.href = `search.php?${searchParams.toString()}`;
        });
    }
}

// Load featured destinations
function loadFeaturedDestinations() {
    const container = document.getElementById('featured-destinations-container');
    if (!container) return;

    // Fetch destinations from API
    fetch('api/get_featured_destinations.php')
        .then(response => response.json())
        .then(destinations => {
            container.innerHTML = destinations.map(destination => `
                <div class="col-md-4 mb-4">
                    <div class="destination-card">
                        <img src="${destination.image}" alt="${destination.name}">
                        <div class="card-body">
                            <h5 class="card-title">${destination.name}</h5>
                            <p class="card-text">${destination.description}</p>
                            <p class="price">From $${destination.price}</p>
                            <a href="destination.php?id=${destination.id}" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => console.error('Error loading destinations:', error));
}

// Load special offers
function loadSpecialOffers() {
    const container = document.getElementById('special-offers-container');
    if (!container) return;

    // Fetch offers from API
    fetch('api/get_special_offers.php')
        .then(response => response.json())
        .then(offers => {
            container.innerHTML = offers.map(offer => `
                <div class="col-md-6 mb-4">
                    <div class="offer-card">
                        <div class="row">
                            <div class="col-md-4">
                                <img src="${offer.image}" alt="${offer.title}" class="img-fluid">
                            </div>
                            <div class="col-md-8">
                                <h5>${offer.title}</h5>
                                <p>${offer.description}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="price-tag">
                                        <span class="original-price">$${offer.original_price}</span>
                                        <span class="offer-price">$${offer.offer_price}</span>
                                    </div>
                                    <a href="offer.php?id=${offer.id}" class="btn btn-primary">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => console.error('Error loading offers:', error));
}

// Initialize Google Maps
function initializeGoogleMaps() {
    // This function will be implemented when we add the Google Maps API key
    // Google Maps initialization code will go here
}

// Handle user authentication
function handleLogin(form) {
    const formData = new FormData(form);
    fetch('api/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php';
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Handle user registration
function handleRegister(form) {
    const formData = new FormData(form);
    fetch('api/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'login.php';
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Add to wishlist functionality
function addToWishlist(hotelId) {
    fetch('api/add_to_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ hotel_id: hotelId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Added to wishlist!');
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Handle booking submission
function submitBooking(form) {
    const formData = new FormData(form);
    fetch('api/create_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `booking-confirmation.php?id=${data.booking_id}`;
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Handle review submission
function submitReview(form) {
    const formData = new FormData(form);
    fetch('api/submit_review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Review submitted successfully!');
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
