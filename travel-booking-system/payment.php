<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get booking details
if (!isset($_GET['booking_id'])) {
    header('Location: index.php');
    exit();
}

$booking_id = $_GET['booking_id'];
$stmt = $conn->prepare("
    SELECT b.*, h.name as hotel_name, h.location 
    FROM bookings b 
    JOIN hotels h ON b.hotel_id = h.id 
    WHERE b.id = ? AND b.user_id = ?
");

$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header('Location: index.php');
    exit();
}

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, you would integrate with a payment gateway like Stripe here
    // For demonstration, we'll simulate a successful payment
    
    $payment_method = $_POST['payment_method'];
    $card_number = $_POST['card_number'];
    
    // Update booking status
    $update_stmt = $conn->prepare("
        UPDATE bookings 
        SET status = 'confirmed', 
            payment_method = ?,
            payment_date = NOW() 
        WHERE id = ?
    ");
    
    $update_stmt->bind_param("si", $payment_method, $booking_id);
    
    if ($update_stmt->execute()) {
        // Send confirmation email
        $to = $_SESSION['email'];
        $subject = "Booking Confirmation - TravelEase";
        $message = "Thank you for your booking at " . $booking['hotel_name'] . ".\n\n";
        $message .= "Booking Details:\n";
        $message .= "Check-in: " . $booking['check_in'] . "\n";
        $message .= "Check-out: " . $booking['check_out'] . "\n";
        $message .= "Total Amount: $" . number_format($booking['total_price'], 2) . "\n\n";
        $message .= "Your booking is now confirmed. We hope you enjoy your stay!";
        
        mail($to, $subject, $message);
        
        header("Location: booking-confirmation.php?id=" . $booking_id);
        exit();
    } else {
        $error = "An error occurred while processing your payment. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - TravelEase</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="row">
            <!-- Payment Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Payment Details</h2>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form id="paymentForm" method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select class="form-control" name="payment_method" required>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="debit_card">Debit Card</option>
                                    <option value="paypal">PayPal</option>
                                </select>
                            </div>
                            
                            <div id="cardDetails">
                                <div class="mb-3">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" class="form-control" name="card_number" 
                                           pattern="[0-9]{16}" placeholder="1234 5678 9012 3456" required>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="text" class="form-control" name="expiry" 
                                               pattern="(0[1-9]|1[0-2])\/[0-9]{2}" placeholder="MM/YY" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" name="cvv" 
                                               pattern="[0-9]{3,4}" placeholder="123" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Cardholder Name</label>
                                    <input type="text" class="form-control" name="cardholder_name" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Complete Payment</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Order Summary</h3>
                        <div class="booking-details mb-4">
                            <h4><?php echo htmlspecialchars($booking['hotel_name']); ?></h4>
                            <p class="text-muted"><?php echo htmlspecialchars($booking['location']); ?></p>
                            
                            <div class="dates">
                                <p><strong>Check-in:</strong> <?php echo date('M d, Y', strtotime($booking['check_in'])); ?></p>
                                <p><strong>Check-out:</strong> <?php echo date('M d, Y', strtotime($booking['check_out'])); ?></p>
                                <p><strong>Guests:</strong> <?php echo $booking['guests']; ?></p>
                            </div>
                        </div>
                        
                        <div class="price-details">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($booking['total_price'] / 1.12, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Taxes & fees</span>
                                <span>$<?php echo number_format($booking['total_price'] - ($booking['total_price'] / 1.12), 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total</strong>
                                <strong>$<?php echo number_format($booking['total_price'], 2); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-body">
                        <h4 class="card-title">Secure Payment</h4>
                        <p class="card-text">Your payment information is encrypted and secure. We never store your full card details.</p>
                        <div class="payment-icons">
                            <i class="fab fa-cc-visa"></i>
                            <i class="fab fa-cc-mastercard"></i>
                            <i class="fab fa-cc-amex"></i>
                            <i class="fab fa-cc-paypal"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle payment method change
        document.querySelector('select[name="payment_method"]').addEventListener('change', function() {
            const cardDetails = document.getElementById('cardDetails');
            cardDetails.style.display = this.value === 'paypal' ? 'none' : 'block';
            
            // Toggle required attributes
            const inputs = cardDetails.querySelectorAll('input');
            inputs.forEach(input => {
                input.required = this.value !== 'paypal';
            });
        });
        
        // Format card number input
        document.querySelector('input[name="card_number"]').addEventListener('input', function(e) {
            let value = this.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = '';
            
            for(let i = 0; i < value.length; i++) {
                if(i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            
            this.value = formattedValue;
        });
        
        // Format expiry date input
        document.querySelector('input[name="expiry"]').addEventListener('input', function(e) {
            let value = this.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            if(value.length > 2) {
                value = value.substr(0, 2) + '/' + value.substr(2);
            }
            this.value = value;
        });
    </script>
</body>
</html>
