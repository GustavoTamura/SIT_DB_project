// Booking page functionality - loads data from database

let movies = [];
let theaters = [];
let showtimes = [];
let seats = [];
let selectedMovieId = null;
let selectedTheaterId = null;
let selectedDate = null;
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
            alert('Failed to load movie list.');
        }
    } catch (error) {
        console.error('Error loading movies:', error);
        alert('An error occurred while loading movies.');
    }
}

// Load theaters from database
async function loadTheaters() {
    try {
        const response = await fetch('api/get_theaters.php');
        const data = await response.json();
        
        if (data.success && data.theaters) {
            theaters = data.theaters;
            populateTheaterSelect();
        } else {
            console.error('Failed to load theaters:', data);
        }
    } catch (error) {
        console.error('Error loading theaters:', error);
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
            // Trigger change event to load theaters
            movieSelect.dispatchEvent(new Event('change'));
        }
    }
}

// Populate theater dropdown
function populateTheaterSelect() {
    const theaterSelect = document.getElementById('theater');
    if (!theaterSelect) return;
    
    theaterSelect.innerHTML = '<option value="">Select a theater</option>';
    
    theaters.forEach(theater => {
        const option = document.createElement('option');
        option.value = theater.theater_id;
        option.textContent = theater.theater_name;
        theaterSelect.appendChild(option);
    });
}

// Load showtimes for selected movie, theater and date
async function loadShowtimes(movieId, theaterId, date) {
    try {
        let url = `api/get_showtimes.php?movie_id=${encodeURIComponent(movieId)}`;
        if (theaterId) {
            url += `&theater_id=${encodeURIComponent(theaterId)}`;
        }
        if (date) {
            url += `&date=${encodeURIComponent(date)}`;
        }
        
        console.log('Fetching showtimes from:', url);
        const response = await fetch(url);
        const data = await response.json();
        
        console.log('Showtimes response:', data);
        
        if (data.success && data.showtimes) {
            showtimes = data.showtimes;
            console.log('Found', showtimes.length, 'showtimes');
            populateShowtimeSelect();
        } else {
            console.error('Failed to load showtimes:', data);
            const timeSelect = document.getElementById('time');
            if (timeSelect) {
                timeSelect.innerHTML = '<option value="">No showtimes available for this date</option>';
                alert('這個日期沒有場次，請選擇其他日期（建議：2026-01-20 到 2026-01-25）');
            }
        }
    } catch (error) {
        console.error('Error loading showtimes:', error);
        const timeSelect = document.getElementById('time');
        if (timeSelect) {
            timeSelect.innerHTML = '<option value="">Error loading showtimes</option>';
        }
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
        option.dataset.roomId = showtime.room_id;
        const timeStr = showtime.show_time.substring(0, 5); // HH:MM
        option.textContent = `${timeStr} - ${showtime.room_name || 'Room ' + showtime.room_id}`;
        timeSelect.appendChild(option);
    });
}

// Load seats for selected showtime
async function loadSeats(showtimeId) {
    console.log('Loading seats for showtime:', showtimeId);
    try {
        const response = await fetch(`api/get_seats.php?showtime_id=${encodeURIComponent(showtimeId)}`);
        const data = await response.json();
        
        console.log('Seats response:', data);
        
        if (data.success && data.seats) {
            seats = data.seats;
            console.log('Seats loaded:', seats.length, 'seats');
            displaySeatMap();
        } else {
            console.error('Failed to load seats:', data);
            document.getElementById('seat-map').innerHTML = '<p>No seats available</p>';
        }
    } catch (error) {
        console.error('Error loading seats:', error);
        document.getElementById('seat-map').innerHTML = '<p>Error loading seats</p>';
    }
}

// Display seat map
function displaySeatMap() {
    console.log('Displaying seat map...');
    const seatMap = document.getElementById('seat-map');
    const seatContainer = document.getElementById('seat-selection-container');
    
    if (!seatMap) {
        console.error('seat-map element not found!');
        return;
    }
    
    console.log('Seat container found, displaying', seats.length, 'seats');
    
    seatMap.innerHTML = '';
    seatContainer.style.display = 'block';
    
    seats.forEach(seat => {
        const seatElement = document.createElement('div');
        seatElement.className = 'seat';
        seatElement.textContent = seat.seat_number;
        seatElement.dataset.seatId = seat.seat_id;
        
        if (seat.is_booked == 1) {
            seatElement.classList.add('booked');
        } else {
            seatElement.classList.add('available');
            seatElement.addEventListener('click', () => toggleSeat(seat.seat_id, seatElement));
        }
        
        seatMap.appendChild(seatElement);
    });
}

// Toggle seat selection
function toggleSeat(seatId, seatElement) {
    if (seatElement.classList.contains('booked')) return;
    
    const index = selectedSeats.indexOf(seatId);
    
    if (index > -1) {
        // Deselect
        selectedSeats.splice(index, 1);
        seatElement.classList.remove('selected');
        seatElement.classList.add('available');
    } else {
        // Select
        selectedSeats.push(seatId);
        seatElement.classList.remove('available');
        seatElement.classList.add('selected');
    }
    
    updateSelectedSeatsDisplay();
}

// Update selected seats display
function updateSelectedSeatsDisplay() {
    const display = document.getElementById('selected-seats-display');
    if (!display) return;
    
    if (selectedSeats.length === 0) {
        display.textContent = 'None';
    } else {
        const seatNumbers = selectedSeats.map(seatId => {
            const seat = seats.find(s => s.seat_id == seatId);
            return seat ? seat.seat_number : seatId;
        });
        display.textContent = seatNumbers.join(', ');
    }
}

// Handle movie selection change
function handleMovieChange(event) {
    const movieId = event.target.value;
    
    if (movieId) {
        selectedMovieId = movieId;
        
        // Load theaters if not already loaded
        if (theaters.length === 0) {
            loadTheaters();
        }
        
        // Enable theater selection
        const theaterSelect = document.getElementById('theater');
        if (theaterSelect) {
            theaterSelect.disabled = false;
            theaterSelect.value = '';
        }
        
        // Reset only the dependent fields (date, time, seats)
        const dateField = document.getElementById('date');
        const timeSelect = document.getElementById('time');
        
        if (dateField) {
            dateField.value = '';
            dateField.disabled = true;
        }
        
        if (timeSelect) {
            timeSelect.value = '';
            timeSelect.disabled = true;
            timeSelect.innerHTML = '<option value="">Select a time</option>';
        }
        
        resetSeatSelection();
    } else {
        selectedMovieId = null;
        
        // Disable and reset all dependent fields
        const theaterSelect = document.getElementById('theater');
        if (theaterSelect) {
            theaterSelect.value = '';
            theaterSelect.disabled = true;
        }
        
        resetDateDependentFields();
    }
}

// Handle theater selection change
function handleTheaterChange(event) {
    const theaterId = event.target.value;
    
    if (theaterId) {
        selectedTheaterId = theaterId;
        
        // Enable date field
        const dateField = document.getElementById('date');
        if (dateField) {
            dateField.disabled = false;
            
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            dateField.min = today;
        }
        
        // Reset only time and seats (not date)
        const timeSelect = document.getElementById('time');
        if (timeSelect) {
            timeSelect.value = '';
            timeSelect.disabled = true;
            timeSelect.innerHTML = '<option value="">Select a time</option>';
        }
        
        resetSeatSelection();
    } else {
        selectedTheaterId = null;
        
        // Reset date and all dependent fields
        const dateField = document.getElementById('date');
        if (dateField) {
            dateField.value = '';
            dateField.disabled = true;
        }
        
        const timeSelect = document.getElementById('time');
        if (timeSelect) {
            timeSelect.value = '';
            timeSelect.disabled = true;
            timeSelect.innerHTML = '<option value="">Select a time</option>';
        }
        
        resetSeatSelection();
    }
}

// Handle date change
function handleDateChange(event) {
    const date = event.target.value;
    
    console.log('Date changed:', date);
    console.log('Selected movie:', selectedMovieId);
    console.log('Selected theater:', selectedTheaterId);
    
    if (date && selectedMovieId && selectedTheaterId) {
        selectedDate = date;
        console.log('Loading showtimes for:', selectedMovieId, selectedTheaterId, date);
        loadShowtimes(selectedMovieId, selectedTheaterId, date);
        
        // Enable showtime field
        const timeSelect = document.getElementById('time');
        if (timeSelect) {
            timeSelect.disabled = false;
        }
        
        // Reset seat selection
        resetSeatSelection();
    } else {
        console.warn('Cannot load showtimes - missing:', {
            date: date,
            movieId: selectedMovieId,
            theaterId: selectedTheaterId
        });
    }
}

// Handle showtime selection
function handleShowtimeChange(event) {
    const showtimeId = event.target.value;
    
    console.log('Showtime changed to:', showtimeId);
    
    if (showtimeId) {
        selectedShowtimeId = showtimeId;
        selectedSeats = [];
        loadSeats(showtimeId);
    } else {
        selectedShowtimeId = null;
        resetSeatSelection();
    }
}

// Reset functions
function resetTheaterDependentFields() {
    const dateField = document.getElementById('date');
    const timeSelect = document.getElementById('time');
    
    if (dateField) {
        dateField.value = '';
        dateField.disabled = true;
    }
    
    if (timeSelect) {
        timeSelect.value = '';
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Select a time</option>';
    }
    
    resetSeatSelection();
}

function resetDateDependentFields() {
    const dateField = document.getElementById('date');
    const timeSelect = document.getElementById('time');
    
    if (dateField) {
        dateField.value = '';
        dateField.disabled = true;
    }
    
    if (timeSelect) {
        timeSelect.value = '';
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Select a time</option>';
    }
    
    resetSeatSelection();
}

function resetSeatSelection() {
    selectedSeats = [];
    const seatContainer = document.getElementById('seat-selection-container');
    seatContainer.style.display = 'none';
    document.getElementById('seat-map').innerHTML = '<p>Loading seats...</p>';
}

// Handle form submission
async function handleBookingSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    
    // Validation
    if (!selectedMovieId) {
        alert('Please select a movie.');
        return;
    }
    
    if (!selectedTheaterId) {
        alert('Please select a theater.');
        return;
    }
    
    if (!selectedDate) {
        alert('Please select a date.');
        return;
    }
    
    if (!selectedShowtimeId) {
        alert('Please select a showtime.');
        return;
    }
    
    if (selectedSeats.length === 0) {
        alert('Please select at least one seat.');
        return;
    }
    
    const formData = {
        showtime_id: selectedShowtimeId,
        seat_ids: selectedSeats
    };
    
    try {
        const response = await fetch('api/create_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const contentType = response.headers.get('content-type');
        
        // Check if response is JSON
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            throw new Error('Server returned an error. Please check if you are logged in.');
        }
        
        const data = await response.json();
        console.log('Booking response:', data);
        
        if (data.success) {
            alert(`Booking Successful!\n\nBooking ID: ${data.booking_id}\nSeats: ${data.seats_count}\nTotal: $${data.total_price}\n\nTicket Numbers:\n${data.ticket_numbers.join('\n')}`);
            
            // Reset form
            form.reset();
            selectedMovieId = null;
            selectedTheaterId = null;
            selectedDate = null;
            selectedShowtimeId = null;
            selectedSeats = [];
            
            // Redirect to index or booking history
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 2000);
        } else {
            // Handle specific error messages
            if (data.message && data.message.includes('login')) {
                alert('Please login to make a booking!\n\nRedirecting to login page...');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1500);
            } else {
                alert('Booking failed: ' + (data.message || 'Unknown error.'));
            }
        }
    } catch (error) {
        console.error('Error creating booking:', error);
        alert('Booking error:\n' + error.message + '\n\nIf you are not logged in, please login first.');
    }
}


// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadMovies();
    loadTheaters(); // Preload theaters
    
    // Add event listeners
    const movieSelect = document.getElementById('movie');
    if (movieSelect) {
        movieSelect.addEventListener('change', handleMovieChange);
    }
    
    const theaterSelect = document.getElementById('theater');
    if (theaterSelect) {
        theaterSelect.addEventListener('change', handleTheaterChange);
    }
    
    const dateField = document.getElementById('date');
    if (dateField) {
        dateField.addEventListener('change', handleDateChange);
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
