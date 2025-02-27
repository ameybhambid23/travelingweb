<?php
session_start();
require_once 'config/database.php';

// Get filter parameters
$location = isset($_GET['location']) ? $_GET['location'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : 99999;
$rating = isset($_GET['rating']) ? $_GET['rating'] : 0;

// Build query
$sql = "SELECT * FROM hotels WHERE price_per_night BETWEEN ? AND ? AND rating >= ?";
$params = [$min_price, $max_price, $rating];
$types = "ddd";

if (!empty($location)) {
    $sql .= " AND location LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$hotels = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotels - TravelEase</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-md-3 p-4">
                <h4>Filters</h4>
                <form action="" method="GET" class="filter-form">
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($location); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Price Range</label>
                        <div class="d-flex gap-2">
                            <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?php echo $min_price; ?>">
                            <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?php echo $max_price; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Minimum Rating</label>
                        <select name="rating" class="form-control">
                            <option value="0" <?php echo $rating == 0 ? 'selected' : ''; ?>>Any</option>
                            <option value="3" <?php echo $rating == 3 ? 'selected' : ''; ?>>3+ Stars</option>
                            <option value="4" <?php echo $rating == 4 ? 'selected' : ''; ?>>4+ Stars</option>
                            <option value="4.5" <?php echo $rating == 4.5 ? 'selected' : ''; ?>>4.5+ Stars</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </form>
            </div>
            
            <!-- Hotels List -->
            <div class="col-md-9 p-4">
                <div class="row">
                    <?php foreach ($hotels as $hotel): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card hotel-card">
                            <img src="<?php echo htmlspecialchars($hotel['image_url'] ?? 'assets/images/hotel-placeholder.jpg'); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($hotel['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-primary">$<?php echo number_format($hotel['price_per_night'], 2); ?>/night</span>
                                        <span class="badge bg-warning"><?php echo number_format($hotel['rating'], 1); ?> ★</span>
                                    </div>
                                    <a href="hotel-details.php?id=<?php echo $hotel['id']; ?>" class="btn btn-outline-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($hotels)): ?>
                <div class="alert alert-info">No hotels found matching your criteria.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Map Modal -->
    <div class="modal fade" id="mapModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hotel Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="map" style="height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
