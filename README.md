# Debre Tabor Gebeya — Localized Digital Marketplace

Welcome to **Debre Tabor Gebeya**, a production-ready, full-featured e-commerce platform designed to bring modern digital commerce into a localized community context. Built with native PHP and MySQL, this project simulates a highly practical, robust multi-user marketplace that prioritizes clean logic, structural security, and seamless financial integrations tailored for local and international context.

I developed this platform to solve real-world transaction hurdles, building out complex multi-tier workflows, a secure custom messaging channel, and modern digital payment integrations entirely from scratch.

---

## 🚀 Key Highlights & Architectural Features

Rather than relying on heavy frameworks, this platform is built with structural clarity and low-overhead processing, focusing heavily on secure user workflows and backend reliability:

* **Multi-Role Ecosystem:** Separate, specialized control spaces for **Customers** (product discovery, dynamic cart, order tracking), **Sellers** (storefront management, real-time stock dashboards, image uploads), and **Admins** (system configuration, global audit logs, sales analytics).
* **Dual-Gateway Payment Engine:** Features complete backend setup and integration endpoints for international transactions via **Stripe API** alongside localized, real-world digital payment processing via **Telebirr Merchant Integration**.
* **Direct Commerce Messaging:** A built-in, real-time communications channel that allows buyers and sellers to discuss order specifications directly, eliminating external dependencies.
* **Intelligent Inventory Tracking:** Dynamic stock monitoring that triggers low-stock flags, helps prevent overselling, and maintains structural audit logs.
* **Robust Security Groundwork:** Engineered from the ground up to counter web vulnerabilities through prepared statements, strict XSS filtration, and enterprise-grade hashing.

---

## 🛠️ The Technical Stack

* **Backend Engineering:** PHP 7.4+ (Pure OOP / Procedural hybrid utilizing clean architectural layout)
* **Database Management:** MySQL 5.7+ (Relational schema designed with explicit foreign key constraints and indexed querying)
* **Frontend Interface:** Semantic HTML5, Custom CSS3 Layouts, and Vanilla JavaScript (No heavy frameworks—optimized for fast load speeds and responsive execution)
* **Integrations:** Telebirr Sandbox API, Stripe Payment Gateway, and secure SMTP Mail handling.

---

## ⚙️ Installation & Local Environment Setup

Follow these steps to deploy and run the localized marketplace on your local machine.

### Prerequisites
* PHP 7.4 or higher installed.
* MySQL 5.7 or higher database server.
* A local web server environment (Apache via XAMPP / WAMP with `mod_rewrite` activated).

### Deployment Steps

1.  **Clone the Repository**
    ```bash
    git clone [https://github.com/zelalem515/debre-tabor-gebeya.git](https://github.com/zelalem515/debre-tabor-gebeya.git)
    cd debre-tabor-gebeya
    ```

2.  **Configure Database Connections**
    * Open your database management tool (e.g., phpMyAdmin) and create a new database named `debre_tabor_gebeya`.
    * Navigate to `php/db.php` in your code editor and update the connection parameters to match your local database environment credentials:
        ```php
        // php/db.php example config
        $host = 'localhost';
        $db   = 'debre_tabor_gebeya';
        $user = 'your_username';
        $pass = 'your_password';
        ```

3.  **Import Database Tables & Schema**
    * Directly import the provided `.sql` database script inside the `database/` folder using your phpMyAdmin dashboard, or execute the built-in migration script through your terminal:
        ```bash
        php database/run_migrations.php
        ```

4.  **Set Up Secure Environment Configuration**
    * Create the following localized credential files inside your project structure. *(Note: Ensure these remain local and are never committed to your public Git history).*
    * `config/stripe.php` — Add your private/public Stripe API development keys.
    * `config/email.php` — Setup your SMTP server variables for transactional emails.
    * `php/telebirr-token.php` — Input your official Telebirr Sandbox developer credentials.

5.  **Access the Platform Hubs**
    Once your web server is active, access the independent platform views via your browser:
    * **Main Customer Marketplace:** `http://localhost/debre-tabor-gebeya/`
    * **Merchant Operations Desk:** `http://localhost/debre-tabor-gebeya/seller/`
    * **System Administrative Center:** `http://localhost/debre-tabor-gebeya/admin/`

---

## 📂 Project Directory Structure

The repository is modularly organized to separate functional logic, assets, and views:

```text
├── customer/           # Customer storefront interface and checkout flows
├── seller/             # Merchant inventory portals and product dashboards
├── admin/              # Global system overview, analytics, and user moderation
├── api/                # Functional server endpoints processing async requests (Carts, Live Chats)
├── php/                # Core backend engines, database connectors, and security services
├── config/             # Localized integration tokens and server keys (Keep restricted)
├── database/           # Relational schema blueprints and historical SQL migrations
├── css/                # Custom, responsive application design layouts
├── js/                 # Pure JavaScript handling dynamic updates and client interactions
└── includes/           # Reusable user interface components (Global Headers, Footers, Navbars)
```
## 🔒 Security Architectures Implemented
```This project was developed with a defensive programming mindset, enforcing data sanitation and system protection rules at every entry point:

Defeating SQL Injection: Absolute reliance on PDO (PHP Data Objects) and Prepared Statements across all database interactions. Raw parameters are never directly concatenated into queries.

Cryptographic Passwords: Passwords are never saved in plain text. Secure storage is enforced utilizing PHP’s native password_hash() implementing the robust Bcrypt algorithm.

State & Session Integrity: Active protection against session hijacking and fixation attacks, leveraging strictly managed native PHP session workflows.

Cross-Site Scripting (XSS) Mitigation: Complete validation, filtering, and strict sanitization routines executed on all incoming global arrays ($_POST, $_GET) prior to browser rendering.
```
👨‍💻 Developer & Project Context
Lead Developer: Zelalem Birhan Geta

Institutional Background: Debre Tabor University (DTU) — Gafat Institute of Technology, Department of Information Technology

Professional Target: Specialized in Full-Stack Engineering (MERN / Native PHP & JS Systems)

Direct Contact / Feedback: ```Connect with me via Telegram: @zedo1940```

📄 License
This software ecosystem is open-source and distributed under the MIT License. Feel free to explore, tweak, and expand the code base.
