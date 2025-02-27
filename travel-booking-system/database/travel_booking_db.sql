-- Create database
CREATE DATABASE IF NOT EXISTS travel_booking_db;
USE travel_booking_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create hotels table
CREATE TABLE IF NOT EXISTS hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    location VARCHAR(100),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    price_per_night DECIMAL(10,2),
    rating DECIMAL(3,2),
    amenities TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    hotel_id INT,
    check_in DATE,
    check_out DATE,
    guests INT,
    total_price DECIMAL(10,2),
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Create reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    hotel_id INT,
    rating INT,
    comment TEXT,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Create wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    hotel_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Insert sample hotels
INSERT INTO hotels (name, description, location, latitude, longitude, price_per_night, rating, amenities, image_url) VALUES
(
    'Grand Luxury Resort & Spa',
    'Experience luxury at its finest in our 5-star resort featuring world-class amenities, stunning ocean views, and exceptional service. Our resort offers spacious rooms, a full-service spa, multiple restaurants, and an infinity pool overlooking the beach.',
    'Maldives',
    4.175496,
    73.509347,
    599.99,
    4.8,
    '["Free WiFi", "Swimming Pool", "Spa", "Restaurant", "Room Service", "Beach Access", "Fitness Center", "Bar/Lounge", "Airport Shuttle"]',
    'assets/images/hotels/grand-luxury-resort.jpg'
),
(
    'Mountain View Lodge',
    'Nestled in the heart of the mountains, our lodge offers a perfect retreat for nature lovers. Enjoy hiking trails, scenic views, and cozy accommodations with modern amenities.',
    'Swiss Alps',
    46.818188,
    8.227512,
    299.99,
    4.6,
    '["Free WiFi", "Restaurant", "Parking", "Ski Storage", "Fireplace", "Mountain Views", "Bar/Lounge"]',
    'assets/images/hotels/mountain-view-lodge.jpg'
),
(
    'Urban Boutique Hotel',
    'Located in the city center, our boutique hotel combines modern design with comfort. Perfect for business travelers and tourists alike, with easy access to attractions and shopping.',
    'New York City',
    40.712776,
    -74.005974,
    399.99,
    4.5,
    '["Free WiFi", "Business Center", "Restaurant", "Fitness Center", "Room Service", "Valet Parking", "Concierge"]',
    'assets/images/hotels/urban-boutique-hotel.jpg'
);

-- Insert sample user (password: test123)
INSERT INTO users (email, password, first_name, last_name, phone) VALUES
('test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '+1234567890');

-- Insert sample reviews
INSERT INTO reviews (user_id, hotel_id, rating, comment) VALUES
(1, 1, 5, 'Amazing experience! The resort exceeded all our expectations. The staff was incredibly friendly and the facilities were top-notch.'),
(1, 2, 4, 'Beautiful location with breathtaking views. The lodge was cozy and comfortable. Great hiking trails nearby.');

-- Insert sample bookings
INSERT INTO bookings (user_id, hotel_id, check_in, check_out, guests, total_price, status) VALUES
(1, 1, '2025-03-15', '2025-03-20', 2, 2999.95, 'confirmed'),
(1, 2, '2025-04-10', '2025-04-15', 2, 1499.95, 'pending');
