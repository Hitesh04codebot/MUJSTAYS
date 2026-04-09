MUJSTAYS
Product Requirements Document
PG Discovery & Booking Platform for Manipal University Jaipur

Version 1.0  |  April 2025
Tech Stack: PHP · MySQL · HTML/CSS/JavaScript · XAMPP/cPanel


Document Overview

Attribute	Detail	Notes
Project Name	MUJSTAYS	PG Discovery & Booking Platform
Target Users	Students of MUJ, Jaipur	PG Owners / Admins
Backend	PHP (procedural/OOP)	Single-file-per-page architecture
Database	MySQL via PDO	Hosted on cPanel / XAMPP localhost
Frontend	HTML, CSS, JavaScript	Reusable PHP component partials
Storage	Local file system	/uploads/ directory structure
Deployment	XAMPP (dev) → cPanel (prod)	Zero code change on deploy
Auth	PHP Sessions + OTP email	Role-based: student / owner / admin

 
1. Executive Summary
MUJSTAYS is a purpose-built, web-based PG (Paying Guest) discovery and booking platform designed exclusively for students of Manipal University Jaipur (MUJ). The platform addresses a critical gap in the local rental market: PGs near MUJ are either unlisted, poorly documented, or scattered across generic platforms that offer no MUJ-specific filtering, no student-centric features, and no verified owner system.

MUJSTAYS brings together three user roles — Students (Tenants), PG Owners, and Platform Administrators — on a single, cohesive platform that supports listing discovery, room-level booking, online payments, in-app communication, and review management. The system is architected for simplicity, maintainability, and zero-friction deployment: every page is a single self-contained PHP file, all environment settings live in one config file, and the identical codebase runs on XAMPP locally and cPanel in production without modification.

1.1 Problem Statement
The Core Problem
Students arriving at or enrolled in Manipal University Jaipur have no reliable, MUJ-centric platform to discover, verify, compare, and book PG accommodations. Existing national platforms (NoBroker, 99acres, MagicBricks) list fewer than 5% of actual PGs near MUJ, lack MUJ-specific location filters, do not verify owner identities, and provide no booking or payment workflow. This forces students to rely on word-of-mouth, physical visits, and unverified WhatsApp groups.

1.2 Solution Overview
•	A verified, searchable directory of PGs within commutable distance of MUJ campus
•	Rich listing pages: photos, videos, amenities, room-type pricing, availability status, map view
•	Booking workflow: request/instant booking, move-in date selection, online payment, booking history
•	Owner portal: KYC-verified listing management, booking management, earnings dashboard, tenant chat
•	Admin panel: property moderation, user management, transaction oversight, complaint resolution
•	One-config deploy: update config.php with DB credentials and the site goes live immediately on cPanel

1.3 Success Metrics
Metric	Target	Notes
PG Listings	500+ verified PGs within 6 months of launch	Quality over quantity; admin-approved only
Student Registrations	2,000+ active student accounts in Year 1	MUJ student email preferred
Booking Conversion	30%+ of listing views result in booking requests	Funnel: View → Save → Request → Confirm
Owner KYC	100% of listed PGs have verified owner identity	No unverified listings visible to students
Response Time	Owner responds to booking within 24 hours	Auto-reminder notification if no response
Review Coverage	60%+ of completed stays leave a review	Post-checkout automated email prompt
 
2. Stakeholders & User Roles
MUJSTAYS serves three distinct user roles, each with its own registration flow, dashboard, permissions, and feature set. All three roles are stored in the same users table, differentiated by a role enum field.

2.1 Role Summary
Role Name	role Enum Value	Primary Responsibility
Student / Tenant	student	Searches, views, saves, compares, books, pays, reviews PGs. Primary consumer of the platform.
PG Owner / Manager	owner	Lists and manages PG properties, handles bookings, communicates with tenants, tracks payments.
Platform Administrator	admin	Approves listings, verifies owners, manages all users, monitors transactions, resolves disputes.

2.2 Student / Tenant — Detailed Profile
Who They Are
Undergraduate or postgraduate students enrolled at Manipal University Jaipur who need accommodation. They may be first-time tenants unfamiliar with the local PG market, or returning students looking to upgrade their stay.

Key needs:
•	Find PGs quickly by location relative to MUJ campus
•	Filter by budget, room type (single/double/triple), gender, and amenities
•	See real photos and verified reviews before committing
•	Book and pay online without physical visits for initial shortlisting
•	Chat with owner to ask questions before finalizing
•	Maintain a history of bookings and payments

2.3 PG Owner / Manager — Detailed Profile
Who They Are
Individual property owners or professional PG operators managing one or more PG properties near MUJ. They want maximum occupancy, minimal vacancy, and a trustworthy channel to attract verified student tenants.

Key needs:
•	Easy listing creation with photo uploads and room-type management
•	KYC verification badge to build student trust
•	Dashboard showing occupancy rate, earnings, and pending bookings
•	Accept or reject booking requests with reason
•	Communicate with prospective tenants via in-app chat
•	Respond to reviews to manage online reputation

2.4 Platform Administrator — Detailed Profile
Who They Are
The MUJSTAYS operations team responsible for platform quality, owner verification, content moderation, and financial oversight.

Key needs:
•	Review and approve or reject PG listing submissions
•	Verify owner KYC documents and flag fraudulent accounts
•	Suspend or block users who violate platform policies
•	Monitor all transactions and manage platform commission
•	Resolve disputes between students and owners
•	Send platform-wide announcements and notifications
 
3. System Architecture
3.1 Core Architecture Principles
MUJSTAYS is built on five non-negotiable architectural decisions that govern every aspect of the codebase:

Principle	Description
Single-File-Per-Page	Each .php file is entirely self-contained: PHP logic (DB queries, form handling, session checks) at the top, HTML/CSS/JS output at the bottom. No separate controllers, no separate API files for standard page operations.
One Config File	A single /config/config.php defines ALL environment-specific values: DB credentials, BASE_URL, UPLOAD_PATH, COMMISSION_RATE, DEBUG_MODE. Changing this one file is all that is needed to switch between XAMPP and cPanel.
Reusable Component Partials	Shared UI elements (navbar, footer, PG card, filter sidebar, chat widget) live in /components/ as .php files and are pulled in via require_once. A change to navbar.php updates all 40+ pages simultaneously.
PDO Prepared Statements	All database interaction uses PDO with prepared statements to prevent SQL injection. A single /includes/db.php creates the PDO instance from config constants and is included by every page.
Localhost → cPanel Zero-Change Deploy	No environment detection code, no .env loader, no build step. Update config.php, upload the folder, import schema.sql — done.

3.2 Technology Stack
Component	Technology	Implementation	Purpose
Backend Language	PHP 8.x	Procedural + OOP mixed	All business logic, DB queries, auth
Database	MySQL 8.x	InnoDB engine, UTF-8mb4	Relational, FK constraints, indexes
Frontend	HTML5, CSS3, JS (ES6)	Vanilla + minimal jQuery	No frontend framework required
Local Dev Server	XAMPP	Apache + PHP + MySQL bundle	htdocs/mujstays/ as root
Production Hosting	cPanel Shared Hosting	Apache .htaccess for clean URLs	public_html/mujstays/ as root
File Storage	Local Filesystem	/uploads/ directory	Photos, videos, KYC documents
Email	PHPMailer / mail()	SMTP via cPanel mail server	OTP, booking confirmations, alerts
Session Auth	PHP $_SESSION	Server-side session storage	Role-based access on every page
Maps	Google Maps Embed API	Free embed (no billing required)	Property location display
Payments	Razorpay / Paytm SDK	JS + PHP server-side verify	Advance or full rent payment

3.3 Complete File & Directory Structure
Every file listed below is an actual file that must be created. The structure is organized so any developer can locate any functionality instantly.

File / Path	Purpose
/	Project root — served as website root on both XAMPP (localhost/mujstays/) and cPanel (public_html/mujstays/)
/index.php	Home page — hero section, featured PGs, popular locations, testimonials, CTAs
/explore.php	Explore/All Listings page — full PG grid with filters, sort, pagination
/pg-detail.php	Single PG detail page — gallery, amenities, map, booking form, reviews
/about.php	About Us — mission, vision, team, platform story
/contact.php	Contact Us — form (name, email, message), phone, location
/faq.php	FAQ — accordion Q&A organized by category
/terms.php	Terms & Conditions — full legal text
/privacy.php	Privacy Policy — data handling policies
/login.php	Login page — email/password + role auto-detect
/signup.php	Registration — student or owner account creation
/verify-email.php	Email OTP verification after signup
/forgot-password.php	Forgot password — sends OTP to registered email
/reset-password.php	Password reset form — validates OTP, sets new password
/user/dashboard.php	Student dashboard — saved PGs, recent bookings, notifications summary
/user/profile.php	Student profile — edit name, contact, profile photo, document upload
/user/bookings.php	Booking history — all bookings with status, payment info, action buttons
/user/saved.php	Saved / bookmarked PGs — grid view of favourites
/user/compare.php	Compare up to 3 PGs side-by-side on all parameters
/user/chat.php	In-app messaging — conversation threads with PG owners
/user/notifications.php	All notifications — booking updates, new PG alerts, payment confirmations
/user/reviews.php	Post and manage reviews for completed stays
/user/payments.php	Payment history — all transactions with receipts
/owner/dashboard.php	Owner overview — occupancy rate, earnings, pending bookings, quick actions
/owner/profile.php	Owner profile — personal details, KYC documents, business info
/owner/listings.php	All PG listings by this owner — status badges, edit/delete actions
/owner/add-listing.php	Multi-step form to create a new PG listing
/owner/edit-listing.php	Edit existing listing — pre-filled form
/owner/bookings.php	Manage booking requests — accept, reject, view tenant details
/owner/payments.php	Payment records — received amounts, commission deducted, settlement history
/owner/chat.php	Respond to tenant messages across all listings
/owner/reviews.php	View and respond to tenant reviews for all properties
/admin/dashboard.php	Admin overview — platform-wide stats, recent activity feed
/admin/users.php	Manage all users and owners — search, filter, block, verify
/admin/listings.php	Listing moderation — pending approvals, flagged listings, removal
/admin/bookings.php	View all platform bookings — filter by status, date, property
/admin/payments.php	Transaction monitoring — all payments, commission tracking, refunds
/admin/complaints.php	Dispute management — view, assign, resolve user complaints
/admin/notifications.php	Send platform announcements, manage automated notification rules
/config/config.php	THE SINGLE CONFIG FILE — DB credentials, BASE_URL, UPLOAD_PATH, all constants
/includes/db.php	PDO connection singleton — uses config constants, included by every page
/includes/auth_check.php	Session validation middleware — checks role, redirects unauthorized users
/includes/upload_handler.php	Centralized file upload logic — validates type/size, saves to /uploads/
/includes/mailer.php	Email sending wrapper — OTP, booking confirmation, alert emails
/includes/helpers.php	Utility functions — sanitize input, format currency, paginate results
/includes/payment_gateway.php	Payment gateway integration — Razorpay/Paytm order creation & verification
/components/navbar.php	Global navigation bar — logo, links, login/profile dropdown, notifications bell
/components/footer.php	Global footer — links, social media, copyright
/components/pg-card.php	Reusable PG listing card — thumbnail, price, location, rating, save button
/components/filter-sidebar.php	Search filters sidebar — price range, room type, amenities, gender, sort
/components/chat-widget.php	Floating/embedded chat widget — message thread UI
/components/notification-bell.php	Notification bell icon with unread count badge
/components/star-rating.php	Reusable star rating display and input widget
/components/booking-modal.php	Booking request modal — move-in date, room type selection, payment option
/assets/css/style.css	Global stylesheet — design system: colors, typography, cards, buttons, forms
/assets/js/main.js	Global JavaScript — filter logic, AJAX calls, modal handlers, validation
/assets/js/chat.js	Chat polling/WebSocket logic for real-time messaging feel
/assets/images/	Static images — logos, icons, placeholder images
/uploads/pg_images/	Owner-uploaded PG photos (organized by pg_id subfolder)
/uploads/pg_videos/	Owner-uploaded PG walkthrough videos
/uploads/kyc/	Owner KYC documents (ID proofs — private, not web-accessible directly)
/uploads/profile/	User and owner profile photos
/database/schema.sql	Complete MySQL dump — all table definitions + initial seed data (admin account, sample PGs)
/.htaccess	Apache URL rewriting, error pages, file access restrictions (block /uploads/kyc/ from direct URL access)
 
4. Database Design
4.1 Database Overview
Database name: mujstays_db. Engine: InnoDB (all tables). Charset: utf8mb4 (supports emojis and Devanagari script). All foreign keys are indexed. Soft-delete pattern (is_deleted flag) is used on critical tables to preserve data integrity.

4.2 Table: users
Central table for all human actors on the platform. Role enum distinguishes students, owners, and admins.
Column	Type	Constraint	Notes
id	INT UNSIGNED	PRIMARY KEY AUTO_INCREMENT	
name	VARCHAR(100)	NOT NULL	Full display name
email	VARCHAR(150)	UNIQUE NOT NULL	Login identifier
password_hash	VARCHAR(255)	NOT NULL	bcrypt hashed
phone	VARCHAR(15)	NULL	With country code
role	ENUM('student','owner','admin')	NOT NULL DEFAULT student	
gender	ENUM('male','female','other')	NULL	Used for gender-pref filtering
profile_photo	VARCHAR(255)	NULL	Path in /uploads/profile/
is_verified	TINYINT(1)	DEFAULT 0	Email verified flag
is_kyc_verified	TINYINT(1)	DEFAULT 0	Owner KYC approved by admin
is_active	TINYINT(1)	DEFAULT 1	0 = suspended/blocked by admin
otp_code	VARCHAR(6)	NULL	Email OTP for verify/reset
otp_expires_at	DATETIME	NULL	OTP expiry timestamp
created_at	DATETIME	DEFAULT CURRENT_TIMESTAMP	
updated_at	DATETIME	ON UPDATE CURRENT_TIMESTAMP	

4.3 Table: pg_listings
Core property table. Each row represents one PG property. One owner can have many listings.
Column	Type	Constraint	Notes
id	INT UNSIGNED	PRIMARY KEY AUTO_INCREMENT	
owner_id	INT UNSIGNED	FK → users.id NOT NULL	Cascades on owner delete
title	VARCHAR(200)	NOT NULL	e.g. "Shanti Girls PG Near MUJ Gate 2"
slug	VARCHAR(220)	UNIQUE NOT NULL	URL-friendly version of title
description	TEXT	NOT NULL	Full markdown-friendly description
address	VARCHAR(300)	NOT NULL	Full street address
area_name	VARCHAR(100)	NOT NULL	e.g. Jagatpura, Sitapura, Govindpura
latitude	DECIMAL(10,8)	NOT NULL	For map display and distance calc
longitude	DECIMAL(11,8)	NOT NULL	
distance_from_muj	DECIMAL(5,2)	NULL	Km, calculated on save
price_min	INT UNSIGNED	NOT NULL	Lowest room type price/month
price_max	INT UNSIGNED	NOT NULL	Highest room type price/month
gender_preference	ENUM('male','female','any')	NOT NULL DEFAULT any	
has_food	TINYINT(1)	DEFAULT 0	Meals included flag
has_wifi	TINYINT(1)	DEFAULT 0	
has_ac	TINYINT(1)	DEFAULT 0	
has_parking	TINYINT(1)	DEFAULT 0	
has_laundry	TINYINT(1)	DEFAULT 0	
has_gym	TINYINT(1)	DEFAULT 0	
has_cctv	TINYINT(1)	DEFAULT 0	
has_warden	TINYINT(1)	DEFAULT 0	
rules	TEXT	NULL	House rules, curfew info etc.
status	ENUM('draft','pending','approved','rejected','inactive')	DEFAULT draft	Admin controls approved/rejected
rejection_reason	TEXT	NULL	Admin note on rejection
is_featured	TINYINT(1)	DEFAULT 0	Show on home page featured section
avg_rating	DECIMAL(3,2)	DEFAULT 0.00	Denormalized; updated on review insert
total_reviews	INT UNSIGNED	DEFAULT 0	Denormalized count
created_at	DATETIME	DEFAULT CURRENT_TIMESTAMP	
updated_at	DATETIME	ON UPDATE CURRENT_TIMESTAMP	
is_deleted	TINYINT(1)	DEFAULT 0	Soft delete

4.4 Table: room_types
Each PG listing can have multiple room types (single, double, triple) each with its own price, total beds, and available beds.
Column	Type	Constraint	Notes
id	INT UNSIGNED	PRIMARY KEY	
pg_id	INT UNSIGNED	FK → pg_listings.id	
type	ENUM('single','double','triple','dormitory')	NOT NULL	
price_per_month	INT UNSIGNED	NOT NULL	In INR
security_deposit	INT UNSIGNED	DEFAULT 0	Refundable amount
total_beds	INT UNSIGNED	NOT NULL	
available_beds	INT UNSIGNED	NOT NULL	Decrements on confirmed booking
description	VARCHAR(300)	NULL	Room-specific details

4.5 Table: pg_images
Multiple images per PG listing. One image is flagged as the cover/thumbnail.
Column	Type	Constraint	Notes
id	INT UNSIGNED	PRIMARY KEY	
pg_id	INT UNSIGNED	FK → pg_listings.id	
file_path	VARCHAR(300)	NOT NULL	Relative path from project root
is_cover	TINYINT(1)	DEFAULT 0	Used as thumbnail in cards
sort_order	INT	DEFAULT 0	Display sequence
uploaded_at	DATETIME	DEFAULT CURRENT_TIMESTAMP	

4.6 Table: bookings
Column	Type	Constraint	Notes
id	INT UNSIGNED	PRIMARY KEY	
pg_id	INT UNSIGNED	FK → pg_listings.id	
room_type_id	INT UNSIGNED	FK → room_types.id	
student_id	INT UNSIGNED	FK → users.id	The student making the booking
owner_id	INT UNSIGNED	FK → users.id	Denormalized for fast owner queries
move_in_date	DATE	NOT NULL	
duration_months	INT UNSIGNED	DEFAULT 1	Planned stay duration
status	ENUM('pending','confirmed','rejected','cancelled','completed')	DEFAULT pending	
booking_type	ENUM('request','instant')	NOT NULL	Instant = auto-confirm if beds available
rejection_reason	TEXT	NULL	Owner fills this on rejection
total_amount	INT UNSIGNED	NOT NULL	Rent + deposit for first month
advance_paid	INT UNSIGNED	DEFAULT 0	Amount paid online at booking time
created_at	DATETIME	DEFAULT CURRENT_TIMESTAMP	
updated_at	DATETIME	ON UPDATE CURRENT_TIMESTAMP	

4.7 Table: payments
Column	Type	Constraint	Notes
id	INT UNSIGNED	PRIMARY KEY	
booking_id	INT UNSIGNED	FK → bookings.id	
payer_id	INT UNSIGNED	FK → users.id	Student who paid
amount	INT UNSIGNED	NOT NULL	In INR paise or whole rupees
gateway_order_id	VARCHAR(100)	NULL	Razorpay order ID
gateway_payment_id	VARCHAR(100)	NULL	Razorpay payment ID for verification
commission_amount	INT UNSIGNED	DEFAULT 0	Platform cut deducted
status	ENUM('initiated','success','failed','refunded')	DEFAULT initiated	
method	ENUM('upi','card','netbanking','wallet','offline')	NULL	
paid_at	DATETIME	NULL	Set on gateway success callback
created_at	DATETIME	DEFAULT CURRENT_TIMESTAMP	

4.8 Table: reviews
Column	Type	Constraint	Notes
id	INT UNSIGNED	PRIMARY KEY	
pg_id	INT UNSIGNED	FK → pg_listings.id	
student_id	INT UNSIGNED	FK → users.id	
booking_id	INT UNSIGNED	FK → bookings.id	Ensures only actual tenants review
rating	TINYINT UNSIGNED	NOT NULL (1–5)	
review_text	TEXT	NULL	
owner_response	TEXT	NULL	Owner can reply once
is_flagged	TINYINT(1)	DEFAULT 0	Admin flagged inappropriate review
created_at	DATETIME	DEFAULT CURRENT_TIMESTAMP	

4.9 Table: messages
Column	Type	Constraint	Notes
id	INT UNSIGNED	PRIMARY KEY	
sender_id	INT UNSIGNED	FK → users.id	
receiver_id	INT UNSIGNED	FK → users.id	
pg_id	INT UNSIGNED	FK → pg_listings.id NULL	Context of the conversation
booking_id	INT UNSIGNED	FK → bookings.id NULL	Linked booking if any
message_text	TEXT	NOT NULL	
is_read	TINYINT(1)	DEFAULT 0	
sent_at	DATETIME	DEFAULT CURRENT_TIMESTAMP	

4.10 Additional Tables
Table Name	Key Columns
notifications	user_id (FK), type (ENUM: booking_update, payment, new_pg, announcement, review), title, body, link, is_read, created_at
saved_pgs	id, student_id (FK → users), pg_id (FK → pg_listings), saved_at — UNIQUE(student_id, pg_id)
kyc_documents	id, owner_id (FK → users), doc_type (ENUM: aadhar, pan, passport, voter_id), file_path, status (ENUM: pending, approved, rejected), reviewed_by (FK admin user), reviewed_at
complaints	id, reporter_id (FK → users), reported_user_id (FK → users NULL), pg_id (FK NULL), booking_id (FK NULL), type (ENUM: fake_listing, owner_behaviour, payment_issue, other), description, status (ENUM: open, in_review, resolved, closed), admin_note, created_at, resolved_at
compare_sessions	id, student_id (FK → users), pg_ids (JSON array of up to 3 pg_ids), created_at — transient table, cleaned up by cron or on login
contact_messages	id, name, email, phone, subject, message, is_read, created_at — stores public contact form submissions for admin
 
5. Configuration System
5.1 config/config.php — The Single Source of Truth
This is the most critical file in the project. No other file should contain hardcoded credentials, paths, or environment-specific values. Every page includes this file first.

Constant	Purpose & Notes
DB_HOST	Database host — "localhost" for both XAMPP and standard cPanel hosting
DB_NAME	Database name — e.g. "mujstays_db" (XAMPP) or "cpanelusername_mujstays" (cPanel convention)
DB_USER	Database username — "root" on XAMPP, actual DB user on cPanel
DB_PASS	Database password — empty string on XAMPP, actual password on cPanel
BASE_URL	Full base URL — "http://localhost/mujstays" (XAMPP) or "https://mujstays.com" (cPanel)
UPLOAD_PATH	Absolute server path to uploads dir — used by upload_handler.php
MAX_FILE_SIZE_MB	Maximum upload file size in MB — default 10 for images, 50 for videos
ALLOWED_IMAGE_TYPES	Array of allowed MIME types — image/jpeg, image/png, image/webp
COMMISSION_RATE	Platform commission percentage on payments — e.g. 5 for 5%
RAZORPAY_KEY_ID	Payment gateway public key — different for test vs live
RAZORPAY_KEY_SECRET	Payment gateway secret — never exposed to frontend
SMTP_HOST	Mail server host for PHPMailer
SMTP_USER	SMTP username / email address
SMTP_PASS	SMTP password
SMTP_PORT	SMTP port — typically 587 (TLS) or 465 (SSL)
SITE_NAME	"MUJSTAYS" — used in email templates, page titles, meta tags
SITE_EMAIL	Platform contact email — shown in footer and emails
DEBUG_MODE	Boolean — true on XAMPP (shows errors), false on cPanel (logs errors silently)
ADMIN_EMAIL	Admin email for system alerts — new complaints, KYC submissions, etc.
SESSION_LIFETIME	Session cookie lifetime in seconds — default 86400 (24 hours)

5.2 Deployment Workflow
The following steps constitute the complete deployment procedure from local development to live cPanel hosting:

1.	On XAMPP: Place project folder inside C:/xampp/htdocs/mujstays/ (Windows) or /Applications/XAMPP/htdocs/mujstays/ (Mac)
2.	Open XAMPP Control Panel → Start Apache → Start MySQL
3.	Import /database/schema.sql via phpMyAdmin at localhost/phpmyadmin
4.	Set config.php: DB_HOST=localhost, DB_USER=root, DB_PASS=(empty), BASE_URL=http://localhost/mujstays, DEBUG_MODE=true
5.	Navigate to http://localhost/mujstays — site is live locally
6.	For cPanel deploy: Log into cPanel → File Manager → Navigate to public_html
7.	Upload entire mujstays/ folder via File Manager or FTP (FileZilla)
8.	In cPanel → MySQL Databases: Create database, create DB user, assign all privileges
9.	In cPanel → phpMyAdmin: Select new database, import schema.sql
10.	Update config.php only: DB_NAME, DB_USER, DB_PASS, BASE_URL, SMTP settings, RAZORPAY live keys, DEBUG_MODE=false
11.	Visit live domain — site is fully operational with zero code changes
 
6. Feature Specifications
6.1 Public Pages (No Login Required)
6.1.1 Home Page (index.php)
Feature	Specification
Hero Section	Full-width banner with tagline "Find Your Perfect PG Near MUJ", a prominent search bar with location autocomplete (limited to MUJ vicinity), and two CTAs: "Explore PGs" (→ explore.php) and "List Your PG" (→ signup.php?role=owner)
Search Bar	Location text input + room type dropdown + max price input + "Search" button. Submits GET to explore.php with pre-filled filter params.
Featured PGs	Horizontal scrollable row of 6–8 admin-selected PG cards (is_featured=1). Each card shows: cover image, title, area, price range, rating, gender badge, "View Details" button.
Popular Locations	Grid of 6 area cards (Jagatpura, Sitapura, Govindpura, Vatika, Tonk Road, Agra Road) each showing PG count and a link to explore.php?area=X
How It Works	3-step visual: Search → Book → Move In with icons and brief descriptions. Builds trust for first-time visitors.
Testimonials	Carousel of 3–5 student testimonials with name, photo (optional), MUJ batch year, rating stars, and quote. Populated from approved reviews table.
Stats Bar	Animated counters: Total Listed PGs, Verified Owners, Happy Residents, Cities Covered
Call-to-Action	Full-width banner section: "Are you a PG Owner? List your property and reach 10,000+ MUJ students" with "Register as Owner" button

6.1.2 Explore / All Listings Page (explore.php)
Feature	Specification
Results Grid	3-column responsive grid of PG cards. Each card: cover photo, title, area name, distance from MUJ, price range, amenity icon pills (WiFi, AC, Food), rating badge, gender badge, Save button (AJAX).
Filter Sidebar	Price range slider (₹2,000 – ₹20,000), Room Type checkboxes (Single/Double/Triple), Amenities checkboxes (WiFi, AC, Food, Parking, Laundry, Gym, CCTV), Gender dropdown, Distance from MUJ dropdown (0.5km, 1km, 2km, 5km+)
Sort Options	Dropdown: Relevance (default), Price: Low to High, Price: High to Low, Rating: High to Low, Newest First, Distance: Nearest First
Search Bar	Live search within results by PG name or area name — filters without page reload via JS
Pagination	Page-based pagination: 12 cards per page. Shows "Page 1 of N" and prev/next buttons. Page param in URL for shareable links.
Map View Toggle	Toggle button switches from grid view to a full Google Maps embed with pins for each result. Clicking a pin shows a small card popup with name, price, and "View" button.
No Results State	If no PGs match filters: friendly message with a "Clear Filters" button and suggested nearby areas

6.1.3 PG Detail Page (pg-detail.php?id=X)
Feature	Specification
Image Gallery	Primary large image with thumbnail strip below. Lightbox on click. If video exists, video thumbnail opens modal player.
Key Info Header	PG name, area, full address, distance from MUJ, gender badge, KYC-verified badge (if owner is verified), avg rating (stars), total reviews count
Room Types Table	Table listing all room types: Type | Price/Month | Security Deposit | Available Beds | "Book Now" button. Beds shown as "Available" or "Full" badge.
Amenities Section	Icon grid of all amenities. Available amenities highlighted, unavailable greyed out. Includes: WiFi, AC, Food (Veg/Non-Veg), Parking, Laundry, Gym, CCTV, Warden, Geyser, Water Purifier, Power Backup, Common Area
Rules & Policies	Collapsible section: house rules text, curfew timing, guest policy, notice period.
Location & Map	Google Maps embed centered on PG coordinates with a marker. Shows distance/directions from MUJ main gate.
Owner Info Card	Owner name, profile photo, phone (masked: show last 4 digits only until user is logged in), "Chat with Owner" button, "Call" button (reveals full number on click for logged-in users), "Report this Listing" link
Availability Status	Banner: "Rooms Available" (green) or "All Rooms Full — Join Waitlist" (orange)
Reviews Section	Rating distribution bar chart, list of approved reviews with student name (first name + last initial), date, rating, review text, and owner response if any
Sticky CTA	Fixed bottom bar on mobile: "₹X,XXX/mo onwards — Book Now" button

6.2 Authentication System
6.2.1 Registration (signup.php)
Feature	Specification
Role Selection	Step 1: Two large cards — "I am a Student looking for a PG" and "I am a PG Owner". Role selection determines form fields shown in next steps.
Student Fields	Full Name, MUJ Email (@jaipur.manipal.edu preferred but not enforced), Password, Confirm Password, Phone Number, Gender
Owner Fields	Full Name, Email, Password, Phone, Business Name (optional), Number of Properties (optional)
Validation	Client-side: required fields, email format, password strength (min 8 chars, 1 number), password match. Server-side: duplicate email check, sanitization.
Email Verification	On submit: account created with is_verified=0, 6-digit OTP sent to email, redirect to verify-email.php
OTP Verification	verify-email.php: user enters OTP within 10 minutes. On success: is_verified=1, redirect to role dashboard. "Resend OTP" button after 60-second cooldown.

6.2.2 Login & Password Recovery
Feature	Specification
Login	Email + Password. On success: set $_SESSION[user_id, role, name, is_verified]. If !is_verified: redirect to verify-email.php. If !is_active: show suspension message.
Role Redirect	After login: student → /user/dashboard.php, owner → /owner/dashboard.php, admin → /admin/dashboard.php
Remember Me	"Stay logged in" checkbox — extends session cookie lifetime to SESSION_LIFETIME constant
Forgot Password	Email input → OTP sent → reset-password.php asks for OTP + new password + confirm → bcrypt hash stored → success message → redirect to login
Failed Login	After 5 failed attempts for same email within 30 min: account temporarily locked for 15 minutes. Lockout stored in users table (login_attempts, locked_until fields).

6.3 Student Features
6.3.1 Booking System
Feature	Specification
Request Booking	Student clicks "Book Now" on a room type → booking-modal.php opens → select move-in date (calendar picker, min: today + 3 days) → choose payment option (Pay Advance Online / Pay at Property) → Submit creates booking in status=pending
Instant Booking	For listings where owner enables instant booking: status auto-set to confirmed if beds available. Decrement available_beds in room_types.
Payment Flow	If "Pay Advance Online" selected: Razorpay order created server-side → JS opens Razorpay checkout → on success: payment verified server-side via signature check → payment record created, booking confirmed
Booking History	/user/bookings.php: table of all bookings with columns: PG Name, Room Type, Move-in Date, Status badge, Amount, Action. Actions vary by status: pending=Cancel, confirmed=Chat/View Receipt, completed=Write Review
Cancellation	Student can cancel a pending booking before owner confirmation with no penalty. Confirmed bookings show "Request Cancellation" which triggers an admin complaint.

6.3.2 Save, Compare & Notifications
Feature	Specification
Save / Bookmark	Heart icon on every PG card. AJAX toggle — inserts/deletes from saved_pgs table. Saved count shown on profile nav. /user/saved.php shows all bookmarked PGs.
Compare	Each PG card has "Add to Compare" button. Up to 3 PGs added to compare_sessions. /user/compare.php: full side-by-side table — Price, Room Types, Amenities, Rating, Distance, Rules, Location map.
Notifications	/user/notifications.php: full list with icons by type. Unread count badge on navbar bell icon. Clicking notification marks it read and navigates to relevant page. Types: booking_status_change, payment_receipt, new_pg_nearby, owner_message, review_posted.
New PG Alerts	Student can set area preferences in profile. When admin approves a new PG in that area, notification is generated automatically.

6.4 Owner Features
6.4.1 Add Listing (owner/add-listing.php)
Multi-step form organized into 4 tabs/steps to prevent overwhelming the owner:
Step	Fields & Behavior
Step 1: Basic Info	PG Title, Description (rich textarea), Address, Area Name (dropdown of MUJ-area localities), Pin Code, Google Maps link or coordinates input (auto-geocoded)
Step 2: Room & Pricing	Dynamic room type builder: add rows for Single/Double/Triple. Each row: Price/Month, Security Deposit, Total Beds, Available Beds. At least one room type required.
Step 3: Amenities & Rules	Checkboxes grid for all amenities. Gender Preference dropdown. Food type (Veg/Non-Veg/Both). Rules textarea. Has Food checkbox triggers food price per month field.
Step 4: Photos & Videos	Drag-and-drop image uploader — min 3 photos required for submission. One photo marked as cover. Optional: video upload (max 50MB). Preview thumbnails shown before submit.
Form Validation	Client-side: all required fields, min photos check. Server-side: file type/size validation via upload_handler.php, sanitize all text inputs. On error: re-display form with error messages and previously entered data preserved.
Post-Submit	Listing created with status=pending. Owner sees "Your listing is under review" message. Admin receives notification email. Listing hidden from public until admin approves.

6.4.2 Owner Dashboard (owner/dashboard.php)
Widget	Specification
Summary Cards	4 stat cards at top: Total Listings, Total Confirmed Bookings (this month), Total Earnings (this month, after commission), Pending Booking Requests
Occupancy Rate	Per-listing occupancy bar: filled_beds / total_beds as percentage. Color coded: green ≥70%, orange 40–69%, red <40%
Recent Bookings	Table of last 10 bookings across all properties: Student Name, PG Name, Room Type, Move-in Date, Status badge, Action (Accept/Reject/Chat)
Quick Actions	Shortcut buttons: + Add New Listing, View All Bookings, View Payments, KYC Status (shows pending/approved badge)
Earnings Chart	Monthly bar chart (last 6 months) showing total received after commission. Built using Chart.js (CDN, no install needed).

6.5 Admin Features
6.5.1 Admin Dashboard (admin/dashboard.php)
Widget	Specification
Platform Stats	Cards: Total Registered Users, Total PG Owners, Total Active Listings, Total Bookings (all time), Total Revenue (platform commission collected), Pending KYC Requests, Open Complaints
Activity Feed	Real-time-feel feed of last 20 events: new registrations, listing submissions, bookings, payments, complaints — with timestamps and links to relevant admin pages
Pending Actions Queue	Priority list: Listings awaiting approval (N), KYC documents awaiting review (N), Open complaints (N). Each with direct action links.
Revenue Chart	Monthly line chart of platform commission over last 12 months
Top PGs	Table of top 5 PGs by bookings this month — used to understand platform health

6.5.2 Listing Moderation (admin/listings.php)
Feature	Specification
Listing Queue	Tabs: Pending (N) | Approved | Rejected | All. Table: PG Name, Owner Name, Area, Submitted Date, Status, Actions.
Review Listing	Click listing → view full detail identical to public pg-detail.php but with admin action bar at top: Approve, Reject (with reason textarea), Feature (toggle is_featured), Remove
Bulk Actions	Checkbox select multiple listings → bulk approve or bulk remove for efficiency
Search & Filter	Search by PG name or owner email. Filter by status, area, date range.
Quality Checklist	Admin sees checklist: Has 3+ photos? Has complete address? Room types filled? Description > 100 chars? Helps standardize approval decisions.
 
7. Page-by-Page Technical Specification
7.1 Every Page — Standard Pattern
Without exception, every .php file follows this exact internal structure:

12.	PHP Logic Block (top of file): session_start(), require_once config/config.php, require_once includes/db.php, require_once includes/auth_check.php (protected pages only), handle POST form submissions, run SELECT queries for display data, define PHP variables for template
13.	Component Includes: require_once components/navbar.php, any page-specific component includes
14.	HTML Output Block: DOCTYPE, head (meta, title, style.css link, page-specific CSS), body, PHP echo of data variables, JS at bottom (main.js, page-specific JS), navbar and footer already loaded via require_once

7.2 Page Technical Details
File	HTTP Methods	Key DB Operations	Auth Requirement
index.php	GET only	SELECT approved featured PGs + recent approved PGs by area + approved reviews for testimonials	No auth required
explore.php	GET (filter params)	SELECT with dynamic WHERE clause built from GET params. COUNT query for pagination.	No auth required
pg-detail.php	GET ?id=N	SELECT pg + images + room_types + owner + reviews + amenities. Log view count.	No auth (save/book needs session)
login.php	GET + POST	POST: verify email+password, check active/verified, set session	Redirect if already logged in
signup.php	GET + POST	POST: validate, check duplicate email, INSERT user, send OTP email	Redirect if already logged in
verify-email.php	GET + POST	POST: check OTP + expiry, UPDATE is_verified=1, clear OTP fields	Must be logged in (session set at signup)
forgot-password.php	GET + POST	POST: find email, generate OTP, UPDATE users, send email	No auth required
reset-password.php	GET + POST	POST: verify OTP+expiry, bcrypt new password, UPDATE user, clear OTP	No auth required
user/dashboard.php	GET only	SELECT saved_pgs (last 4) + bookings (last 5) + unread notifications count	role=student required
user/bookings.php	GET + POST (cancel)	SELECT all bookings for student. POST cancel: UPDATE status=cancelled if pending	role=student required
user/compare.php	GET ?ids=1,2,3	SELECT full details for up to 3 pg_ids. Build comparison array.	role=student required
owner/add-listing.php	GET + POST	POST: validate, INSERT pg_listings, INSERT room_types, handle multiple file uploads via upload_handler.php, INSERT pg_images	role=owner, is_kyc_verified check optional
owner/dashboard.php	GET only	SELECT listings + bookings + SUM(payments) for this owner	role=owner required
owner/bookings.php	GET + POST	POST accept: UPDATE booking status=confirmed, decrement available_beds, send notification. POST reject: UPDATE status=rejected, store reason, send notification	role=owner required
admin/listings.php	GET + POST	POST approve: UPDATE status=approved, send owner notification. POST reject: UPDATE status=rejected with reason. POST feature: toggle is_featured.	role=admin required
admin/users.php	GET + POST	POST block: UPDATE is_active=0. POST verify_kyc: UPDATE is_kyc_verified=1. SELECT all users with filter.	role=admin required
 
8. Reusable Component Specifications
All files in /components/ are included via require_once and receive data through globally available PHP variables or by running their own small queries. They output HTML only — no form processing logic.

Component	Detailed Specification
navbar.php	Dynamic navigation based on $_SESSION[role]. Unauthenticated: Logo, Home, Explore, About, Contact, Login, Sign Up. Student: + My Bookings, Saved, Compare, Notifications bell with unread count badge, Profile dropdown (Dashboard, Profile, Logout). Owner: + My Listings, Bookings, Earnings, Profile dropdown. Admin: + Admin Panel link. Mobile: hamburger menu with slide-in drawer. Active link highlighted based on current page filename.
footer.php	Three columns: Brand (logo, tagline, social links), Quick Links (Home, Explore, About, Contact, FAQ, T&C, Privacy), Contact Info (address near MUJ, email, phone). Bottom bar: Copyright line, "Built for MUJ Students" tagline.
pg-card.php	Accepts $pg array variable. Renders: cover image (or placeholder if none), title (truncated to 60 chars), area name, distance badge, price range, amenity icon pills (max 4 shown), rating stars with count, gender badge (colored), "Save" heart button (AJAX, only for logged-in students), "View Details" button linking to pg-detail.php?id={id}.
filter-sidebar.php	Pure HTML form outputting GET params. Price range: dual-handle JS slider + number inputs (₹2,000 – ₹20,000). Room type: checkboxes. Amenities: scrollable checkbox list. Gender: radio buttons. Distance from MUJ: select dropdown. Sort: select dropdown. "Apply Filters" button + "Clear All" link. On mobile: hidden behind "Show Filters" toggle button.
booking-modal.php	Included on pg-detail.php. Bootstrap/CSS modal. Fields: Room type selector (radio, shows price), Move-in date (date input, min=today+3), Duration (1–12 months), Payment option radio (Online Advance / Pay at Property). Shows total calculation dynamically in JS. Submit triggers server-side booking creation via form POST or AJAX.
chat-widget.php	Embedded chat UI on /user/chat.php and /owner/chat.php. Left sidebar: list of conversations (avatar, name, last message snippet, timestamp, unread badge). Right panel: message thread with send input at bottom. Polling: JavaScript setInterval every 5 seconds calls a lightweight messages-poll.php endpoint to fetch new messages and append to thread. No WebSocket required — polling sufficient for MVP.
notification-bell.php	Bell SVG icon with a red badge showing unread count (fetched in navbar.php query). Clicking opens a dropdown showing last 5 notifications with icon, title, time, read/unread state. "View All" link at bottom. Clicking any notification: AJAX marks as read, then navigates to notification link.
star-rating.php	Two modes: display (shows filled/half/empty stars based on decimal rating) and input (clickable stars for review form, stores value in hidden input). Accepts $rating (float) and $mode (display|input) parameters.
 
9. Security Specifications
Security Concern	Implementation
SQL Injection Prevention	ALL database queries use PDO prepared statements with named or positional placeholders. Direct string interpolation in SQL queries is prohibited throughout the codebase.
XSS Prevention	All user-supplied data echoed to HTML is wrapped in htmlspecialchars($var, ENT_QUOTES, UTF-8). This is enforced in every PHP template output section.
CSRF Protection	Every POST form includes a CSRF token generated by session: $_SESSION[csrf_token] = bin2hex(random_bytes(32)). Token validated on POST handler before processing.
Password Security	All passwords stored using PHP password_hash($pwd, PASSWORD_BCRYPT) with default cost factor 12. Verified via password_verify(). Plaintext passwords never stored or logged.
Session Security	session_regenerate_id(true) called on login to prevent session fixation. Sessions stored server-side. SESSION_LIFETIME constant controls cookie expiry. session_destroy() on logout.
File Upload Security	upload_handler.php validates: MIME type via finfo_file() (not file extension alone), file size against MAX_FILE_SIZE_MB, generates random filename (uniqid + random_bytes) to prevent path traversal. KYC files stored in /uploads/kyc/ which is blocked from direct web access via .htaccess.
Role-Based Access Control	auth_check.php included at top of every protected page. Checks: session exists → user is_active → role matches required role for that directory. Any failure → redirect to login with error message.
Rate Limiting	Login: 5 failed attempts within 30 minutes → 15-minute lockout (stored in users table). OTP resend: 60-second cooldown enforced server-side. Contact form: max 3 submissions per IP per hour (stored in contact_messages table).
Phone Number Masking	Owner phone numbers shown as "+91 XXXXX XXXXX XX" to guest/unauthenticated users. Full number revealed only to logged-in students via a separate AJAX endpoint that logs the reveal event.
Admin Route Protection	Every file in /admin/ requires role=admin in session. Admin login is separate from user login — same login.php but session role check redirects. Multi-factor authentication (OTP to admin email) optionally added for admin login.
Input Sanitization	helpers.php provides sanitize_input() function: strips tags, trims whitespace, runs htmlspecialchars. Used on all $_POST and $_GET values before use.
Error Handling	DEBUG_MODE=false on production: PHP display_errors=Off, errors logged to /logs/error.log, generic "Something went wrong" shown to users. No stack traces or DB errors exposed to frontend.
 
10. UX & Design System
10.1 Design Principles
•	Mobile-First: All pages designed for 375px mobile viewport first, then responsive breakpoints at 768px (tablet) and 1200px (desktop)
•	MUJ-Contextual: Color palette, imagery, and copy reflect the Jaipur / Rajasthan cultural context while remaining modern and professional
•	Trust-First: KYC badges, verified review counts, owner response rates, and photo galleries are prominently displayed to reduce booking anxiety
•	Minimal Cognitive Load: Filter sidebar auto-collapses on mobile, booking modal is single-screen with inline price calculation, chat is a familiar messaging UI pattern

10.2 Color & Typography
Design Token	Value & Usage
Primary Color	#1A3C5E — Deep navy blue for header, buttons, headings
Accent Color	#2E86AB — Teal blue for links, badges, highlights
Success Green	#27AE60 — Booking confirmed, KYC verified, available badges
Warning Orange	#F39C12 — Pending status, waitlist, near-full occupancy
Danger Red	#E74C3C — Rejected, full, suspended, blocked badges
Background	#F8FAFC — Off-white page background
Card Background	#FFFFFF — White cards with 8px border-radius, subtle shadow
Primary Font	Inter (Google Fonts CDN) — body text, UI labels
Heading Font	Poppins (Google Fonts CDN) — page titles, section headings
Base Font Size	16px body, 14px captions, 13px table cells
Icon Library	Font Awesome 6 Free (CDN) — consistent icon language across all pages

10.3 Responsive Breakpoints
Breakpoint	Range	Layout Behavior
Mobile	< 768px	Single column layouts, hamburger nav, stacked filter sidebar, bottom sticky CTA bar
Tablet	768px – 1199px	2-column PG grid, filter as overlay drawer, condensed navbar
Desktop	≥ 1200px	3-column PG grid, persistent filter sidebar, full navbar, hover effects on cards
 
11. Notification & Email System
11.1 In-App Notifications
Notifications are generated by server-side PHP functions in helpers.php: create_notification($user_id, $type, $title, $body, $link). Each notification event below triggers this function:

Type	Recipient	Message Template
booking_update	Student	Your booking at {PG Name} has been {confirmed/rejected}
booking_request	Owner	New booking request from {Student Name} for {Room Type}
payment_receipt	Student	Payment of ₹{amount} confirmed for {PG Name}
payment_received	Owner	₹{amount} received for booking at {PG Name}
new_pg_nearby	Student	New PG listed in {area} matching your preferences
review_posted	Owner	{Student} left a {N}-star review for {PG Name}
kyc_approved	Owner	Your KYC has been verified. Your listings are now live.
kyc_rejected	Owner	Your KYC verification was not approved. Reason: {reason}
listing_approved	Owner	Your listing {PG Name} has been approved and is now live
listing_rejected	Owner	Your listing {PG Name} was not approved. Reason: {reason}
complaint_resolved	Reporter	Your complaint #{id} has been resolved by the admin
announcement	All Users	Platform-wide message from admin (e.g. maintenance, new features)

11.2 Email Notifications (via mailer.php)
Email Type	Content Specification
OTP Email	Subject: "Your MUJSTAYS verification code is {OTP}". HTML template with large OTP display, 10-minute validity notice, "Ignore if you did not request this" footer.
Booking Confirmation	To student: PG name, address, room type, move-in date, amount paid, owner contact. To owner: student name, contact, room type, move-in date.
Booking Rejection	To student: PG name, rejection reason, link to explore similar PGs
Payment Receipt	HTML receipt with: transaction ID, amount, date, PG name, room type, gateway reference. Printable format.
Review Prompt	Sent 7 days after confirmed move-in date: "How was your stay at {PG Name}? Share your experience to help other MUJ students."
New PG Alert	To students who set area preferences: PG name, photo thumbnail, price range, distance from MUJ, "View & Book" CTA button.
Admin Alert	To admin email: daily digest of pending KYC (N), pending listings (N), open complaints (N). Sent at 9 AM by a cron job or triggered on first admin login of the day.
 
12. Non-Functional Requirements
Requirement	Specification
Performance	Pages must load in under 3 seconds on a standard cPanel shared hosting plan with a 10 Mbps connection. Achieved via: image compression (max 800px width, JPEG quality 80) done by upload_handler.php using PHP GD library, pagination limiting result sets to 12 rows, DB indexes on pg_listings.status, pg_listings.area_name, bookings.student_id, bookings.owner_id, messages.receiver_id
Scalability	Designed for 500–5,000 concurrent sessions on shared hosting. No horizontal scaling in MVP. If traffic grows beyond shared hosting capacity, architecture allows migration to VPS (Nginx + PHP-FPM) by updating .htaccess rules only — application code unchanged.
Availability	Platform targets 99% uptime relying on cPanel hosting provider SLA. No redundancy in MVP. Database backups automated via cPanel built-in backup tool scheduled daily.
Maintainability	Single-file-per-page architecture means any developer can open one file and understand the full request lifecycle for that page without tracing across multiple layers. All DB queries are in the same file that uses them — no hidden ORM magic.
Browser Compatibility	Chrome 90+, Firefox 88+, Safari 14+, Edge 90+, Mobile Chrome (Android 8+), Mobile Safari (iOS 13+). No IE11 support.
Accessibility	WCAG 2.1 Level A compliance: all images have alt text, form inputs have labels, color contrast ratio ≥ 4.5:1 for body text, keyboard navigation works on all interactive elements, focus states visible.
SEO	Each PG detail page has: unique <title> tag, meta description (auto-generated from PG description), Open Graph tags for social sharing, canonical URL, schema.org/LodgingBusiness structured data for search engine rich results.
Data Retention	User data retained as long as account is active. On account deletion request: anonymize personal data (replace name/email/phone with [deleted]), retain booking records for financial audit. KYC documents retained for 2 years after owner account closure.
 
13. Development Roadmap
13.1 Phase 1 — Core MVP (Weeks 1–6)
Goal
Working platform with public listing discovery, owner listing creation, and basic student booking flow.

•	Setup: XAMPP environment, mujstays_db schema.sql, config.php, db.php, auth_check.php, helpers.php
•	Auth: signup.php, login.php, verify-email.php, forgot-password.php, reset-password.php
•	Core UI: style.css design system, navbar.php, footer.php, pg-card.php
•	Home: index.php — hero, featured PGs (manually seeded), popular areas, static testimonials
•	Explore: explore.php — full listings grid with filter sidebar, pagination
•	PG Detail: pg-detail.php — gallery, room types, amenities, owner info, map embed
•	Owner: add-listing.php, edit-listing.php, listings.php, owner profile
•	Admin: admin/listings.php approval workflow, admin/users.php basic management
•	Student: user/bookings.php (view only), user/profile.php

13.2 Phase 2 — Engagement & Payments (Weeks 7–10)
Goal
Fully functional booking + payment system, chat, notifications, and reviews.

•	Payment: payment_gateway.php Razorpay integration, user/payments.php, owner/payments.php
•	Chat: chat-widget.php, user/chat.php, owner/chat.php, messages-poll.php endpoint
•	Notifications: notification-bell.php, user/notifications.php, in-app notification generation on all key events
•	Reviews: star-rating.php, user/reviews.php, review display on pg-detail.php, owner/reviews.php responses
•	Save & Compare: AJAX save toggle, user/saved.php, user/compare.php side-by-side
•	Email: mailer.php, OTP emails, booking confirmation emails, payment receipts

13.3 Phase 3 — Admin & Polish (Weeks 11–13)
Goal
Full admin panel, KYC workflow, complaint system, SEO, and production deployment.

•	Admin: admin/dashboard.php with charts, admin/complaints.php, admin/payments.php with commission tracking
•	KYC: kyc_documents upload in owner/profile.php, admin review in admin/users.php
•	SEO: meta tags, Open Graph, schema.org structured data on pg-detail.php
•	Performance: image compression in upload_handler.php via PHP GD, DB query optimization, index verification
•	Security audit: CSRF tokens on all forms, XSS audit, SQL injection test, file upload security test
•	cPanel deploy: upload, schema import, config update, domain DNS, SSL certificate (Let's Encrypt via cPanel)
•	User testing with actual MUJ students and local PG owners

13.4 Phase 4 — Post-Launch Enhancements (Month 4+)
•	Mobile App (React Native or Flutter) connecting to a PHP REST API layer added to the existing codebase
•	Advanced search: distance-based sorting using Haversine formula on lat/lng coordinates
•	Virtual tour: 360° photo support using A-Frame.io embedded in pg-detail.php
•	Rent reminder: automated monthly email/notification reminders for active tenants
•	Analytics dashboard for owners: page views, booking conversion rate, review sentiment
•	WhatsApp notification integration for booking updates (Twilio or WhatsApp Business API)
 
14. Glossary
Term	Definition
PG	Paying Guest — a rented room in a residential property typically targeting students or working professionals
MUJ	Manipal University Jaipur — the university campus around which all PG listings are geo-anchored
KYC	Know Your Customer — identity verification process requiring owners to upload government-issued ID proof
XAMPP	Cross-platform Apache + MySQL + PHP + Perl bundle used for local development
cPanel	Web hosting control panel used by most Indian shared hosting providers (Hostinger, GoDaddy, Bluehost India)
PDO	PHP Data Objects — PHP extension for database access using prepared statements
Single-file-per-page	Architecture pattern where each page's PHP logic and HTML output coexist in one .php file
Soft Delete	Setting is_deleted=1 instead of actually removing a database row, preserving data for audit/recovery
Denormalized Field	Fields like avg_rating and total_reviews stored directly on pg_listings for query performance, kept in sync via triggers or PHP on INSERT/UPDATE of reviews
CSRF Token	Cross-Site Request Forgery token — a unique per-session value in forms that prevents unauthorized form submissions from external sites
slug	URL-friendly string derived from PG title — e.g. "shanti-girls-pg-near-muj-gate-2" — used in SEO-friendly URLs
Instant Booking	Booking type where owner pre-approves all requests — booking is auto-confirmed without owner review
Commission	Percentage of payment amount retained by MUJSTAYS platform before crediting owner earnings
Occupancy Rate	Percentage of a PG's total beds currently booked — (filled_beds / total_beds) * 100

