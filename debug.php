<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Database Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #1a1a1a;
            color: #fff;
        }
        .section {
            background: #2a2a2a;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border: 1px solid #444;
        }
        h2 {
            color: #e50914;
            margin-top: 0;
        }
        button {
            background: #e50914;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #b8070f;
        }
        pre {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        .success { color: #4caf50; }
        .error { color: #f44336; }
    </style>
</head>
<body>
    <h1>🔧 Cinema Booking System - Debug Page</h1>
    
    <div class="section">
        <h2>1. Database Connection Test</h2>
        <?php
        require_once 'db_connect.php';
        if ($conn->connect_error) {
            echo '<p class="error">❌ Connection FAILED: ' . $conn->connect_error . '</p>';
        } else {
            echo '<p class="success">✅ Database Connected Successfully!</p>';
            echo '<p>Host: ' . DB_HOST . '</p>';
            echo '<p>Database: ' . DB_NAME . '</p>';
            
            // Test query
            $result = $conn->query("SELECT COUNT(*) as count FROM Movie");
            if ($result) {
                $row = $result->fetch_assoc();
                echo '<p class="success">✅ Found ' . $row['count'] . ' movies in database</p>';
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h2>2. Test API Endpoints</h2>
        <button onclick="testMovies()">Test Get Movies</button>
        <button onclick="testShowtimes()">Test Get Showtimes (Movie 1)</button>
        <button onclick="testSeats()">Test Get Seats (Showtime 1)</button>
        <button onclick="testBooking()">Test Booking Process</button>
        <pre id="api-output">Click a button to test...</pre>
    </div>
    
    <div class="section">
        <h2>3. Current Data in Database</h2>
        <?php
        echo '<h3>Movies:</h3>';
        $result = $conn->query("SELECT movie_id, title, duration FROM Movie LIMIT 5");
        echo '<pre>';
        while ($row = $result->fetch_assoc()) {
            echo "ID: {$row['movie_id']}, Title: {$row['title']}, Duration: {$row['duration']} min\n";
        }
        echo '</pre>';
        
        echo '<h3>Showtimes:</h3>';
        $result = $conn->query("SELECT s.showtime_id, m.title, s.show_date, s.show_time FROM Showtime s JOIN Movie m ON s.movie_id = m.movie_id LIMIT 5");
        echo '<pre>';
        while ($row = $result->fetch_assoc()) {
            echo "ID: {$row['showtime_id']}, Movie: {$row['title']}, Date: {$row['show_date']} {$row['show_time']}\n";
        }
        echo '</pre>';
        
        echo '<h3>Recent Bookings:</h3>';
        $result = $conn->query("SELECT b.booking_id, c.name, c.email, m.title, s.seat_number FROM Booking b JOIN Customer c ON b.customer_id = c.customer_id JOIN Movie m ON (SELECT movie_id FROM Showtime WHERE showtime_id = b.showtime_id) = m.movie_id JOIN Seats s ON b.seat_id = s.seat_id ORDER BY b.booking_id DESC LIMIT 5");
        echo '<pre>';
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "Booking ID: {$row['booking_id']}, Customer: {$row['name']} ({$row['email']}), Movie: {$row['title']}, Seat: {$row['seat_number']}\n";
            }
        } else {
            echo "No bookings yet.\n";
        }
        echo '</pre>';
        ?>
    </div>
    
    <script>
        async function testMovies() {
            const output = document.getElementById('api-output');
            output.textContent = 'Loading...';
            try {
                const response = await fetch('api.php?action=get_movies');
                const data = await response.json();
                output.textContent = JSON.stringify(data, null, 2);
            } catch (error) {
                output.textContent = 'Error: ' + error.message;
            }
        }
        
        async function testShowtimes() {
            const output = document.getElementById('api-output');
            output.textContent = 'Loading...';
            try {
                const response = await fetch('api.php?action=get_showtimes&movie_id=1');
                const data = await response.json();
                output.textContent = JSON.stringify(data, null, 2);
            } catch (error) {
                output.textContent = 'Error: ' + error.message;
            }
        }
        
        async function testSeats() {
            const output = document.getElementById('api-output');
            output.textContent = 'Loading...';
            try {
                const response = await fetch('api.php?action=get_seats&showtime_id=1');
                const data = await response.json();
                output.textContent = JSON.stringify(data, null, 2);
            } catch (error) {
                output.textContent = 'Error: ' + error.message;
            }
        }
        
        async function testBooking() {
            const output = document.getElementById('api-output');
            output.textContent = 'Finding available seat...';
            try {
                // First, get available seats for showtime 1
                const seatsResponse = await fetch('api.php?action=get_seats&showtime_id=1');
                const seatsResult = await seatsResponse.json();
                const seats = seatsResult.data || [];
                
                // Find first available seat
                const availableSeat = seats.find(s => s.is_booked == 0);
                
                if (!availableSeat) {
                    output.textContent = 'No available seats found for showtime 1. All seats are booked!';
                    return;
                }
                
                output.textContent = `Found available seat: ${availableSeat.seat_number}\nTesting booking...`;
                
                // Test booking with available seat
                const response = await fetch('api.php?action=book', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: 'Test User',
                        email: 'test' + Date.now() + '@example.com',
                        phone: '123-456-7890',
                        showtime_id: 1,
                        seat_id: availableSeat.seat_id,
                        price: 12.50
                    })
                });
                const text = await response.text();
                output.textContent = 'Response:\n' + text;
                try {
                    const data = JSON.parse(text);
                    output.textContent = `Successfully tested booking!\n\nSeat: ${availableSeat.seat_number}\n\n` + JSON.stringify(data, null, 2);
                } catch (e) {
                    // Keep the text response if not JSON
                }
            } catch (error) {
                output.textContent = 'Error: ' + error.message;
            }
        }
    </script>
</body>
</html>
