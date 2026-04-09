# MUJSTAYS — PG Discovery & Booking Platform

MUJSTAYS is a premium platform designed exclusively for students of **Manipal University Jaipur (MUJ)** to find, compare, and book verified PGs near the campus. 

## 🚀 Key Features
- **Student Dashboard:** Access saved properties, track booking history, and manage payments.
- **Owner Dashboard:** Live property management, analytics, and booking moderation.
- **Rich Filtering:** Search by area (Jagatpura, Sitapura, etc.), budget, gender preference, and amenities (WiFi, AC, Food).
- **Online Booking & Payments:** Partial advance payments for reservation or "Pay at Property" options.
- **Messaging System:** Directly chat with property owners for faster communication.
- **Verified Listings:** Integrated moderation system for listing approval and owner KYC.

## 🛠️ Tech Stack
- **Backend:** PHP (7.4+) with PDO for secure database interactions.
- **Database:** MySql (normalized schema with Areas, Listings, and Room Types).
- **Styling:** Vanilla CSS with a modern, glassmorphic design system.
- **Authentication:** Role-based (Student/Owner/Admin) with email-based verification (OTP).

## 📦 Setup Instructions
1. **Clone the project** into your `xampp/htdocs` directory.
2. **Database Setup:** 
   - Import the `install.sql` file into a database named `mujstays_db`.
   - Run the migration at `run_deeper_migrations.php` to seed the database with initial areas and demo users.
3. **Configuration:**
   - Edit `config/config.php` to update your local host or production URLs.
4. **Login Credentials (Demo):**
   - **Student:** `student@mujstays.com` / `Student@1234`
   - **Owner:** `owner@mujstays.com` / `Owner@1234`
   - **Admin:** `admin@mujstays.com` / `Admin@1234`

## 📁 Repository Map
- `/admin`: Moderation tools and dashboard.
- `/owner`: Listing management and property configuration.
- `/user`: Student-specific profile and booking views.
- `/includes`: Core logic, database setup, and helpers.
- `/assets`: CSS and UI assets.

## 🧪 Testing
The project uses **TestSprite** for automated frontend and backend testing. Test artifacts are available in the `/testsprite_tests` directory.

---
*Developed by DeepMind Advanced Agentic Coding for the MUJSTAYS project.*
