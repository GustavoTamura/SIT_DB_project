# 🔔 Latest Update - January 18, 2026

## Important: Setup Required After Pull

Hey team! I've just pushed major updates to the project. Please follow these steps to get everything working on your machine.

---

## 📥 Step 1: Pull the Latest Code

```bash
cd C:\xampp\htdocs\SIT_DB_project
git pull origin main
```

---

## 🗄️ Step 2: Update Your Database (IMPORTANT!)

### Option A: If you already have the cinema_booking database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Select the `cinema_booking` database
3. Click the "SQL" tab
4. Copy and paste the contents of `database_updates.sql`
5. Click "Go" to execute

### Option B: Fresh database setup (recommended if you have issues)

1. In phpMyAdmin, drop the old `cinema_booking` database
2. Create a new database named `cinema_booking`
3. Click "SQL" tab
4. Copy and paste the contents of `cinema_booking.sql`
5. Click "Go"
6. Then also execute `database_updates.sql`

---

## 🔑 Step 3: Setup Password Hashes (REQUIRED!)

Visit this URL in your browser:
```
http://localhost/SIT_DB_project/update_passwords.php
```

This will automatically set up all test account passwords with proper hashing.

---

## ✅ Step 4: Test the System

1. Make sure XAMPP Apache and MySQL are running
2. Visit: http://localhost/SIT_DB_project/index.html
3. Try logging in with:
   - **Admin account**: ali@mail.com / ali123
   - **User account**: omar@mail.com / omar123

---