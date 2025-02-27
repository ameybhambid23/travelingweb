<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get booking details
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$booking_id = $_GET['id'];
$stmt = $conn->prepare("
    SELECT b.*, h.name as hotel_name, h.location, h.image_url, u.email, u.first_name, u.last_name
    FROM bookings b 
    JOIN hotels h ON b.hotel_id = h.id 
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ? AND b.user_id = ?
");

$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header('Location: index.php');
    exit();
}

// Calculate booking duration
$check_in = new DateTime($booking['check_in']);
$check_out = new DateTime($booking['check_out']);
$duration = $check_in->diff($check_out)->days;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - TravelEase</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                    <h2 class="mt-2">Booking Confirmed!</h2>
                    <p class="text-muted">Your booking has been successfully confirmed. A confirmation email has been sent to <?php echo htmlspecialchars($booking['email']); ?></p>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="booking-details">
                            <h3>Booking Details</h3>
                            <table class="table">
                                <tr>
                                    <th>Booking Reference:</th>
                                    <td>#<?php echo str_pad($booking['id'], 8, '0', STR_PAD_LEFT); ?></td>
                                </tr>
                                <tr>
                                    <th>Hotel:</th>
                                    <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td><?php echo htmlspecialchars($booking['location']); ?></td>
                                </tr>
                                <tr>
                                    <th>Check-in:</th>
                                    <td><?php echo date('l, M d, Y', strtotime($booking['check_in'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Check-out:</th>
                                    <td><?php echo date('l, M d, Y', strtotime($booking['check_out'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td><?php echo $duration; ?> night<?php echo $duration > 1 ? 's' : ''; ?></td>
                                </tr>
                                <tr>
                                    <th>Guests:</th>
                                    <td><?php echo $booking['guests']; ?></td>
                                </tr>
                                <tr>
                                    <th>Total Amount:</th>
                                    <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-success">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="guest-details mt-4">
                            <h3>Guest Information</h3>
                            <table class="table">
                                <tr>
                                    <th>Name:</th>
                                    <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($booking['image_url'] ?? 'assets/images/hotel-placeholder.jpg'); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($booking['hotel_name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($booking['hotel_name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($booking['location']); ?></p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h4>What's Next?</h4>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-envelope text-primary"></i>
                                    Check your email for booking confirmation
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-calendar text-primary"></i>
                                    Save the dates in your calendar
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                    Plan your journey to the hotel
                                </li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <a href="booking-pdf.php?id=<?php echo $booking_id; ?>" class="btn btn-primary">
                                <i class="fas fa-download"></i> Download Booking Details
                            </a>
                            <a href="my-bookings.php" class="btn btn-outline-primary">
                                View All Bookings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add to Calendar functionality
        function addToCalendar() {
            const event = {
                title: 'Stay at <?php echo addslashes($booking['hotel_name']); ?>',
                start: '<?php echo $booking['check_in']; ?>',
                end: '<?php echo $booking['check_out']; ?>',
                location: '<?php echo addslashes($booking['location']); ?>'
            };

            // Generate calendar links (Google Calendar, iCal, etc.)
            // Implementation would go here
        }
    </script>
</body>
</html>
