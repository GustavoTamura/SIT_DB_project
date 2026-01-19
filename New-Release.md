# ⚠️ Required Setup After Pull

Hello everyone!  
A new update has just been pushed to the project. To ensure everything runs smoothly, please follow the setup instructions below.

---

## 📥 Step 1 — Pull the Latest Code

```bash
cd C:\xampp\htdocs\SIT_DB_project
git pull origin Lucas
```

---

## 🗄️ Step 2 — Update Your Database (IMPORTANT)

### **Option A — If you already have the `cinema_booking` database**

1. Open phpMyAdmin  
   http://localhost/phpmyadmin
2. Select the `cinema_booking` database
3. Go to the **SQL** tab
4. Paste the contents of `database_updates.sql`
5. Click **Go**

---

### **Option B — Fresh Database Setup (recommended if issues occur)**

1. Open phpMyAdmin
2. Drop (delete) the old `cinema_booking` database
3. Create a new database named `cinema_booking`
4. Open the **SQL** tab
5. Paste the contents of `cinema_booking.sql` → Click **Go**
6. Then execute `database_updates.sql`

---

## 🧩 Step 3 — Verify Admin/User Logins

Once XAMPP **Apache + MySQL** are running, go to:

👉 http://localhost/SIT_DB_project/index.html

**Test with the login credentials:**

#### Admin account:
```
admin@mail.com
admin123
```

#### User account:
```
gabriel@mail.com
123456
```

---

## 🧪 Step 4 — Test Features

If everything is properly configured:

✔ Login system should work  
✔ Movies should load from DB  
✔ Booking should redirect to payment  
✔ Admin panel should allow add/edit/delete movies

---

If you face any issues, ping the group!
