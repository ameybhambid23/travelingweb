<?php
session_start();
require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header('Location: hotels.php');
    exit();
}

$hotel_id = $_GET['id'];

// Get hotel details
$stmt = $conn->prepare("
    SELECT h.*, 
           COUNT(DISTINCT r.id) as review_count,
           AVG(r.rating) as average_rating
    FROM hotels h
    LEFT JOIN reviews r ON h.id = r.hotel_id
    WHERE h.id = ?
    GROUP BY h.id
");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$hotel = $stmt->get_result()->fetch_assoc();

if (!$hotel) {
    header('Location: hotels.php');
    exit();
}

// Get hotel reviews with user information
$reviews_stmt = $conn->prepare("
    SELECT r.*, u.first_name, u.last_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.hotel_id = ?
    ORDER BY r.created_at DESC
");
$reviews_stmt->bind_param("i", $hotel_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate rating distribution
$rating_distribution = array_fill(1, 5, 0);
foreach ($reviews as $review) {
    $rating_distribution[$review['rating']]++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel['name']); ?> - TravelEase</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/reviews.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <!-- Hotel Details -->
        <div class="row mb-5">
            <div class="col-md-8">
                <div id="hotelCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="<?php echo htmlspecialchars($hotel['image_url'] ?? 'assets/images/hotel-placeholder.jpg'); ?>" 
                                 class="d-block w-100" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                        </div>
                        <!-- Add more carousel items for additional hotel images -->
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#hotelCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#hotelCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>

                <h1><?php echo htmlspecialchars($hotel['name']); ?></h1>
                <p class="text-muted">
                    <i class="fas fa-map-marker-alt"></i> 
                    <?php echo htmlspecialchars($hotel['location']); ?>
                </p>

                <div class="hotel-rating mb-4">
                    <div class="d-flex align-items-center">
                        <div class="rating-stars">
                            <?php
                            $rating = round($hotel['average_rating'], 1);
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="fas fa-star text-warning"></i>';
                                } elseif ($i - 0.5 <= $rating) {
                                    echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                } else {
                                    echo '<i class="far fa-star text-warning"></i>';
                                }
                            }
                            ?>
                        </div>
                        <span class="ms-2"><?php echo number_format($rating, 1); ?> out of 5</span>
                        <span class="ms-2 text-muted">(<?php echo $hotel['review_count']; ?> reviews)</span>
                    </div>
                </div>

                <div class="hotel-description mb-4">
                    <h3>About This Hotel</h3>
                    <p><?php echo nl2br(htmlspecialchars($hotel['description'])); ?></p>
                </div>

                <div class="hotel-amenities mb-4">
                    <h3>Amenities</h3>
                    <div class="row">
                        <?php
                        $amenities = json_decode($hotel['amenities'], true) ?? [];
                        foreach ($amenities as $amenity): ?>
                            <div class="col-md-4 mb-2">
                                <i class="fas fa-check text-success"></i> <?php echo htmlspecialchars($amenity); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card booking-card sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h3 class="card-title">Price Details</h3>
                        <div class="price-per-night mb-3">
                            <h2>$<?php echo number_format($hotel['price_per_night'], 2); ?></h2>
                            <span class="text-muted">per night</span>
                        </div>

                        <form action="hotel-booking.php" method="GET">
                            <input type="hidden" name="id" value="<?php echo $hotel_id; ?>">
                            <div class="mb-3">
                                <label class="form-label">Check-in</label>
                                <input type="date" class="form-control" name="check_in" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Check-out</label>
                                <input type="date" class="form-control" name="check_out" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Guests</label>
                                <select class="form-control" name="guests" required>
                                    <?php for($i = 1; $i <= 4; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Book Now</button>
                        </form>

                        <hr>

                        <div class="hotel-policies mt-3">
                            <h5>Hotel Policies</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-clock"></i> Check-in: 2:00 PM</li>
                                <li><i class="fas fa-clock"></i> Check-out: 11:00 AM</li>
                                <li><i class="fas fa-ban-smoking"></i> Non-smoking rooms</li>
                                <li><i class="fas fa-paw"></i> Pet-friendly</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="row">
            <div class="col-md-4">
                <div class="review-summary">
                    <div class="overall-rating">
                        <div class="rating-number"><?php echo number_format($hotel['average_rating'], 1); ?></div>
                        <div class="rating-stars">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="fas fa-star text-warning"></i>';
                                } elseif ($i - 0.5 <= $rating) {
                                    echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                } else {
                                    echo '<i class="far fa-star text-warning"></i>';
                                }
                            }
                            ?>
                        </div>
                        <div class="rating-count"><?php echo $hotel['review_count']; ?> reviews</div>
                    </div>

                    <div class="rating-bars">
                        <?php for ($i = 5; $i >= 1; $i--): 
                            $percentage = $hotel['review_count'] > 0 ? 
                                ($rating_distribution[$i] / $hotel['review_count']) * 100 : 0;
                        ?>
                        <div class="rating-bar">
                            <span class="rating-label"><?php echo $i; ?> stars</span>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <span class="rating-count"><?php echo $rating_distribution[$i]; ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="text-center">
                        <a href="write-review.php?hotel_id=<?php echo $hotel_id; ?>" class="btn btn-primary">
                            Write a Review
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-8">
                <div class="review-filters">
                    <button class="filter-button active" data-rating="all">All</button>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <button class="filter-button" data-rating="<?php echo $i; ?>">
                            <?php echo $i; ?> Stars (<?php echo $rating_distribution[$i]; ?>)
                        </button>
                    <?php endfor; ?>
                </div>

                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card" data-rating="<?php echo $review['rating']; ?>">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></strong>
                                        <div class="review-date">
                                            <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $review['rating'] ? 
                                            '<i class="fas fa-star"></i>' : 
                                            '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="review-content">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </div>
                            <div class="review-helpful">
                                <div class="helpful-buttons">
                                    <button class="helpful-button" onclick="markHelpful(<?php echo $review['id']; ?>)">
                                        <i class="fas fa-thumbs-up"></i> Helpful
                                        <span class="helpful-count">0</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter reviews
        document.querySelectorAll('.filter-button').forEach(button => {
            button.addEventListener('click', function() {
                const rating = this.dataset.rating;
                
                // Update active button
                document.querySelectorAll('.filter-button').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
                
                // Filter reviews
                document.querySelectorAll('.review-card').forEach(card => {
                    if (rating === 'all' || card.dataset.rating === rating) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Mark review as helpful
        function markHelpful(reviewId) {
            fetch('api/mark_helpful.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    review_id: reviewId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const button = event.target.closest('.helpful-button');
                    button.classList.add('active');
                    button.querySelector('.helpful-count').textContent = data.helpful_count;
                }
            });
        }

        // Initialize date inputs
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);

        document.querySelector('input[name="check_in"]').min = today.toISOString().split('T')[0];
        document.querySelector('input[name="check_out"]').min = tomorrow.toISOString().split('T')[0];

        // Update check-out min date when check-in changes
        document.querySelector('input[name="check_in"]').addEventListener('change', function() {
            const checkIn = new Date(this.value);
            const nextDay = new Date(checkIn);
            nextDay.setDate(nextDay.getDate() + 1);
            document.querySelector('input[name="check_out"]').min = nextDay.toISOString().split('T')[0];
        });
    </script>
</body>
</html>
