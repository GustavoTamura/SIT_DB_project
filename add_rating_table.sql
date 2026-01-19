-- Add movie ratings table
-- Run this SQL to add the rating feature to your database

USE cinema_booking;

-- Create movie_ratings table
CREATE TABLE IF NOT EXISTS movie_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    movie_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating IN (2, 4, 6, 8, 10)),
    rated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_movie (movie_id, customer_id),
    FOREIGN KEY (movie_id) REFERENCES Movie(movie_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id) ON DELETE CASCADE
);

-- Add index for better performance
CREATE INDEX idx_movie_ratings ON movie_ratings(movie_id);
CREATE INDEX idx_customer_ratings ON movie_ratings(customer_id);
