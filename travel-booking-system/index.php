<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelEase - Your Travel Companion</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h1>Find Your Perfect Journey</h1>
                    <p>Discover amazing places at exclusive deals</p>
                </div>
            </div>
            <!-- Search Form -->
            <div class="search-container">
                <form action="search.php" method="GET">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" placeholder="Where to?" name="destination">
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" name="check_in">
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" name="check_out">
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" name="guests">
                                <option value="">Guests</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4+</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Featured Destinations -->
    <section class="featured-destinations">
        <div class="container">
            <h2 class="text-center mb-4">Popular Destinations</h2>
            <div class="row" id="featured-destinations-container">
                <!-- Destinations will be loaded dynamically via JavaScript -->
            </div>
        </div>
    </section>

    <!-- Special Offers -->
    <section class="special-offers bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-4">Special Offers</h2>
            <div class="row" id="special-offers-container">
                <!-- Offers will be loaded dynamically via JavaScript -->
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
