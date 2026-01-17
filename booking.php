<?php 
require_once 'db_connect.php';

// Fetch all movies from database
$sql = "SELECT movie_id, title, duration, description FROM Movie ORDER BY title";
$result = $conn->query($sql);

$movies = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - MovieHub</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.html" class="logo">🎬 MovieHub</a>
            <ul class="nav-links">
                <li><a href="index.html#movies">Movies</a></li>
                <li><a href="booking.html" class="active">Booking</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero hero-small">
        <h1>Booking</h1>
        <p>Select your movie, showtime and seats, then confirm your reservation.</p>
    </section>

    <main class="container">
        <section class="booking-section static-booking">
            <h2 class="section-title">Booking Details</h2>
            <form class="booking-form">
                <div class="form-row">
                    <label for="movie">Movie</label>
                    <select id="movie" name="movie" onchange="loadShowtimes()">
                        <option value="">Select a movie</option>
                        <?php foreach ($movies as $movie): ?>
                        <option value="<?= $movie['movie_id'] ?>"><?= htmlspecialchars($movie['title']) ?> (<?= $movie['duration'] ?> min)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <label for="showtime">Showtime</label>
                    <select id="showtime" name="showtime" onchange="loadSeats()">
                        <option value="">Select a showtime</option>
                    </select>
                </div>

                <div class="form-row">
                    <label for="seat">Select Seat</label>
                    <select id="seat" name="seat">
                        <option value="">Select a seat</option>
                    </select>
                </div>

                <div class="form-row">
                    <label for="name">Full name</label>
                    <input type="text" id="name" name="name" placeholder="John Doe">
                </div>

                <div class="form-row">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com">
                </div>

                <button type="submit" class="btn">Confirm Booking</button>
            </form>
        </section>
    </main>

    <footer> required>
                </div>

                <div class="form-row">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" placeholder="123-456-7890" required>
                </div>

                <button type="submit" class="btn">Confirm Booking</button>
            </form>
            <div id="booking-message" style="margin-top: 20px; padding: 15px; border-radius: 8px; display: none;"></div>
        </section>
    </main>

    <script>
        function loadShowtimes() {
            const movieId = document.getElementById('movie').value;
            const showtimeSelect = document.getElementById('showtime');
            const seatSelect = document.getElementById('seat');
            
            showtimeSelect.innerHTML = '<option value="">Select a showtime</option>';
            seatSelect.innerHTML = '<option value="">Select a seat</option>';
            
            if (!movieId) return;
            
            fetch('api.php?action=get_showtimes&movie_id=' + movieId)
                .then(response => response.json())
                .then(result => {
                    const data = result.data || [];
                    data.forEach(showtime => {
                        const option = document.createElement('option');
                        option.value = showtime.showtime_id;
                        option.textContent = `${showtime.show_date} ${showtime.show_time} - ${showtime.theater_name} (${showtime.room_name})`;
                        showtimeSelect.appendChild(option);
                    });
                });
        }
        
        function loadSeats() {
            const showtimeId = document.getElementById('showtime').value;
            const seatSelect = document.getElementById('seat');
            
            seatSelect.innerHTML = '<option value="">Select a seat</option>';
            
            if (!showtimeId) return;
            
            fetch('api.php?action=get_seats&showtime_id=' + showtimeId)
                .then(response => response.json())
                .then(result => {
                    const data = result.data || [];
                    data.forEach(seat => {
                        if (!seat.is_booked) {
                            const option = document.createElement('option');
                            option.value = seat.seat_id;
                            option.textContent = seat.seat_number;
                            seatSelect.appendChild(option);
                        }
                    });
                });
        }
        
        document.querySelector('.booking-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                showtime_id: document.getElementById('showtime').value,
                seat_id: document.getElementById('seat').value,
                price: 12.50
            };
            
            fetch('api.php?action=book', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('booking-message');
                messageDiv.style.display = 'block';
                
                if (data.success) {
                    messageDiv.style.background = '#4caf50';
                    messageDiv.style.color = 'white';
                    messageDiv.textContent = data.message + ' Booking ID: ' + data.booking_id;
                    document.querySelector('.booking-form').reset();
                } else {
                    messageDiv.style.background = '#f44336';
                    messageDiv.style.color = 'white';
                    messageDiv.textContent = 'Error: ' + data.message;
                }
            })
            .catch(error => {
                const messageDiv = document.getElementById('booking-message');
                messageDiv.style.display = 'block';
                messageDiv.style.background = '#f44336';
                messageDiv.style.color = 'white';
                messageDiv.textContent = 'Error: ' + error.message;
            });
        });
    </script      <a href="contact.html">Contact</a>
            </div>
            <p>&copy; 2025 MovieHub. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>


