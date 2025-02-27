<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if hotel_id is provided
if (!isset($_GET['hotel_id'])) {
    header('Location: index.php');
    exit();
}

$hotel_id = $_GET['hotel_id'];

// Get hotel details
$stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$hotel = $stmt->get_result()->fetch_assoc();

if (!$hotel) {
    header('Location: index.php');
    exit();
}

// Check if user has already reviewed this hotel
$check_review = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND hotel_id = ?");
$check_review->bind_param("ii", $_SESSION['user_id'], $hotel_id);
$check_review->execute();
$existing_review = $check_review->get_result()->fetch_assoc();

if ($existing_review) {
    header('Location: hotel-details.php?id=' . $hotel_id . '&message=already_reviewed');
    exit();
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    $insert_stmt = $conn->prepare("
        INSERT INTO reviews (user_id, hotel_id, rating, comment) 
        VALUES (?, ?, ?, ?)
    ");
    
    $insert_stmt->bind_param("iiis", $_SESSION['user_id'], $hotel_id, $rating, $comment);
    
    if ($insert_stmt->execute()) {
        // Update hotel average rating
        $update_rating = $conn->prepare("
            UPDATE hotels 
            SET rating = (
                SELECT AVG(rating) 
                FROM reviews 
                WHERE hotel_id = ?
            )
            WHERE id = ?
        ");
        $update_rating->bind_param("ii", $hotel_id, $hotel_id);
        $update_rating->execute();
        
        header('Location: hotel-details.php?id=' . $hotel_id . '&message=review_submitted');
        exit();
    } else {
        $error = "An error occurred while submitting your review";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Review - TravelEase</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Write a Review</h2>
                        
                        <div class="hotel-info text-center mb-4">
                            <h4><?php echo htmlspecialchars($hotel['name']); ?></h4>
                            <p class="text-muted"><?php echo htmlspecialchars($hotel['location']); ?></p>
                        </div>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-4 text-center">
                                <label class="form-label">Your Rating</label>
                                <div class="rating">
                                    <input type="radio" name="rating" value="5" id="star5" required>
                                    <label for="star5"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="4" id="star4">
                                    <label for="star4"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="3" id="star3">
                                    <label for="star3"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="2" id="star2">
                                    <label for="star2"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="1" id="star1">
                                    <label for="star1"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Your Review</label>
                                <textarea class="form-control" name="comment" rows="5" required
                                          placeholder="Share your experience at this hotel..."></textarea>
                            </div>
                            
                            <div class="review-guidelines mb-3">
                                <h5>Review Guidelines:</h5>
                                <ul class="text-muted">
                                    <li>Be specific and honest about your experience</li>
                                    <li>Keep it family-friendly and respectful</li>
                                    <li>Avoid mentioning personal information</li>
                                    <li>Focus on your stay experience</li>
                                </ul>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                                <a href="hotel-details.php?id=<?php echo $hotel_id; ?>" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Star rating functionality
        document.querySelectorAll('.rating input').forEach(input => {
            input.addEventListener('change', function() {
                const rating = this.value;
                const stars = document.querySelectorAll('.rating label i');
                
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('text-warning');
                    } else {
                        star.classList.remove('text-warning');
                    }
                });
            });
        });
    </script>
</body>
</html>
