# Debre Tabor Gebeya - E-Commerce Platform

A full-featured e-commerce platform built with PHP and MySQL, designed to simulate a localized digital marketplace. It features multi-user workflows (customers, sellers, admins), real-time messaging, payment processing, and backend inventory management.

## Features

- **User Management**: Customer registration, seller portals, and an admin control dashboard.
- **Product Management**: Allows sellers to list items with image uploads and category tags.
- **Shopping Cart**: Real-time add/remove functionality with a smooth checkout flow.
- **Payment Processing**: Integration setups for Stripe and Telebirr payment gateways.
- **Messaging System**: Direct communication channel between customers and sellers.
- **Notifications**: Automatic order status updates and system alerts.
- **Inventory Management**: Stock tracking, low-stock flags, and simple audit logs.
- **Admin Dashboard**: Centralized user control, sales reporting, and order tracking.

## Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Payment Gateways**: Stripe API, Telebirr API Integration
- **Email Handling**: SMTP configuration

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/XAMPP with mod_rewrite enabled)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone [https://github.com/zelalem515/debre-tabor-gebeya.git](https://github.com/zelalem515/debre-tabor-gebeya.git)
   cd debre-tabor-gebeya
Configure Database Connection

Create a new MySQL database in phpMyAdmin named debre_tabor_gebeya.

Open php/db.php and update your database credentials (username, password).

Import Database Schema

Import the provided SQL file directly via phpMyAdmin, or run the migration script:

Bash
php database/run_migrations.php
Configure Environment Keys
Create the following local configuration files (ensure these are ignored in your .gitignore):

config/stripe.php (Stripe API Keys)

config/email.php (SMTP Server Settings)

php/telebirr-token.php (Telebirr Merchant Credentials)

Access the Local App

Customer Portal: http://localhost/debre-tabor-gebeya/

Admin Panel: http://localhost/debre-tabor-gebeya/admin/

Seller Panel: http://localhost/debre-tabor-gebeya/seller/

Project Structure
├── admin/              # Admin dashboard panels
├── api/                # Functional API endpoints (Cart, Messages, etc.)
├── customer/           # Customer interface pages
├── seller/             # Seller inventory and dashboard pages
├── php/                # Core backend logic, database connections, and helper services
├── config/             # Local API configuration files (kept secure)
├── database/           # Database schemas and migration files
├── css/                # Custom application stylesheets
├── js/                 # Frontend JavaScript logic
└── includes/           # Reusable templates (header, footer, nav)
Security Implementation
SQL Injection Prevention: All SQL queries run through PDO/Prepared Statements.

Password Security: Safe credentials storage using password_hash() with bcrypt.

Session Protection: Handled via secure built-in PHP session management.

Input Validation: Strict sanitization on all POST/GET requests to block XSS.

Developer & Maintainer
Developer: Zelalem Birhan Geta

Institution: Debre Tabor University (DTU) - IT Department

Connect / Support: Telegram (@zedo1940)

License
This project is open-source and available under the MIT License.
