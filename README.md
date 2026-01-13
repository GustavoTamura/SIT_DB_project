# MovieHub - Movie Ticket Booking System

A modern web application for movie ticket booking with admin management features.

## Features

- **User Authentication**: Registration and login system
- **Movie Management**: Browse movies loaded from MySQL database
- **Admin Panel**: Full CRUD operations for movies (Create, Read, Update, Delete)
- **Search Functionality**: Search movies by title or description
- **Responsive Design**: Modern and user-friendly interface

## Technology Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL (via XAMPP)
- **Server**: Apache (via XAMPP)

## Quick Start

1. Install XAMPP and start Apache and MySQL services
2. Import the database using `BDD.txt` in phpMyAdmin
3. Place project files in `C:\xampp\htdocs\Projet\`
4. Access the site at `http://localhost/Projet/index.html`

For detailed setup instructions, see [SETUP.md](SETUP.md)

## Default Admin Account

- **Email**: admin@moviehub.com
- **Password**: password

## Project Structure

```
Projet/
├── api/              # API endpoints
├── config.php        # Database configuration
├── login.php         # Login API
├── register.php      # Registration API
├── admin.php         # Admin panel
├── login.html        # Login/Register page
├── index.html        # Homepage
└── ...
```

## License

© 2025 MovieHub. All rights reserved.
