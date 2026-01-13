# Installation and Configuration Guide

## Prerequisites

1. **XAMPP** installed and configured
2. **MySQL** enabled in XAMPP
3. **Apache** enabled in XAMPP

## Database Configuration

1. Start XAMPP and enable **Apache** and **MySQL** services

2. Open phpMyAdmin (http://localhost/phpmyadmin)

3. Create the database by executing the SQL script contained in `BDD.txt`:
   - Click on "New database"
   - Name it `cinema_booking`
   - Select the database
   - Go to the "SQL" tab
   - Copy-paste the content of `BDD.txt`
   - Click "Execute"

4. **Important**: Modify the `admin` field in the `Customer` table so it is `DEFAULT 0` instead of `DEFAULT 1`:
   ```sql
   ALTER TABLE Customer MODIFY admin BOOLEAN DEFAULT 0;
   ```

5. Create an administrator account manually in phpMyAdmin:
   ```sql
   INSERT INTO Customer (name, email, password_hash, admin) 
   VALUES ('Admin', 'admin@moviehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
   ```
   The default password is: `password`

   Or use this command to create a custom password hash:
   ```php
   <?php echo password_hash('your_password', PASSWORD_DEFAULT); ?>
   ```

## Project Configuration

1. Place all project files in the XAMPP `htdocs` folder:
   ```
   C:\xampp\htdocs\Projet\
   ```

2. Check the configuration in `config.php`:
   - `DB_HOST`: `localhost` (default)
   - `DB_USER`: `root` (default XAMPP)
   - `DB_PASS`: `` (empty by default XAMPP)
   - `DB_NAME`: `cinema_booking`

3. If you have modified XAMPP MySQL settings, adjust them in `config.php`

## Accessing the Site

1. Open your browser and go to:
   ```
   http://localhost/Projet/index.html
   ```

2. To login as admin:
   - Email: `admin@moviehub.com`
   - Password: `password` (or the one you defined)

## Features

### Available Pages

- **index.html**: Homepage with movie list (loaded from database)
- **login.html**: Login/registration page
- **booking.html**: Booking page
- **about.html**: About page
- **contact.html**: Contact page
- **admin.php**: Administration page (admin access only)

### Authentication Features

- **Registration**: User account creation
- **Login**: Authentication with email and password
- **Logout**: Session closure
- **Persistent Session**: Session is maintained between pages

### Admin Features

The **Admin** tab only appears if you are logged in with an administrator account.

In the Admin page, you can:
- **View all movies**: Complete list of movies from the database
- **Search movies**: Search by title or description
- **Add a movie**: Create a new movie with title, duration and description
- **Edit a movie**: Update information of an existing movie
- **Delete a movie**: Remove a movie from the database

## File Structure

```
Projet/
├── api/
│   ├── movies.php          # CRUD API for movies (admin only)
│   └── get_movies.php       # API to get all movies (public)
├── config.php               # Database connection configuration
├── login.php                # Login API
├── register.php             # Registration API
├── logout.php               # Logout API
├── check_session.php        # Session verification API
├── admin.php                # Administration page
├── login.html               # Login/registration page
├── index.html               # Homepage
├── booking.html             # Booking page
├── about.html               # About page
├── contact.html             # Contact page
├── styles.css               # Stylesheet
├── auth.js                  # JavaScript script for authentication
└── BDD.txt                  # SQL script for database creation
```

## Troubleshooting

### Database Connection Error

1. Check that MySQL is started in XAMPP
2. Check settings in `config.php`
3. Check that the `cinema_booking` database exists

### Admin Tab Not Appearing

1. Check that you are logged in with an admin account
2. Check in the database that the `admin` field is set to `1` for your account
3. Logout and login again

### Movies Not Displaying

1. Check that the `Movie` table contains data
2. Check browser console for JavaScript errors
3. Check that `api/get_movies.php` works correctly

## Important Notes

- Passwords are hashed with PHP's `password_hash()`
- PHP sessions are used to maintain login state
- Access to admin API (`api/movies.php`) requires administrator rights
- All PHP files must be executed via Apache (not by double-clicking)
