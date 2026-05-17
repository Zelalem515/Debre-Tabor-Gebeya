# Debre Tabor Gebeya - E-Commerce Platform

A full-featured e-commerce platform built with PHP and MySQL, featuring multi-user support (customers, sellers, admins), real-time messaging, payment processing, and inventory management.

## Features

- **User Management**: Customer registration, seller accounts, admin dashboard
- **Product Management**: Sellers can list products with images and categories
- **Shopping Cart**: Add/remove items, checkout process
- **Payment Processing**: Stripe and Telebirr payment gateway integration
- **Messaging System**: Real-time communication between customers and sellers
- **Notifications**: Order updates and system notifications
- **Inventory Management**: Stock tracking, low-stock alerts, audit trails
- **Admin Dashboard**: User management, order tracking, reports
- **Multi-language Support**: Localization support
- **Order Management**: Order tracking, status updates, cancellation

## Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Payment Gateways**: Stripe, Telebirr
- **Email**: SMTP configuration

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (optional, for dependency management)
- Web server (Apache with mod_rewrite enabled)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/zelalem515/debre-tabor-gebeya.git
   cd debre-tabor-gebeya
   ```

2. **Configure database connection**
   - Edit `php/db.php` with your database credentials
   - Create a new MySQL database

3. **Run database migrations**
   ```bash
   # Import the main schema
   /debre_tabor_gebeya.sql
   
   # Or run migrations
   php database/run_migrations.php
   ```

4. **Configure payment gateways**
   - Create `config/stripe.php` with your Stripe API keys
   - Create `config/email.php` with your SMTP settings
   - Create `php/telebirr-token.php` with Telebirr credentials

5. **Set up web server**
   - Point document root to project directory
   - Ensure `.htaccess` is enabled for URL rewriting

6. **Access the application**
   - Customer: `http://localhost/`
   - Admin: `http://localhost/admin/`
   - Seller: `http://localhost/seller/`

## Configuration Files

Create these files in your local environment (not committed to repo):

### `config/stripe.php`
```php
<?php
define('STRIPE_PUBLIC_KEY', 'your_public_key');
define('STRIPE_SECRET_KEY', 'your_secret_key');
?>
```

### `config/email.php`
```php
<?php
define('SMTP_HOST', 'your_smtp_host');
define('SMTP_USER', 'your_email');
define('SMTP_PASS', 'your_password');
define('SMTP_PORT', 587);
?>
```

### `php/telebirr-token.php`
```php
<?php
define('TELEBIRR_API_KEY', 'your_api_key');
define('TELEBIRR_MERCHANT_ID', 'your_merchant_id');
?>
```

## Project Structure

```
├── admin/              # Admin dashboard pages
├── api/                # API endpoints
├── customer/           # Customer-facing pages
├── seller/             # Seller dashboard pages
├── php/                # Core PHP logic and services
├── config/             # Configuration (not in repo)
├── database/           # Database schemas and migrations
├── css/                # Stylesheets
├── js/                 # JavaScript files
├── includes/           # Shared templates
└── tests/              # Property-based tests
```

## Database Schema

Key tables:
- `users` - User accounts (customers, sellers, admins)
- `products` - Product listings
- `categories` - Product categories
- `cart` - Shopping cart items
- `orders` - Customer orders
- `order_items` - Items in orders
- `payments` - Payment records
- `messages` - Messaging system
- `notifications` - User notifications
- `inventory_audit` - Stock change tracking

## API Endpoints

### Cart
- `POST /api/add-to-cart.php` - Add item to cart
- `POST /api/remove-from-cart.php` - Remove item from cart
- `POST /api/update-cart.php` - Update cart quantities
- `GET /api/get-cart-count.php` - Get cart item count

### Messaging
- `POST /api/messages/send.php` - Send message
- `GET /api/messages/get-messages.php` - Get conversation messages
- `GET /api/messages/get-conversations.php` - List conversations
- `POST /api/messages/mark-read.php` - Mark messages as read

### Notifications
- `GET /api/notifications/get.php` - Get notifications
- `POST /api/notifications/mark-read.php` - Mark notification as read

### Payments
- `POST /api/webhooks/stripe.php` - Stripe webhook handler

## Testing

Run property-based tests to verify system correctness:

```bash
php tests/run_inventory_tests.php
php tests/run_payment_tests.php
php tests/test_auth_properties.php
php tests/test_cart_properties.php
```

## Security Considerations

- All database queries use prepared statements to prevent SQL injection
- Passwords are hashed using bcrypt
- Session management with secure cookies
- CSRF protection on forms
- Input validation on all user inputs
- Payment data handled securely through Stripe/Telebirr

## Contributing

1. Create a feature branch (`git checkout -b feature/amazing-feature`)
2. Commit changes (`git commit -m 'Add amazing feature'`)
3. Push to branch (`git push origin feature/amazing-feature`)
4. Open a Pull Request

## License

This project is licensed under the MIT License.

## Support

For issues, questions, or help with the project, please contact:

**Developer**: Zelalem Birhan Geta  
**Institution**: Debre Tabor University (DTU) - IT Student  
**Contact**: [Telegram](https://t.me/zedo1940)

Feel free to reach out for:
- Bug reports and issues
- Feature requests
- Implementation help
- General inquiries

## Authors

- **Zelalem Birhan Geta** - Lead Developer & Maintainer
  - DTU IT Student
  - Contact: https://t.me/zedo1940

---

**Note**: This is a production e-commerce platform. Ensure all security best practices are followed before deploying to production.
