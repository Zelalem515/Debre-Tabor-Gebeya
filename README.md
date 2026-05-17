<div align="center">
  <h1>🛍️ Debre Tabor Gebeya</h1>
  <p><em>A full-featured multi-vendor E-Commerce Platform .</em></p>
  
  ![PHP](https://img.shields.io/badge/PHP-7.4+-blue?style=for-the-badge&logo=php)
  ![MySQL](https://img.shields.io/badge/MySQL-5.7+-blue?style=for-the-badge&logo=mysql)
  ![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
  ![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
  ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
</div>

---

## 👋 Welcome! 

Welcome to **Debre Tabor Gebeya** (Debre Tabor Market)! This project isn't just another codebase—it's a comprehensive e-commerce platform designed to connect local sellers with customers, providing a seamless and secure shopping experience. 

Whether you're a developer looking to contribute, a user interested in the platform, or an evaluator reviewing my work, this README will guide you through everything you need to know about the project.

---

## 🎯 Project Purpose

As an IT student at Debre Tabor University (DTU), I built this platform to apply theoretical knowledge to a real-world problem. The goal was to build a robust, scalable, and secure system that handles complex business logic like multi-user roles, real-time messaging, and secure online payments using both international (Stripe) and local (Telebirr) gateways.

### 🔍 For Evaluators
If you are evaluating this project, here are the technical highlights that demonstrate my skills and what I've learned:
- **Security First:** Implemented prepared statements to prevent SQL injections, bcrypt for password hashing, CSRF protection, and secure session management.
- **Complex Architecture:** Designed a normalized database schema with role-based access control (Admin, Seller, Customer).
- **Core Integrations:** Successfully integrated third-party payment gateways (Stripe API & Telebirr API).
- **Testing:** Implemented property-based testing (`tests/`) to ensure system reliability, particularly for critical flows like inventory and payment processing.
- **Real-time Features:** Built a real-time messaging system allowing instant communication between buyers and sellers.

---

## ✨ Key Features

### 👥 Multi-Role User Management
* **Customers:** Browse products, manage carts, checkout securely, track orders, and message sellers.
* **Sellers:** Dedicated dashboard to manage inventory, list products with beautiful images, and process incoming orders.
* **Administrators:** Global control panel for overall user management, system monitoring, and generating business reports.

### 🛒 Modern Shopping Experience
* Dynamic shopping cart and a smooth, intuitive checkout flow.
* Real-time stock tracking with low-stock alerts and inventory audit trails.
* Order status tracking (Pending, Processing, Completed, Cancelled).

### 💳 Secure Payments & Communication
* **Payments:** Support for Credit/Debit cards via **Stripe** and local mobile money via **Telebirr**.
* **Messaging:** Built-in chat system allowing customers to communicate directly with sellers for negotiations or questions.
* **System Notifications:** Keeps all users engaged with order updates and system alerts.

---

## 🛠️ Technology Stack

* **Backend Environment:** PHP (7.4+)
* **Database Management:** MySQL (5.7+)
* **Frontend UI:** HTML5, Vanilla CSS3, JavaScript
* **Payment Gateways:** Stripe API, Telebirr API
* **Email & Notifications:** SMTP Configuration

---

## 📸 Screenshots
*<img width="1883" height="947" alt="image" src="https://github.com/user-attachments/assets/ea3f2cf1-155d-4bce-bb40-0f015ea2ca0b" />
*Customer Dashboard 
*<img width="1897" height="925" alt="image" src="https://github.com/user-attachments/assets/32f22d35-5a22-4440-97dd-3b2973d16cf8" />
*Admin Dashboard 
<img width="1887" height="912" alt="image" src="https://github.com/user-attachments/assets/45f83edc-a2e6-4e7f-97e8-1aa9f49d9925" />
Seller Dassboar  ---

## 🚀 Getting Started

Want to run this project locally on your machine or deploy it? Follow these steps!

### Prerequisites
Make sure you have installed:
- PHP >= 7.4
- MySQL >= 5.7
- Web Server (Apache with `mod_rewrite` enabled, e.g., XAMPP/WAMP)

### Installation Steps

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Zelalem515/Debre-Tabor-Gebeya.git
   cd Debre-Tabor-Gebeya
   ```

2. **Database Setup:**
   * Create a new MySQL database named `debre_tabor_gebeya`.
   * Import the database structure from `database/debre_tabor_gebeya.sql`.
   * Update the database credentials in `php/db.php`.

3. **Configure API Keys & Environment Variables:**
   Since API keys should never be pushed to a repository, you will need to create the `config/` directory files yourself with your own credentials:
   
   *Create `config/stripe.php`:*
   ```php
   <?php
   define('STRIPE_PUBLIC_KEY', 'your_stripe_public_key');
   define('STRIPE_SECRET_KEY', 'your_stripe_secret_key');
   ?>
   ```
   
   *Create `config/email.php`:*
   ```php
   <?php
   define('SMTP_HOST', 'your_smtp_host');
   define('SMTP_USER', 'your_email');
   define('SMTP_PASS', 'your_password');
   define('SMTP_PORT', 587);
   ?>
   ```

   *Create `php/telebirr-token.php`:*
   ```php
   <?php
   define('TELEBIRR_API_KEY', 'your_telebirr_api_key');
   define('TELEBIRR_MERCHANT_ID', 'your_merchant_id');
   ?>
   ```

4. **Launch the Application:**
   * Point your Apache server's document root to the project directory.
   * Access the platform in your browser at `http://localhost/` (or your configured local domain).
   * **Admin panel:** `http://localhost/admin/`
   * **Seller panel:** `http://localhost/seller/`

---

## 🧪 Testing

I've included a suite of property-based tests to verify system correctness and catch edge cases. To run them:

```bash
php tests/run_inventory_tests.php
php tests/run_payment_tests.php
php tests/test_auth_properties.php
php tests/test_cart_properties.php
```

---

## 🤝 Contributing

Feedback, bug reports, and pull requests are always welcome! 

1. Fork the project.
2. Create your feature branch (`git checkout -b feature/AmazingFeature`).
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`).
4. Push to the branch (`git push origin feature/AmazingFeature`).
5. Open a Pull Request.

---

## 👨‍💻 About the Developer

Hello! I'm **Zelalem Birhan Geta**, the lead developer behind this project. I'm an IT student at Debre Tabor University (DTU) passionate about web development and building software that solves practical problems for our community.

* 🎓 **Institution:** Debre Tabor University (DTU) 
* 💬 **Let's Connect / Telegram:** [@zedo1940](https://t.me/zedo1940)
* 📧 **Feedback & Support:** I'm always open to discussing this project, receiving feedback from evaluators, or helping out if you have issues running or understanding the codebase. Just reach out via Telegram!

---

<div align="center">
  <p>Made with ❤️ in Debre Tabor</p>
  <p><em>Licensed under the MIT License.</em></p>
</div>
