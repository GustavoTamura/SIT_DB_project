<?php
// ================== CONFIG ==================
// TODO: replace this hardcoded shit by a proper call to db_connect.php
// I'm waayyy too lazy to do that right now

$db_host = "localhost";
$db_name = "cinema_booking";
$db_user = "root";
$db_pass = "";

$message_status = "";

// ================== HANDLE FORM ==================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Basic validation
    if (
        empty($_POST['fullname']) ||
        empty($_POST['email']) ||
        empty($_POST['subject']) ||
        empty($_POST['message'])
    ) {
        $message_status = "Please fill in all fields.";
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
                $db_user,
                $db_pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]
            );

            $stmt = $pdo->prepare("
                INSERT INTO contact_messages
                (ip_address, fullname, email, subject, message)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $_SERVER['REMOTE_ADDR'],
                trim($_POST['fullname']),
                trim($_POST['email']),
                trim($_POST['subject']),
                trim($_POST['message'])
            ]);

            $message_status = "Message sent successfully!";
        } catch (PDOException $e) {
            $message_status = "Database error. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - MovieHub</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.html" class="logo">🎬 MovieHub</a>
            <ul class="nav-links">
                <li><a href="index.html#movies">Movies</a></li>
                <li><a href="booking.html">Booking</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="contact.php" class="active">Contact</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero hero-small">
        <h1>Contact Us</h1>
        <p>Have a question or feedback? Get in touch with our support team.</p>
    </section>

    <main class="container">
        <section class="contact-section">
            <h2 class="section-title">Send us a message</h2>

            <?php if ($message_status): ?>
                <p class="form-status"><?= htmlspecialchars($message_status) ?></p>
            <?php endif; ?>

            <form class="booking-form" method="post" action="">
                <div class="form-row">
                    <label for="fullname">Full name</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>

                <div class="form-row">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-row">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>

                <div class="form-row">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>

                <button type="submit" class="btn">Send Message</button>
            </form>

            <div class="contact-info">
                <h3>Other ways to reach us</h3>
                <p>Email: support@moviehub.example</p>
                <p>Phone: +1 (555) 123-4567</p>
                <p>Address: 123 Cinema Street, Movie City</p>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; 2025 MovieHub. All rights reserved.</p>
        </div>
    </footer>
<script src="auth.js?v=6"></script>
</body>
</html>
