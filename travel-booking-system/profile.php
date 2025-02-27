<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user's bookings
$bookings_stmt = $conn->prepare("
    SELECT b.*, h.name as hotel_name, h.location, h.image_url 
    FROM bookings b 
    JOIN hotels h ON b.hotel_id = h.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$bookings_stmt->bind_param("i", $_SESSION['user_id']);
$bookings_stmt->execute();
$bookings = $bookings_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    
    // Update password if provided
    $password_update = "";
    $types = "sssi";
    $params = [$first_name, $last_name, $phone, $_SESSION['user_id']];
    
    if (!empty($_POST['new_password'])) {
        if (password_verify($_POST['current_password'], $user['password'])) {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $password_update = ", password = ?";
            $types .= "s";
            $params[] = $new_password;
        } else {
            $error = "Current password is incorrect";
        }
    }
    
    if (!isset($error)) {
        $update_stmt = $conn->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, phone = ? $password_update
            WHERE id = ?
        ");
        $update_stmt->bind_param($types, ...$params);
        
        if ($update_stmt->execute()) {
            $success = "Profile updated successfully";
            // Refresh user data
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "An error occurred while updating your profile";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - TravelEase</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="row">
            <!-- Profile Information -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="profile-avatar">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                            </div>
                            <h3 class="mt-2"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>

                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password">
                                <small class="text-muted">Leave blank to keep current password</small>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary w-100">
                                Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Booking History -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h3>My Bookings</h3>
                        
                        <?php if(empty($bookings)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                                <p>You haven't made any bookings yet.</p>
                                <a href="hotels.php" class="btn btn-primary">Browse Hotels</a>
                            </div>
                        <?php else: ?>
                            <?php foreach($bookings as $booking): ?>
                                <div class="card mb-3">
                                    <div class="row g-0">
                                        <div class="col-md-4">
                                            <img src="<?php echo htmlspecialchars($booking['image_url'] ?? 'assets/images/hotel-placeholder.jpg'); ?>" 
                                                 class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($booking['hotel_name']); ?>">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($booking['hotel_name']); ?></h5>
                                                <p class="card-text"><?php echo htmlspecialchars($booking['location']); ?></p>
                                                
                                                <div class="booking-details">
                                                    <p>
                                                        <strong>Check-in:</strong> <?php echo date('M d, Y', strtotime($booking['check_in'])); ?><br>
                                                        <strong>Check-out:</strong> <?php echo date('M d, Y', strtotime($booking['check_out'])); ?><br>
                                                        <strong>Guests:</strong> <?php echo $booking['guests']; ?><br>
                                                        <strong>Total:</strong> $<?php echo number_format($booking['total_price'], 2); ?>
                                                    </p>
                                                    
                                                    <div class="booking-status">
                                                        <span class="badge bg-<?php 
                                                            echo $booking['status'] === 'confirmed' ? 'success' : 
                                                                ($booking['status'] === 'pending' ? 'warning' : 'danger'); 
                                                        ?>">
                                                            <?php echo ucfirst($booking['status']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-3">
                                                    <a href="booking-details.php?id=<?php echo $booking['id']; ?>" 
                                                       class="btn btn-outline-primary btn-sm">View Details</a>
                                                       
                                                    <?php if(strtotime($booking['check_in']) > time()): ?>
                                                        <a href="cancel-booking.php?id=<?php echo $booking['id']; ?>" 
                                                           class="btn btn-outline-danger btn-sm"
                                                           onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                            Cancel Booking
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(strtotime($booking['check_out']) < time() && $booking['status'] === 'confirmed'): ?>
                                                        <a href="write-review.php?hotel_id=<?php echo $booking['hotel_id']; ?>" 
                                                           class="btn btn-outline-success btn-sm">Write Review</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
