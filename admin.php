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
                <li><a href="admin.php" class="active">Admin</a></li>
                <li><a href="#" onclick="logout(); return false;">Logout</a></li>
            </ul>
        </nav>
    </header>

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
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn" style="flex: 1;">Save</button>
                    <button type="button" class="btn" onclick="closeModal()" style="flex: 1; background: #666;">Cancel</button>
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
                tbody.innerHTML = '<tr><td colspan="5" class="no-movies">No movies found</td></tr>';
                return;
            }

            tbody.innerHTML = movies.map(movie => `
                <tr>
                    <td>${movie.movie_id}</td>
                    <td><strong>${escapeHtml(movie.title)}</strong></td>
                    <td>${movie.duration || '-'}</td>
                    <td>${escapeHtml(movie.description || '-')}</td>
                    <td>
                        <div class="action-buttons">
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
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                
                if (data.success) {
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

        // Delete a movie
        async function deleteMovie(movieId) {
            if (!confirm('Are you sure you want to delete this movie?')) {
                return;
            }

            try {
                const response = await fetch('api/movies.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ movie_id: movieId })
                });

                const data = await response.json();
                
                if (data.success) {
                    showAlert('Movie deleted successfully', 'success');
                    loadMovies();
                } else {
                    showAlert(data.message || 'Error deleting movie', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Connection error', 'error');
            }
        }

        // Close modal
        function closeModal() {
            document.getElementById('movie-modal').style.display = 'none';
            editingMovieId = null;
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('movie-modal');
            if (event.target === modal) {
                closeModal();
            }
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

        // Logout
        async function logout() {
            try {
                const response = await fetch('logout.php');
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'index.html';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    </script>
</body>
</html>

