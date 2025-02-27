<?php
require_once 'database.php';

// Sample hotels data
$hotels = [
    [
        'name' => 'Grand Luxury Resort & Spa',
        'description' => 'Experience luxury at its finest in our 5-star resort featuring world-class amenities, stunning ocean views, and exceptional service. Our resort offers spacious rooms, a full-service spa, multiple restaurants, and an infinity pool overlooking the beach.',
        'location' => 'Maldives',
        'latitude' => 4.175496,
        'longitude' => 73.509347,
        'price_per_night' => 599.99,
        'rating' => 4.8,
        'amenities' => json_encode([
            'Free WiFi',
            'Swimming Pool',
            'Spa',
            'Restaurant',
            'Room Service',
            'Beach Access',
            'Fitness Center',
            'Bar/Lounge',
            'Airport Shuttle'
        ])
    ],
    [
        'name' => 'Mountain View Lodge',
        'description' => 'Nestled in the heart of the mountains, our lodge offers a perfect retreat for nature lovers. Enjoy hiking trails, scenic views, and cozy accommodations with modern amenities.',
        'location' => 'Swiss Alps',
        'latitude' => 46.818188,
        'longitude' => 8.227512,
        'price_per_night' => 299.99,
        'rating' => 4.6,
        'amenities' => json_encode([
            'Free WiFi',
            'Restaurant',
            'Parking',
            'Ski Storage',
            'Fireplace',
            'Mountain Views',
            'Bar/Lounge'
        ])
    ],
    [
        'name' => 'Urban Boutique Hotel',
        'description' => 'Located in the city center, our boutique hotel combines modern design with comfort. Perfect for business travelers and tourists alike, with easy access to attractions and shopping.',
        'location' => 'New York City',
        'latitude' => 40.712776,
        'longitude' => -74.005974,
        'price_per_night' => 399.99,
        'rating' => 4.5,
        'amenities' => json_encode([
            'Free WiFi',
            'Business Center',
            'Restaurant',
            'Fitness Center',
            'Room Service',
            'Valet Parking',
            'Concierge'
        ])
    ]
];

// Insert sample hotels
$stmt = $conn->prepare("
    INSERT INTO hotels (name, description, location, latitude, longitude, price_per_night, rating, amenities) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($hotels as $hotel) {
    $stmt->bind_param(
        "ssddddss",
        $hotel['name'],
        $hotel['description'],
        $hotel['location'],
        $hotel['latitude'],
        $hotel['longitude'],
        $hotel['price_per_night'],
        $hotel['rating'],
        $hotel['amenities']
    );
    $stmt->execute();
}

echo "Sample data has been inserted successfully!\n";
?>
