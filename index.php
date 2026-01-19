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
    <title>Movie Ticket Booking System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #e50914;
            --secondary-color: #f5f5f1;
            --dark-bg: #141414;
            --card-bg: #1f1f1f;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --accent: #ff6b6b;
            --success: #4caf50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #141414 0%, #1a1a1a 100%);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* Header */
        header {
            background: rgba(20, 20, 20, 0.95);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--text-primary);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.7)),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><rect fill="%23141414" width="1200" height="600"/><circle fill="%23e50914" opacity="0.1" cx="200" cy="150" r="100"/><circle fill="%23e50914" opacity="0.1" cx="800" cy="400" r="150"/></svg>');
            background-size: cover;
            background-position: center;
            padding: 4rem 2rem;
            text-align: center;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-color), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.2rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Section Titles */
        .section-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
        }

        /* Movies Grid */
        .movies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .movie-card {
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .movie-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(229, 9, 20, 0.3);
        }

        .movie-poster {
            width: 100%;
            height: 350px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            position: relative;
            overflow: hidden;
        }

        .movie-poster::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
        }

        .movie-info {
            padding: 1.5rem;
        }

        .movie-title {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .movie-meta {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .movie-rating {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            color: #ffd700;
        }

        .btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background 0.3s, transform 0.2s;
            width: 100%;
        }

        .btn:hover {
            background: #b8070f;
            transform: scale(1.02);
        }

        .btn:active {
            transform: scale(0.98);
        }

        /* Booking Section */
        .booking-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .selected-movie {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .selected-movie img {
            width: 80px;
            height: 120px;
            border-radius: 8px;
            object-fit: cover;
        }

        .selected-movie-info h3 {
            margin-bottom: 0.5rem;
        }

        .selected-movie-info p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Showtimes */
        .showtimes {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .showtime-btn {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            border: 2px solid transparent;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .showtime-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--primary-color);
        }

        .showtime-btn.selected {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Seats Selection */
        .seats-container {
            margin-top: 2rem;
        }

        .screen {
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            height: 60px;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--text-secondary);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .seats-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 0.5rem;
            max-width: 800px;
            margin: 0 auto 2rem;
        }

        .seat {
            aspect-ratio: 1;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .seat:hover:not(.occupied):not(.selected) {
            background: rgba(229, 9, 20, 0.3);
            border-color: var(--primary-color);
        }

        .seat.selected {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .seat.occupied {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            cursor: not-allowed;
            opacity: 0.5;
        }

        .seat-label {
            display: flex;
            justify-content: space-between;
            max-width: 800px;
            margin: 0 auto 1rem;
            font-size: 0.9rem;
        }

        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legend-box {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 2px solid;
        }

        .legend-box.available {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .legend-box.selected {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .legend-box.occupied {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            opacity: 0.5;
        }

        /* Booking Summary */
        .booking-summary {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .summary-row:last-child {
            border-bottom: none;
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--primary-color);
        }

        .total-price {
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        /* Footer */
        footer {
            background: var(--dark-bg);
            padding: 2rem;
            text-align: center;
            color: var(--text-secondary);
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: var(--text-primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .movies-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .seats-grid {
                grid-template-columns: repeat(8, 1fr);
            }

            .nav-links {
                display: none;
            }
        }

        /* Hidden class for dynamic content */
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <a href="index.html" class="logo">🎬 MovieHub</a>
            <ul class="nav-links">
                <li><a href="index.html#movies" class="active">Movies</a></li>
                <li><a href="booking.html">Booking</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Book Your Movie Tickets</h1>
        <p>Experience the magic of cinema. Choose your favorite movie, select your seats, and enjoy the show!</p>
    </section>

    <!-- Main Container -->
    <div class="container">
        <!-- Movies Section -->
        <section id="movies">
            <h2 class="section-title">Now Showing</h2>
            <div class="movies-grid">
                <?php foreach ($movies as $movie): 
                    $hours = floor($movie['duration'] / 60);
                    $mins = $movie['duration'] % 60;
                    $duration = "{$hours}h {$mins}m";
                    $icons = ['🎬', '🎭', '🎀', '🦇', '🌀', '🌌', '🎪', '🎨', '🎯', '🎵'];
                    $icon = $icons[$movie['movie_id'] % count($icons)];
                ?>
                <div class="movie-card">
                    <div class="movie-poster" style="cursor: pointer;" onclick="selectMovie('<?= addslashes($movie['title']) ?>', '<?= addslashes($movie['description']) ?>', '<?= $duration ?>', 'PG-13', <?= $movie['movie_id'] ?>)"><?= $icon ?></div>
                    <div class="movie-info">
                        <h3 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h3>
                        <div class="movie-meta">
                            <span class="movie-rating">⭐ 8.5</span>
                            <span><?= $duration ?></span>
                        </div>
                        <button class="btn" type="button" onclick="event.stopPropagation(); selectMovie('<?= addslashes($movie['title']) ?>', '<?= addslashes($movie['description']) ?>', '<?= $duration ?>', 'PG-13', <?= $movie['movie_id'] ?>); return false;">Book Now</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Booking Section -->
        <section id="booking" class="booking-section hidden">
            <div class="booking-header">
                <div class="selected-movie">
                    <div class="movie-poster" style="width: 80px; height: 120px; font-size: 2rem;">🎬</div>
                    <div class="selected-movie-info">
                        <h3 id="selected-movie-title">Select a movie to continue</h3>
                        <p id="selected-movie-info">Choose a movie from above</p>
                    </div>
                </div>
            </div>

            <div id="showtimes-section" class="hidden">
                <h3 style="margin-bottom: 1rem;">Select Showtime</h3>
                <div class="showtimes">
                    <button class="showtime-btn" onclick="selectShowtime('10:00 AM', this)">10:00 AM</button>
                    <button class="showtime-btn" onclick="selectShowtime('1:30 PM', this)">1:30 PM</button>
                    <button class="showtime-btn" onclick="selectShowtime('4:45 PM', this)">4:45 PM</button>
                    <button class="showtime-btn" onclick="selectShowtime('8:00 PM', this)">8:00 PM</button>
                    <button class="showtime-btn" onclick="selectShowtime('10:30 PM', this)">10:30 PM</button>
                </div>
            </div>

            <div id="seats-section" class="hidden">
                <div class="seats-container">
                    <div class="screen">SCREEN</div>
                    <div class="seat-label">
                        <span>A</span>
                        <span>B</span>
                        <span>C</span>
                        <span>D</span>
                        <span>E</span>
                        <span>F</span>
                        <span>G</span>
                        <span>H</span>
                        <span>I</span>
                        <span>J</span>
                        <span>K</span>
                        <span>L</span>
                    </div>
                    <div class="seats-grid" id="seats-grid"></div>
                    <div class="seat-legend">
                        <div class="legend-item">
                            <div class="legend-box available"></div>
                            <span>Available</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-box selected"></div>
                            <span>Selected</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-box occupied"></div>
                            <span>Occupied</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Booking Summary -->
        <section id="summary-section" class="booking-summary hidden">
            <h2 class="section-title">Booking Summary</h2>
            <div class="summary-row">
                <span>Movie:</span>
                <span id="summary-movie">-</span>
            </div>
            <div class="summary-row">
                <span>Showtime:</span>
                <span id="summary-showtime">-</span>
            </div>
            <div class="summary-row">
                <span>Seats:</span>
                <span id="summary-seats">-</span>
            </div>
            <div class="summary-row">
                <span>Ticket Price:</span>
                <span>$12.00</span>
            </div>
            <div class="summary-row">
                <span>Number of Tickets:</span>
                <span id="summary-count">0</span>
            </div>
            <div class="summary-row">
                <span>Total Amount:</span>
                <span class="total-price" id="summary-total">$0.00</span>
            </div>
            
            <!-- Customer Details Form -->
            <div id="customer-form" style="margin-top: 2rem; display: none;">
                <h3 style="margin-bottom: 1rem;">Your Details</h3>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <input type="text" id="customer-name" placeholder="Full Name" style="padding: 0.8rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); color: white;" required>
                    <input type="email" id="customer-email" placeholder="Email" style="padding: 0.8rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); color: white;" required>
                    <input type="tel" id="customer-phone" placeholder="Phone" style="padding: 0.8rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); color: white;" required>
                </div>
                <button class="btn" id="confirm-btn" onclick="processBooking()" style="margin-top: 1.5rem;">Complete Booking</button>
            </div>
            
            <button class="btn" id="proceed-btn" onclick="showCustomerForm()" style="margin-top: 1.5rem;">Proceed to Checkout</button>
            
            <div id="booking-message" style="margin-top: 1rem; padding: 1rem; border-radius: 8px; display: none;"></div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="#movies">Movies</a>
                <a href="#booking">Booking</a>
                <a href="#about">About Us</a>
                <a href="#contact">Contact</a>
                <a href="#terms">Terms & Conditions</a>
                <a href="#privacy">Privacy Policy</a>
            </div>
            <p>&copy; 2025 MovieHub. All rights reserved.</p>
        </div>
    </footer>

    <script>
        let selectedMovie = null;
        let selectedMovieId = null;
        let selectedShowtime = null;
        let selectedShowtimeId = null;
        let selectedSeats = [];
        let selectedSeatIds = [];
        const ticketPrice = 12.00;
        let occupiedSeats = [];

        // Initialize seats grid
        function initSeats(showtimeId) {
            const seatsGrid = document.getElementById('seats-grid');
            seatsGrid.innerHTML = '<p>Loading seats...</p>';
            
            fetch('api.php?action=get_seats&showtime_id=' + showtimeId)
                .then(response => response.json())
                .then(result => {
                    const seats = result.data || [];
                    seatsGrid.innerHTML = '';
                    
                    seats.forEach(seat => {
                        const seatDiv = document.createElement('div');
                        seatDiv.className = 'seat';
                        seatDiv.textContent = seat.seat_number;
                        seatDiv.dataset.seatId = seat.seat_id;
                        seatDiv.dataset.seatNumber = seat.seat_number;

                        if (seat.is_booked == 1) {
                            seatDiv.classList.add('occupied');
                        }

                        seatDiv.addEventListener('click', () => toggleSeat(seat.seat_id, seat.seat_number, seatDiv));
                        seatsGrid.appendChild(seatDiv);
                    });
                });
        }

        function selectMovie(title, genre, duration, rating, movieId) {
            console.log('selectMovie called:', title, movieId);
            
            selectedMovie = title;
            selectedMovieId = movieId;
            document.getElementById('selected-movie-title').textContent = title;
            document.getElementById('selected-movie-info').textContent = `${genre} • ${duration} • ${rating}`;
            document.getElementById('booking-section').classList.remove('hidden');
            document.getElementById('showtimes-section').classList.remove('hidden');
            document.getElementById('seats-section').classList.add('hidden');
            document.getElementById('summary-section').classList.add('hidden');
            selectedShowtime = null;
            selectedShowtimeId = null;
            selectedSeats = [];
            selectedSeatIds = [];
            
            console.log('Loading showtimes for movie:', movieId);
            // Load showtimes for this movie
            loadShowtimes(movieId);
            
            // Scroll to booking section
            console.log('Scrolling to booking section');
            const bookingSection = document.getElementById('booking');
            if (bookingSection) {
                bookingSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                console.error('Booking section not found!');
            }
        }
        
        function loadShowtimes(movieId) {
            const showtimesGrid = document.querySelector('.showtimes-grid');
            showtimesGrid.innerHTML = '<p>Loading showtimes...</p>';
            
            fetch('api.php?action=get_showtimes&movie_id=' + movieId)
                .then(response => response.json())
                .then(result => {
                    const showtimes = result.data || [];
                    if (showtimes.length === 0) {
                        showtimesGrid.innerHTML = '<p>No showtimes available for this movie.</p>';
                        return;
                    }
                    
                    showtimesGrid.innerHTML = '';
                    showtimes.forEach(showtime => {
                        const btn = document.createElement('button');
                        btn.className = 'showtime-btn';
                        btn.textContent = `${showtime.show_date} ${showtime.show_time}`;
                        btn.onclick = () => selectShowtime(showtime, btn);
                        showtimesGrid.appendChild(btn);
                    });
                });
        }

        function selectShowtime(showtime, element) {
            selectedShowtime = `${showtime.show_date} ${showtime.show_time} - ${showtime.theater_name} (${showtime.room_name})`;
            selectedShowtimeId = showtime.showtime_id;
            
            document.querySelectorAll('.showtime-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            element.classList.add('selected');
            document.getElementById('seats-section').classList.remove('hidden');
            initSeats(showtime.showtime_id);
            
            // Reset selected seats
            selectedSeats = [];
            selectedSeatIds = [];
            updateSummary();
        }

        function toggleSeat(seatId, seatNumber, element) {
            if (element.classList.contains('occupied')) {
                return;
            }

            if (element.classList.contains('selected')) {
                element.classList.remove('selected');
                selectedSeats = selectedSeats.filter(s => s !== seatNumber);
                selectedSeatIds = selectedSeatIds.filter(id => id !== seatId);
            } else {
                element.classList.add('selected');
                selectedSeats.push(seatNumber);
                selectedSeatIds.push(seatId);
            }

            updateSummary();
        }

        function updateSummary() {
            if (selectedMovie && selectedShowtime && selectedSeats.length > 0) {
                document.getElementById('summary-section').classList.remove('hidden');
                document.getElementById('summary-movie').textContent = selectedMovie;
                document.getElementById('summary-showtime').textContent = selectedShowtime;
                document.getElementById('summary-seats').textContent = selectedSeats.join(', ');
                document.getElementById('summary-count').textContent = selectedSeats.length;
                const total = (selectedSeats.length * ticketPrice).toFixed(2);
                document.getElementById('summary-total').textContent = `$${total}`;
                
                // Reset form visibility
                document.getElementById('customer-form').style.display = 'none';
                document.getElementById('proceed-btn').style.display = 'block';
                document.getElementById('booking-message').style.display = 'none';
            } else {
                document.getElementById('summary-section').classList.add('hidden');
            }
        }

        function showCustomerForm() {
            document.getElementById('customer-form').style.display = 'block';
            document.getElementById('proceed-btn').style.display = 'none';
            document.getElementById('customer-form').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function processBooking() {
            console.log('processBooking called');
            
            const name = document.getElementById('customer-name').value.trim();
            const email = document.getElementById('customer-email').value.trim();
            const phone = document.getElementById('customer-phone').value.trim();
            
            console.log('Form data:', { name, email, phone, selectedSeats, selectedSeatIds, selectedShowtimeId });
            
            if (!name || !email || !phone) {
                showMessage('Please fill in all fields', 'error');
                return;
            }
            
            if (selectedSeats.length === 0) {
                showMessage('Please select at least one seat.', 'error');
                return;
            }
            
            // Show loading state
            const confirmBtn = document.getElementById('confirm-btn');
            const originalText = confirmBtn.textContent;
            confirmBtn.textContent = 'Processing...';
            confirmBtn.disabled = true;
            showMessage('Processing your booking...', 'info');

            // Process each seat booking
            const bookingPromises = selectedSeatIds.map(seatId => {
                const bookingData = {
                    name: name,
                    email: email,
                    phone: phone,
                    showtime_id: selectedShowtimeId,
                    seat_id: seatId,
                    price: ticketPrice
                };
                
                console.log('Sending booking request:', bookingData);
                api.php?action=book
                return fetch('process_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(bookingData)
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        throw new Error('Invalid response from server: ' + text);
                    }
                });
            });
            
            Promise.all(bookingPromises)
                .then(results => {
                    console.log('Booking results:', results);
                    const allSuccess = results.every(r => r.success);
                    const bookingIds = results.filter(r => r.success).map(r => r.booking_id);
                    
                    confirmBtn.textContent = originalText;
                    confirmBtn.disabled = false;
                    
                    if (allSuccess) {
                        showMessage(
                            `✅ Booking Confirmed!\n\nMovie: ${selectedMovie}\nShowtime: ${selectedShowtime}\nSeats: ${selectedSeats.join(', ')}\nTotal: $${(selectedSeats.length * ticketPrice).toFixed(2)}\n\nBooking IDs: ${bookingIds.join(', ')}\n\nA confirmation will be sent to ${email}`,
                            'success'
                        );
                        
                        // Reset after 5 seconds
                        setTimeout(() => {
                            resetBooking();
                        }, 5000);
                    } else {
                        const errors = results.filter(r => !r.success).map(r => r.message);
                        showMessage(`❌ Booking failed: ${errors.join(', ')}`, 'error');
                    }
                })
                .catch(error => {
                    console.error('Booking error:', error);
                    confirmBtn.textContent = originalText;
                    confirmBtn.disabled = false;
                    showMessage(`❌ Error: ${error.message}. Please check console for details.`, 'error');
                });
        }
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('booking-message');
            messageDiv.style.display = 'block';
            messageDiv.textContent = message;
            messageDiv.style.whiteSpace = 'pre-line';
            messageDiv.style.padding = '1rem';
            messageDiv.style.borderRadius = '8px';
            messageDiv.style.marginTop = '1rem';
            
            if (type === 'success') {
                messageDiv.style.background = '#4caf50';
                messageDiv.style.color = 'white';
            } else if (type === 'error') {
                messageDiv.style.background = '#f44336';
                messageDiv.style.color = 'white';
            } else {
                messageDiv.style.background = 'rgba(255, 255, 255, 0.2)';
                messageDiv.style.color = 'white';
            }
        }
        
        function resetBooking() {
            selectedMovie = null;
            selectedMovieId = null;
            selectedShowtime = null;
            selectedShowtimeId = null;
            selectedSeats = [];
            selectedSeatIds = [];
            document.getElementById('booking-section').classList.add('hidden');
            document.getElementById('summary-section').classList.add('hidden');
            document.getElementById('customer-name').value = '';
            document.getElementById('customer-email').value = '';
            document.getElementById('customer-phone').value = '';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function confirmBooking() {
            // This is the old function - redirect to new flow
            showCustomerForm();
        }

        // Initialize on page load
        // Seats will be loaded when a showtime is selected
    </script>
</body>
</html>
