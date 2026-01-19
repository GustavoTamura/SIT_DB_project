-- Updated database schema with Ticket table

-- Add booking_date column to Booking table if it doesn't exist
ALTER TABLE Booking 
ADD COLUMN IF NOT EXISTS booking_date DATETIME;

-- Update existing bookings to have booking_date = booking_time
UPDATE Booking 
SET booking_date = booking_time 
WHERE booking_date IS NULL;

-- Create Ticket table if not exists
CREATE TABLE IF NOT EXISTS Ticket (
    ticket_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    ticket_number VARCHAR(50) UNIQUE,
    seat_id INT NULL,
    price DECIMAL(6,2),
    FOREIGN KEY (booking_id) REFERENCES Booking(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (seat_id) REFERENCES Seats(seat_id) ON DELETE SET NULL
);
