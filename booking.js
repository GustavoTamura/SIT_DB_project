// Booking page functionality - loads data from database

let movies = [];
let showtimes = [];
let selectedMovieId = null;
let selectedShowtimeId = null;
let selectedSeats = [];

// Load movies from database on page load
async function loadMovies() {
    try {
        const response = await fetch('api/get_movies.php');
        const data = await response.json();
        
        if (data.success && data.movies) {
            movies = data.movies;
            populateMovieSelect();
        } else {
            console.error('Failed to load movies:', data);
            alert('無法載入電影列表');
        }
    } catch (error) {
        console.error('Error loading movies:', error);
        alert('載入電影時發生錯誤');
    }
}

// Populate movie dropdown with database data
function populateMovieSelect() {
    const movieSelect = document.getElementById('movie');
    if (!movieSelect) return;
    
    // Clear existing options except the first one
    movieSelect.innerHTML = '<option value="">Select a movie</option>';
    
    // Add movies from database
    movies.forEach(movie => {
        const option = document.createElement('option');
        option.value = movie.movie_id;
        option.textContent = movie.title;
        option.dataset.duration = movie.duration;
        option.dataset.description = movie.description;
        movieSelect.appendChild(option);
    });
    
    // Check if there's a movie parameter in the URL
    const urlParams = new URLSearchParams(window.location.search);
    const movieParam = urlParams.get('movie');
    
    if (movieParam) {
        // Find the movie by title
        const movie = movies.find(m => m.title === movieParam);
        if (movie) {
            // Set the select value to the movie ID
            movieSelect.value = movie.movie_id;
            selectedMovieId = movie.movie_id;
            // Trigger change event to load showtimes
            movieSelect.dispatchEvent(new Event('change'));
        }
    }
}

// Load showtimes for selected movie
async function loadShowtimes(movieId) {
    try {
        const response = await fetch(`api/get_showtimes.php?movie_id=${movieId}`);
        const data = await response.json();
        
        if (data.success && data.showtimes) {
            showtimes = data.showtimes;
            populateShowtimeSelect();
        } else {
            console.error('Failed to load showtimes:', data);
            // If no showtimes available, show message
            const timeSelect = document.getElementById('time');
            if (timeSelect) {
                timeSelect.innerHTML = '<option value="">No showtimes available</option>';
            }
        }
    } catch (error) {
        console.error('Error loading showtimes:', error);
    }
}

// Populate showtime dropdown
function populateShowtimeSelect() {
    const timeSelect = document.getElementById('time');
    if (!timeSelect) return;
    
    timeSelect.innerHTML = '<option value="">Select a time</option>';
    
    showtimes.forEach(showtime => {
        const option = document.createElement('option');
        option.value = showtime.showtime_id;
        const date = new Date(showtime.show_date);
        const timeStr = showtime.show_time;
        option.textContent = `${date.toLocaleDateString()} - ${timeStr}`;
        timeSelect.appendChild(option);
    });
}

// Handle movie selection change
function handleMovieChange(event) {
    const movieId = event.target.value;
    
    if (movieId) {
        selectedMovieId = movieId;
        loadShowtimes(movieId);
        
        // Enable date field
        const dateField = document.getElementById('date');
        if (dateField) {
            dateField.disabled = false;
        }
    } else {
        selectedMovieId = null;
        const timeSelect = document.getElementById('time');
        if (timeSelect) {
            timeSelect.innerHTML = '<option value="">Select a time</option>';
        }
    }
}

// Handle showtime selection
function handleShowtimeChange(event) {
    selectedShowtimeId = event.target.value;
}

// Handle form submission
async function handleBookingSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = {
        movie_id: selectedMovieId,
        showtime_id: selectedShowtimeId,
        tickets: form.tickets.value,
        name: form.name.value,
        email: form.email.value
    };
    
    // Validate
    if (!formData.movie_id) {
        alert('請選擇電影');
        return;
    }
    
    if (!formData.showtime_id) {
        alert('請選擇場次');
        return;
    }
    
    if (!formData.name || !formData.email) {
        alert('請填寫姓名和電子郵件');
        return;
    }
    
    try {
        const response = await fetch('api/create_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('訂票成功！您的訂票編號是: ' + data.booking_id);
            form.reset();
            selectedMovieId = null;
            selectedShowtimeId = null;
        } else {
            alert('訂票失敗: ' + (data.message || '未知錯誤'));
        }
    } catch (error) {
        console.error('Error creating booking:', error);
        alert('提交訂票時發生錯誤');
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadMovies();
    
    // Add event listeners
    const movieSelect = document.getElementById('movie');
    if (movieSelect) {
        movieSelect.addEventListener('change', handleMovieChange);
    }
    
    const timeSelect = document.getElementById('time');
    if (timeSelect) {
        timeSelect.addEventListener('change', handleShowtimeChange);
    }
    
    const bookingForm = document.querySelector('.booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', handleBookingSubmit);
    }
});
