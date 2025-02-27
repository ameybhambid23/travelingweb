<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Get hotel details
if (!isset($_GET['id'])) {
    header('Location: hotels.php');
    exit();
}

$hotel_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$hotel = $stmt->get_result()->fetch_assoc();

if (!$hotel) {
    header('Location: hotels.php');
    exit();
}

// Process booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = $_POST['guests'];
    $total_price = $_POST['total_price'];
    
    // Check room availability
    $availability_check = $conn->prepare("
        SELECT COUNT(*) as booked 
        FROM bookings 
        WHERE hotel_id = ? 
        AND status = 'confirmed'
        AND (
            (check_in <= ? AND check_out >= ?) 
            OR (check_in <= ? AND check_out >= ?)
            OR (check_in >= ? AND check_out <= ?)
        )
    ");
    
    $availability_check->bind_param("issssss", 
        $hotel_id, $check_in, $check_in, $check_out, $check_out, $check_in, $check_out
    );
    $availability_check->execute();
    $availability_result = $availability_check->get_result()->fetch_assoc();
    
    if ($availability_result['booked'] >= $hotel['rooms_available']) {
        $error = "Sorry, no rooms available for the selected dates.";
    } else {
        // Create booking
        $stmt = $conn->prepare("
            INSERT INTO bookings (user_id, hotel_id, check_in, check_out, guests, total_price, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->bind_param("iissid", 
            $_SESSION['user_id'], $hotel_id, $check_in, $check_out, $guests, $total_price
        );
        
        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;
            header("Location: payment.php?booking_id=" . $booking_id);
            exit();
        } else {
            $error = "An error occurred while processing your booking. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo htmlspecialchars($hotel['name']); ?> - TravelEase</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="row">
            <!-- Booking Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Book Your Stay</h2>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form id="bookingForm" method="POST" action="">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Check-in Date</label>
                                    <input type="date" class="form-control" name="check_in" id="check_in" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Check-out Date</label>
                                    <input type="date" class="form-control" name="check_out" id="check_out" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Number of Guests</label>
                                <select class="form-control" name="guests" id="guests" required>
                                    <?php for($i = 1; $i <= 4; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Special Requests</label>
                                <textarea class="form-control" name="special_requests" rows="3"></textarea>
                            </div>
                            
                            <input type="hidden" name="total_price" id="total_price" value="">
                            
                            <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Booking Summary -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Booking Summary</h3>
                        <div class="hotel-details mb-4">
                            <h4><?php echo htmlspecialchars($hotel['name']); ?></h4>
                            <p class="text-muted"><?php echo htmlspecialchars($hotel['location']); ?></p>
                        </div>
                        
                        <div class="price-details">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Price per night</span>
                                <span>$<?php echo number_format($hotel['price_per_night'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Number of nights</span>
                                <span id="num_nights">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Taxes & fees</span>
                                <span id="taxes">$0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total</strong>
                                <strong id="total">$0.00</strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-body">
                        <h4 class="card-title">Cancellation Policy</h4>
                        <p class="card-text">Free cancellation up to 24 hours before check-in. After that, cancellation will incur a fee equivalent to the first night's stay.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        const pricePerNight = <?php echo $hotel['price_per_night']; ?>;
        const taxRate = 0.12; // 12% tax rate
        
        // Initialize date pickers
        flatpickr("#check_in", {
            minDate: "today",
            onChange: updatePricing
        });
        
        flatpickr("#check_out", {
            minDate: "today",
            onChange: updatePricing
        });
        
        function updatePricing() {
            const checkIn = new Date(document.getElementById('check_in').value);
            const checkOut = new Date(document.getElementById('check_out').value);
            
            if (checkIn && checkOut && checkOut > checkIn) {
                const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
                const subtotal = nights * pricePerNight;
                const taxes = subtotal * taxRate;
                const total = subtotal + taxes;
                
                document.getElementById('num_nights').textContent = nights;
                document.getElementById('taxes').textContent = `$${taxes.toFixed(2)}`;
                document.getElementById('total').textContent = `$${total.toFixed(2)}`;
                document.getElementById('total_price').value = total;
            }
        }
        
        // Update pricing when form changes
        document.getElementById('bookingForm').addEventListener('change', updatePricing);
    </script>
</body>
</html>
