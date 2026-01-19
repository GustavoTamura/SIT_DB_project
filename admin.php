<?php
require_once 'config.php';

// Vérifier que l'utilisateur est connecté et admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - MovieHub</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-box {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-box input {
            flex: 1;
            min-width: 250px;
            padding: 0.7rem 0.9rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: #111;
            color: var(--text-primary);
        }

        .movies-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .movies-table th,
        .movies-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .movies-table th {
            background: rgba(229, 9, 20, 0.2);
            font-weight: 600;
        }

        .movies-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-showtime {
            background: #2196f3;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-showtime:hover {
            background: #1e88e5;
        }


        .btn-edit {
            background: #4caf50;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-edit:hover {
            background: #45a049;
        }

        .btn-delete {
            background: #f44336;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-delete:hover {
            background: #da190b;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: var(--card-bg);
            margin: 5% auto;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 85vh;
            overflow-y: auto;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .close {
            color: var(--text-secondary);
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
            z-index: 10;
            position: relative;
        }

        .close:hover {
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.7rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: #111;
            color: var(--text-primary);
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid #4caf50;
            color: #4caf50;
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid #f44336;
            color: #f44336;
        }

        .no-movies {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.html" class="logo">🎬 MovieHub</a>
            <ul class="nav-links">
                <li><a href="index.html#movies">Movies</a></li>
                <li><a href="booking.html">Booking</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </nav>
    </header>

<?php
$mode = $_GET['mode'] ?? 'movies'; // movies | messages
?>
    <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
        <a href="admin.php?mode=movies"
           class="btn"
           style="flex: 1; text-align: center; font-size: 1.1rem;
                  background: <?= $mode === 'movies' ? '#4CAF50' : '#777' ?>;">
            🎬 Manage Movies & Showtimes
        </a>

        <a href="admin.php?mode=messages"
           class="btn"
           style="flex: 1; text-align: center; font-size: 1.1rem;
                  background: <?= $mode === 'messages' ? '#2196f3' : '#777' ?>;">
            📩 View Contact Messages
        </a>
    </div>

<?php if ($mode === 'movies'): ?>
<!-- EVERYTHING THAT ALREADY EXISTS STAYS HERE -->

    <section class="hero hero-small">
        <h1>Movie Administration</h1>
        <p>Manage your cinema movies: add, edit, delete and search.</p>
    </section>

    <div class="admin-container">
        <div id="alert-container"></div>

        <div class="admin-header">
            <h2 class="section-title">Movies List</h2>
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Search for a movie..." onkeyup="searchMovies()">
                <button class="btn" onclick="openAddModal()">+ Add Movie</button>
            </div>
        </div>

        <div id="movies-container">
            <table class="movies-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Poster</th>
                        <th>Title</th>
                        <th>Duration (min)</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="movies-tbody">
                    <tr>
                        <td colspan="5" class="no-movies">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal to add/edit a movie -->
    <div id="movie-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Add Movie</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="movie-form" onsubmit="saveMovie(event)">
                <input type="hidden" id="movie-id" name="movie_id">
                <div class="form-group">
                    <label for="movie-title">Title *</label>
                    <input type="text" id="movie-title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="movie-duration">Duration (minutes)</label>
                    <input type="number" id="movie-duration" name="duration" min="1">
                </div>
                <div class="form-group">
                    <label for="movie-description">Description</label>
                    <textarea id="movie-description" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="movie-poster">Poster Image</label>
                    <input type="file" id="movie-poster" name="poster" accept="image/*">
                    <div id="current-poster" style="margin-top: 10px; max-height: 200px; overflow: hidden;"></div>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn" style="flex: 1;">Save</button>
                    <button type="button" class="btn" onclick="closeModal()" style="flex: 1; background: #666;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal to add a showtime (placeholder) -->
    <div id="showtime-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Showtime</h2>
                <span class="close" onclick="closeShowtimeModal()">&times;</span>
            </div>

            <form id="showtime-form" onsubmit="saveShowtime(event)">
                <input type="hidden" id="showtime-movie-id">

                <div class="form-group">
                    <label for="showtime-room">Theater & Room</label>
                    <select id="showtime-room" required>
                        <option value="">Loading rooms...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="showtime-date">Date</label>
                    <input type="date" id="showtime-date" required>
                </div>

                <div class="form-group">
                    <label for="showtime-time">Time</label>
                    <input type="time" id="showtime-time" required>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn" style="flex: 1;">Save</button>
                    <button type="button"
                            class="btn"
                            onclick="closeShowtimeModal()"
                            style="flex: 1; background: #666;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>


    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="index.html#movies">Movies</a>
                <a href="booking.html">Booking</a>
                <a href="about.html">About Us</a>
                <a href="contact.html">Contact</a>
            </div>
            <p>&copy; 2025 MovieHub. All rights reserved.</p>
        </div>
    </footer>

    <script>
        let currentMovies = [];
        let editingMovieId = null;

        // Load movies on page load
        window.addEventListener('DOMContentLoaded', () => {
            loadMovies();
            checkSession();
        });

        // Check session
        async function checkSession() {
            try {
                const response = await fetch('check_session.php');
                const data = await response.json();
                
                if (!data.logged_in || !data.user.is_admin) {
                    window.location.href = 'index.html';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Load all movies
        async function loadMovies() {
            try {
                const response = await fetch('api/movies.php');
                const data = await response.json();
                
                if (data.success) {
                    currentMovies = data.movies;
                    displayMovies(data.movies);
                } else {
                    showAlert('Error loading movies', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Connection error', 'error');
            }
        }

        // Display movies in table
        function displayMovies(movies) {
            const tbody = document.getElementById('movies-tbody');
            
            if (movies.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="no-movies">No movies found</td></tr>';
                return;
            }

            tbody.innerHTML = movies.map(movie => `
                <tr>
                    <td>${movie.movie_id}</td>
                    <td>
                        ${movie.poster_image ? 
                            `<img src="${movie.poster_image}" alt="${escapeHtml(movie.title)}" style="width: 60px; height: 90px; object-fit: cover; border-radius: 4px;">` : 
                            '<span style="color: #999;">No image</span>'}
                    </td>
                    <td><strong>${escapeHtml(movie.title)}</strong></td>
                    <td>${movie.duration || '-'}</td>
                    <td>${escapeHtml(movie.description || '-')}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-showtime" onclick="openAddShowtimeModal(${movie.movie_id})">
                                Add Showtime
                            </button>
                            <button class="btn-edit" onclick="editMovie(${movie.movie_id})">Edit</button>
                            <button class="btn-delete" onclick="deleteMovie(${movie.movie_id})">Delete</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Search movies
        async function searchMovies() {
            const searchTerm = document.getElementById('search-input').value.trim();
            
            if (searchTerm === '') {
                displayMovies(currentMovies);
                return;
            }

            try {
                const response = await fetch(`api/movies.php?search=${encodeURIComponent(searchTerm)}`);
                const data = await response.json();
                
                if (data.success) {
                    displayMovies(data.movies);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Open modal to add a movie
        function openAddModal() {
            editingMovieId = null;
            document.getElementById('modal-title').textContent = 'Add Movie';
            document.getElementById('movie-form').reset();
            document.getElementById('movie-id').value = '';
            document.getElementById('current-poster').innerHTML = '';
            document.getElementById('movie-modal').style.display = 'block';
        }

        // Edit a movie
        async function editMovie(movieId) {
            try {
                const response = await fetch(`api/movies.php?id=${movieId}`);
                const data = await response.json();
                
                if (data.success) {
                    editingMovieId = movieId;
                    document.getElementById('modal-title').textContent = 'Edit Movie';
                    document.getElementById('movie-id').value = data.movie.movie_id;
                    document.getElementById('movie-title').value = data.movie.title;
                    document.getElementById('movie-duration').value = data.movie.duration || '';
                    document.getElementById('movie-description').value = data.movie.description || '';
                    
                    // Display current poster if exists
                    const currentPosterDiv = document.getElementById('current-poster');
                    if (data.movie.poster_image) {
                        currentPosterDiv.innerHTML = `
                            <p>Current poster:</p>
                            <img src="${data.movie.poster_image}" alt="Current poster" style="max-width: 200px; border-radius: 8px;">
                        `;
                    } else {
                        currentPosterDiv.innerHTML = '<p style="color: #999;">No poster uploaded yet</p>';
                    }
                    
                    document.getElementById('movie-modal').style.display = 'block';
                } else {
                    showAlert('Error loading movie', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Connection error', 'error');
            }
        }

        // Save a movie (add or edit)
        async function saveMovie(event) {
            event.preventDefault();
            
            const formData = {
                title: document.getElementById('movie-title').value.trim(),
                duration: document.getElementById('movie-duration').value ? parseInt(document.getElementById('movie-duration').value) : null,
                description: document.getElementById('movie-description').value.trim()
            };

            if (!formData.title) {
                showAlert('Title is required', 'error');
                return;
            }

            const url = 'api/movies.php';
            const method = editingMovieId ? 'PUT' : 'POST';
            
            if (editingMovieId) {
                formData.movie_id = editingMovieId;
            }

            try {
                // First save the movie data
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                
                if (data.success) {
                    const movieId = editingMovieId || data.movie.movie_id;
                    
                    // Check if there's a poster file to upload
                    const posterFile = document.getElementById('movie-poster').files[0];
                    if (posterFile) {
                        await uploadPoster(movieId, posterFile);
                    }
                    
                    showAlert(editingMovieId ? 'Movie updated successfully' : 'Movie added successfully', 'success');
                    closeModal();
                    loadMovies();
                } else {
                    showAlert(data.message || 'Error saving movie', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Connection error', 'error');
            }
        }

        // Upload poster image
        async function uploadPoster(movieId, file) {
            const formData = new FormData();
            formData.append('movie_id', movieId);
            formData.append('poster', file);
            
            try {
                const response = await fetch('api/upload_poster.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (!data.success) {
                    console.error('Poster upload failed:', data.message);
                }
            } catch (error) {
                console.error('Error uploading poster:', error);
            }
        }

        // Delete a movie
        async function deleteMovie(movieId) {
            if (!confirm('Are you sure you want to delete this movie?')) {
                return;
            }

            try {
                const response = await fetch('api/movies.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ movie_id: movieId })
                });

                // If the HTTP status is not OK (4xx / 5xx), show a nice message
                if (!response.ok) {
                    // 409 or 500 most likely means FK constraint (linked showtimes/bookings)
                    if (response.status === 409 || response.status === 500) {
                        showAlert(
                            'Cannot delete this movie because it is linked to existing showtimes or bookings.',
                            'error'
                        );
                    } else {
                        showAlert('Server error while deleting the movie.', 'error');
                    }
                    return;
                }

                // For successful responses, we expect valid JSON
                const data = await response.json();

                if (data.success) {
                    showAlert('Movie deleted successfully', 'success');
                    loadMovies();
                } else {
                    showAlert(data.message || 'Error deleting movie', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                // This only runs on real network errors (server down, etc.)
                showAlert('Connection error while contacting the server.', 'error');
            }
        }


        // Open Add Showtime modal (placeholder)
        function openAddShowtimeModal(movieId) {
            document.getElementById('showtime-movie-id').value = movieId;
            document.getElementById('showtime-form').reset();

            loadRooms();

            document.getElementById('showtime-modal').style.display = 'block';
        }

        // Close Showtime modal
        function closeShowtimeModal() {
            document.getElementById('showtime-modal').style.display = 'none';
        }

        function saveShowtime(event) {
            event.preventDefault();

            const formData = new FormData();
            formData.append('movie_id', document.getElementById('showtime-movie-id').value);
            formData.append('room_id', document.getElementById('showtime-room').value);
            formData.append('show_date', document.getElementById('showtime-date').value);
            formData.append('show_time', document.getElementById('showtime-time').value);

            fetch('/api/add_showtime.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Showtime added successfully', 'success');
                    closeShowtimeModal();
                } else {
                    showAlert(data.error || 'Failed to add showtime', 'error');
                    closeShowtimeModal();
                }
            })
            .catch(error => {
                console.error('Error saving showtime:', error);
                showAlert('Server error while adding showtime', 'error');
                closeShowtimeModal();
            });
        }



        // Close modal
        function closeModal() {
            document.getElementById('movie-modal').style.display = 'none';
            editingMovieId = null;
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const movieModal = document.getElementById('movie-modal');
            const showtimeModal = document.getElementById('showtime-modal');

            if (event.target === movieModal) {
                closeModal();
            }

            if (event.target === showtimeModal) {
                closeShowtimeModal();
            }
        };


        function loadRooms() {
            const select = document.getElementById('showtime-room');
            select.innerHTML = '<option value="">Loading rooms...</option>';

            fetch('/api/get_rooms.php')
                .then(response => response.json())
                .then(rooms => {
                    select.innerHTML = '<option value="">Select a theater and room</option>';

                    rooms.forEach(room => {
                        const option = document.createElement('option');
                        option.value = room.room_id;
                        option.textContent = room.label;
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading rooms:', error);
                    select.innerHTML = '<option value="">Failed to load rooms</option>';
                });
        }

        // Show alert
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            alertContainer.innerHTML = `<div class="alert ${alertClass}">${escapeHtml(message)}</div>`;
            
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        // Escape HTML to prevent XSS injections
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>


<?php endif; ?>



<?php if ($mode === 'messages'): ?>

<?php
require_once 'config.php';
$conn = getDBConnection();

/* Filters */
$name      = $_GET['name'] ?? '';
$email     = $_GET['email'] ?? '';
$from_date = $_GET['from'] ?? '';
$to_date   = $_GET['to'] ?? '';

$where = [];

if ($name !== '') {
    $safe = $conn->real_escape_string($name);
    $where[] = "fullname LIKE '%$safe%'";
}

if ($email !== '') {
    $safe = $conn->real_escape_string($email);
    $where[] = "email LIKE '%$safe%'";
}

if ($from_date !== '') {
    $where[] = "submitted_at >= '$from_date 00:00:00'";
}

if ($to_date !== '') {
    $where[] = "submitted_at <= '$to_date 23:59:59'";
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT *
    FROM contact_messages
    $whereSql
    ORDER BY submitted_at DESC
";

$result = $conn->query($sql);
?>

<!-- Search / refresh form -->
<form method="GET" style="margin-bottom: 2rem;">
    <input type="hidden" name="mode" value="messages">

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
        <input type="text" name="name" placeholder="Full name" value="<?= htmlspecialchars($name) ?>">
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>">
        <input type="date" name="from" value="<?= htmlspecialchars($from_date) ?>">
        <input type="date" name="to" value="<?= htmlspecialchars($to_date) ?>">
    </div>

    <div style="margin-top: 1rem; display: flex; gap: 1rem;">
        <button type="submit" class="btn">🔍 Search</button>
        <a href="admin.php?mode=messages" class="btn" style="background: #666;">
            🔄 Refresh
        </a>
    </div>
</form>

<!-- Messages table -->
<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #333; color: white;">
            <th style="padding: 0.75rem;">Date</th>
            <th>Full name</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Message</th>
            <th>IP</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #ccc;">
                    <td style="padding: 0.75rem;">
                        <?= date('Y-m-d H:i', strtotime($row['submitted_at'])) ?>
                    </td>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['subject']) ?></td>
                    <td style="max-width: 400px;">
                        <?= nl2br(htmlspecialchars($row['message'])) ?>
                    </td>
                    <td><?= htmlspecialchars($row['ip_address']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="padding: 1rem; text-align: center;">
                    No messages found.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?php endif; ?>



<script src="auth.js?v=6"></script>
</body>
</html>

