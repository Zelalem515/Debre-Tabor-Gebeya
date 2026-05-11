<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Localization Module - Complete Multilingual Support
 */

/**
 * Get current language from session
 */
function get_language() {
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }
    return 'en'; // Default to English
}

/**
 * Set language preference
 */
function set_language($lang) {
    if (in_array($lang, ['en', 'am'])) {
        $_SESSION['language'] = $lang;
    }
}

/**
 * Get translated string
 */
function translate($key, $lang = 'en') {
    $translations = get_translations($lang);
    return $translations[$key] ?? $key;
}

/**
 * Get all translations for a language
 */
function get_translations($lang = 'en') {
    $translations = [
        'en' => [
            // Site & Navigation
            'site_name' => 'DEBRETABOR GEBEYA',
            'welcome_to' => 'Welcome to',
            'hero_subtitle' => 'Ethiopian Online Marketplace - Shop with Confidence',
            'browse_products' => 'Browse Products',
            'login' => 'Login',
            'register' => 'Register',
            'logout' => 'Logout',
            
            // Authentication
            'email' => 'Email',
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
            'full_name' => 'Full Name',
            'account_type' => 'Account Type',
            'customer' => 'Customer',
            'seller' => 'Seller',
            'admin' => 'Admin',
            'all_fields_required' => 'All fields are required',
            'passwords_do_not_match' => 'Passwords do not match',
            'registration_successful' => 'Registration successful! Redirecting to login...',
            'password_requirements' => 'At least 8 characters with uppercase, lowercase, and numbers',
            'already_have_account' => 'Already have an account?',
            'dont_have_account' => "Don't have an account?",
            'login_successful' => 'Login successful! Redirecting...',
            'profile_picture' => 'Profile Picture',
            'upload_profile_picture' => 'Upload Profile Picture (Optional)',
            'profile_picture_help' => 'JPG, PNG, GIF - Max 5MB',
            
            // Products
            'featured_products' => 'Featured Products',
            'add_to_cart' => 'Add to Cart',
            'view_details' => 'View Details',
            'price' => 'Price',
            'stock' => 'Stock',
            'in_stock' => 'In Stock',
            'out_of_stock' => 'Out of Stock',
            'product_name' => 'Product Name',
            'description' => 'Description',
            'category' => 'Category',
            'add_product' => 'Add Product',
            'edit_product' => 'Edit Product',
            'delete_product' => 'Delete Product',
            'manage_products' => 'Manage Products',
            'no_products_yet' => 'No products yet',
            'add_your_first_product' => 'Add your first product',
            'no_products_found' => 'No products found',
            'no_products' => 'No products',
            'recent_products' => 'Recent Products',
            'product_added_to_cart' => 'Product added to cart!',
            'error_adding_to_cart' => 'Error adding to cart',
            
            // Cart & Checkout
            'cart' => 'Cart',
            'checkout' => 'Checkout',
            'total' => 'Total',
            'quantity' => 'Quantity',
            'remove' => 'Remove',
            'continue_shopping' => 'Continue Shopping',
            'proceed_to_checkout' => 'Proceed to Checkout',
            'empty_cart' => 'Your cart is empty',
            'cart_summary' => 'Cart Summary',
            'subtotal' => 'Subtotal',
            'shipping' => 'Shipping',
            'tax' => 'Tax',
            'grand_total' => 'Grand Total',
            
            // Payment
            'payment' => 'Payment',
            'select_payment_method' => 'Select Payment Method',
            'payment_method' => 'Payment Method',
            'delivery_address' => 'Delivery Address',
            'complete_payment' => 'Complete Payment',
            'payment_successful' => 'Payment Successful',
            'payment_confirmed' => 'Payment Confirmed!',
            'payment_failed' => 'Payment Failed',
            'transaction_id' => 'Transaction ID',
            'order_summary' => 'Order Summary',
            'telebirr' => 'Telebirr',
            'cbe_birr' => 'CBE Birr',
            'bank_of_abyssinia' => 'Bank of Abyssinia',
            'cash_on_delivery' => 'Cash on Delivery',
            'phone_number' => 'Phone Number',
            'account_number' => 'Account Number',
            'pin' => 'PIN',
            'view_orders' => 'View Orders',
            'continue_shopping_btn' => 'Continue Shopping',
            
            // Orders
            'order_history' => 'Order History',
            'my_orders' => 'My Orders',
            'order_id' => 'Order ID',
            'order_date' => 'Order Date',
            'order_status' => 'Order Status',
            'status' => 'Status',
            'pending' => 'Pending',
            'paid' => 'Paid',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'created_date' => 'Created Date',
            'total_amount' => 'Total Amount',
            'no_orders' => 'No orders yet',
            
            // Dashboard
            'dashboard' => 'Dashboard',
            'welcome' => 'Welcome',
            'total_products' => 'Total Products',
            'total_orders' => 'Total Orders',
            'total_sales' => 'Total Sales',
            'total_customers' => 'Total Customers',
            'recent_activity' => 'Recent Activity',
            
            // Navigation
            'products' => 'Products',
            'categories' => 'Categories',
            'orders' => 'Orders',
            'users' => 'Users',
            'reports' => 'Reports',
            'settings' => 'Settings',
            'language' => 'Language',
            'english' => 'English',
            'amharic' => 'Amharic',
            
            // Search & Filter
            'search' => 'Search',
            'filter' => 'Filter',
            'sort' => 'Sort',
            'all_categories' => 'All Categories',
            'newest' => 'Newest',
            'price_low_to_high' => 'Price: Low to High',
            'price_high_to_low' => 'Price: High to Low',
            'best_selling' => 'Best Selling',
            'most_popular' => 'Most Popular',
            
            // Messages
            'messages' => 'Messages',
            'no_conversations' => 'No conversations yet',
            'start_conversation_by_contacting_seller' => 'Start a conversation by contacting a seller',
            'customers_will_message_you' => 'Customers will message you here',
            'select_conversation' => 'Select a conversation to start messaging',
            'type_message' => 'Type your message...',
            'send' => 'Send',
            'message_cannot_be_empty' => 'Message cannot be empty',
            'message_sent' => 'Message sent successfully',
            'message_deleted' => 'Message deleted',
            'no_messages' => 'No messages',
            'conversation' => 'Conversation',
            'reply' => 'Reply',
            
            // Pagination
            'first' => 'First',
            'previous' => 'Previous',
            'next' => 'Next',
            'last' => 'Last',
            'page' => 'Page',
            'of' => 'of',
            
            // Status Messages
            'error' => 'Error',
            'success' => 'Success',
            'warning' => 'Warning',
            'info' => 'Information',
            'test_credentials' => 'Test Credentials',
            
            // Actions
            'actions' => 'Actions',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'submit' => 'Submit',
            'back' => 'Back',
            'close' => 'Close',
            'confirm' => 'Confirm',
            'yes' => 'Yes',
            'no' => 'No',
            
            // Seller
            'seller_dashboard' => 'Seller Dashboard',
            'seller_profile' => 'Seller Profile',
            'seller_products' => 'My Products',
            'seller_orders' => 'My Orders',
            'seller_messages' => 'Messages',
            'seller_ratings' => 'Ratings',
            'seller_reviews' => 'Reviews',
            
            // Admin
            'admin_dashboard' => 'Admin Dashboard',
            'manage_users' => 'Manage Users',
            'manage_categories' => 'Manage Categories',
            'manage_orders' => 'Manage Orders',
            'view_reports' => 'View Reports',
            'system_settings' => 'System Settings',
            
            // Common
            'loading' => 'Loading...',
            'please_wait' => 'Please wait...',
            'no_data' => 'No data available',
            'error_occurred' => 'An error occurred',
            'try_again' => 'Try Again',
            'go_home' => 'Go Home',
            'contact_support' => 'Contact Support',
            'all_rights_reserved' => 'All rights reserved',
            'ethiopian_ecommerce_excellence' => 'Ethiopian E-Commerce Excellence',
        ],
        'am' => [
            // Site & Navigation
            'site_name' => 'ደብረታቦር ገበያ',
            'welcome_to' => 'ወደ ተመልካች',
            'hero_subtitle' => 'ኢትዮጵያዊ ኦንላይን ገበያ - በእምነት ይገዙ',
            'browse_products' => 'ምርቶችን ያስሱ',
            'login' => 'ግባ',
            'register' => 'ተመዝገብ',
            'logout' => 'ውጣ',
            
            // Authentication
            'email' => 'ኢሜይል',
            'password' => 'ይለፍ ቃል',
            'confirm_password' => 'ይለፍ ቃል ያረጋግጡ',
            'full_name' => 'ሙሉ ስም',
            'account_type' => 'የሂሳብ ዓይነት',
            'customer' => 'ደንበኛ',
            'seller' => 'ሻጭ',
            'admin' => 'አስተዳዳሪ',
            'all_fields_required' => 'ሁሉም መስኮች ያስፈልጋሉ',
            'passwords_do_not_match' => 'ይለፍ ቃሎች አይዛመዱም',
            'registration_successful' => 'ምዝገባ ተሳክቷል! ወደ ግባ እንደገና ይመሩ...',
            'password_requirements' => 'ቢያንስ 8 ቁምፊዎች ከላይ ፊደል፣ ታች ፊደል እና ቁጥሮች ያሉ',
            'already_have_account' => 'ቀድሞ ሂሳብ አለዎት?',
            'dont_have_account' => 'ሂሳብ የለዎት?',
            'login_successful' => 'ግባ ተሳክቷል! እንደገና ይመሩ...',
            'profile_picture' => 'የመገለጫ ሥዕል',
            'upload_profile_picture' => 'የመገለጫ ሥዕል ይጫኑ (ተራ)',
            'profile_picture_help' => 'JPG, PNG, GIF - ከ 5MB በታች',
            
            // Products
            'featured_products' => 'ታዋቂ ምርቶች',
            'add_to_cart' => 'ወደ ሳጥን ጨምር',
            'view_details' => 'ዝርዝሮችን ይመልከቱ',
            'price' => 'ዋጋ',
            'stock' => 'ክምችት',
            'in_stock' => 'በክምችት ውስጥ',
            'out_of_stock' => 'ክምችት ውጭ',
            'product_name' => 'የምርት ስም',
            'description' => 'መግለጫ',
            'category' => 'ምድብ',
            'add_product' => 'ምርት ጨምር',
            'edit_product' => 'ምርት ያርትዑ',
            'delete_product' => 'ምርት ሰርዝ',
            'manage_products' => 'ምርቶችን ያስተዳድሩ',
            'no_products_yet' => 'ገና ምርቶች የሉም',
            'add_your_first_product' => 'የመጀመሪያ ምርትዎን ጨምሩ',
            'no_products_found' => 'ምርቶች አልተገኙም',
            'no_products' => 'ምርቶች የሉም',
            'recent_products' => 'ቅርብ ምርቶች',
            'product_added_to_cart' => 'ምርት ወደ ሳጥን ታክሏል!',
            'error_adding_to_cart' => 'ምርት ወደ ሳጥን ሲጨምር ስህተት',
            
            // Cart & Checkout
            'cart' => 'ሳጥን',
            'checkout' => 'ወጣ',
            'total' => 'ጠቅላላ',
            'quantity' => 'ብዛት',
            'remove' => 'ያስወግዱ',
            'continue_shopping' => 'ገዝ ቀጥል',
            'proceed_to_checkout' => 'ወደ ወጣ ይሂዱ',
            'empty_cart' => 'ሳጥንዎ ባዶ ነው',
            'cart_summary' => 'የሳጥን ማጠቃለያ',
            'subtotal' => 'ንዑስ ጠቅላላ',
            'shipping' => 'ማጓጓዣ',
            'tax' => 'ታክስ',
            'grand_total' => 'ሙሉ ጠቅላላ',
            
            // Payment
            'payment' => 'ክፍያ',
            'select_payment_method' => 'የክፍያ ዘዴ ይምረጡ',
            'payment_method' => 'የክፍያ ዘዴ',
            'delivery_address' => 'የመላኪያ አድራሻ',
            'complete_payment' => 'ክፍያ ይጨርሱ',
            'payment_successful' => 'ክፍያ ተሳክቷል',
            'payment_confirmed' => 'ክፍያ ተረጋግጧል!',
            'payment_failed' => 'ክፍያ ወድቋል',
            'transaction_id' => 'የግብይት ID',
            'order_summary' => 'የትዕዛዝ ማጠቃለያ',
            'telebirr' => 'ቴሌቢር',
            'cbe_birr' => 'CBE ብር',
            'bank_of_abyssinia' => 'የአቢሲንያ ባንክ',
            'cash_on_delivery' => 'ገንዘብ በመላኪያ ጊዜ',
            'phone_number' => 'ስልክ ቁጥር',
            'account_number' => 'የሂሳብ ቁጥር',
            'pin' => 'PIN',
            'view_orders' => 'ትዕዛዞችን ይመልከቱ',
            'continue_shopping_btn' => 'ገዝ ቀጥል',
            
            // Orders
            'order_history' => 'የትዕዛዝ ታሪክ',
            'my_orders' => 'ትዕዛዞቼ',
            'order_id' => 'የትዕዛዝ ID',
            'order_date' => 'የትዕዛዝ ቀን',
            'order_status' => 'የትዕዛዝ ሁኔታ',
            'status' => 'ሁኔታ',
            'pending' => 'በመጠባበቅ ላይ',
            'paid' => 'ተከፍሏል',
            'shipped' => 'ተልኳል',
            'delivered' => 'ተሰጥቷል',
            'cancelled' => 'ተሰርዟል',
            'created_date' => 'የተፈጠረበት ቀን',
            'total_amount' => 'ጠቅላላ መጠን',
            'no_orders' => 'ገና ትዕዛዞች የሉም',
            
            // Dashboard
            'dashboard' => 'ዳሽቦርድ',
            'welcome' => 'ተመልካች',
            'total_products' => 'ጠቅላላ ምርቶች',
            'total_orders' => 'ጠቅላላ ትዕዛዞች',
            'total_sales' => 'ጠቅላላ ሽያጮች',
            'total_customers' => 'ጠቅላላ ደንበኞች',
            'recent_activity' => 'ቅርብ ተግባር',
            
            // Navigation
            'products' => 'ምርቶች',
            'categories' => 'ምድቦች',
            'orders' => 'ትዕዛዞች',
            'users' => 'ተጠቃሚዎች',
            'reports' => 'ሪፖርቶች',
            'settings' => 'ቅንብሮች',
            'language' => 'ቋንቋ',
            'english' => 'English',
            'amharic' => 'አማርኛ',
            
            // Search & Filter
            'search' => 'ፈልግ',
            'filter' => 'ማጣሪያ',
            'sort' => 'ደርድር',
            'all_categories' => 'ሁሉም ምድቦች',
            'newest' => 'ሰብአዊ',
            'price_low_to_high' => 'ዋጋ: ዝቅተኛ ወደ ከፍተኛ',
            'price_high_to_low' => 'ዋጋ: ከፍተኛ ወደ ዝቅተኛ',
            'best_selling' => 'በጣም ይሸጣል',
            'most_popular' => 'በጣም ታዋቂ',
            
            // Messages
            'messages' => 'መልዕክቶች',
            'no_conversations' => 'ገና ምንም ውይይቶች የሉም',
            'start_conversation_by_contacting_seller' => 'ሻጩን በማነጋገር ውይይት ይጀምሩ',
            'customers_will_message_you' => 'ደንበኞች እዚህ ይላክልዎታል',
            'select_conversation' => 'መልዕክት ለመጀመር ውይይት ይምረጡ',
            'type_message' => 'መልዕክትዎን ይተይቡ...',
            'send' => 'ላክ',
            'message_cannot_be_empty' => 'መልዕክት ባዶ ሊሆን አይችልም',
            'message_sent' => 'መልዕክት በተሳካ ሁኔታ ተልኳል',
            'message_deleted' => 'መልዕክት ተሰርዟል',
            'no_messages' => 'ምንም መልዕክቶች የሉም',
            'conversation' => 'ውይይት',
            'reply' => 'መልስ',
            
            // Pagination
            'first' => 'መጀመሪያ',
            'previous' => 'ቀዳሚ',
            'next' => 'ቀጣይ',
            'last' => 'ሌላ',
            'page' => 'ገጽ',
            'of' => 'ከ',
            
            // Status Messages
            'error' => 'ስህተት',
            'success' => 'ስኬት',
            'warning' => 'ማስጠንቀቂያ',
            'info' => 'መረጃ',
            'test_credentials' => 'የሙከራ ምስክር ወረቀቶች',
            
            // Actions
            'actions' => 'ተግባራት',
            'edit' => 'ያርትዑ',
            'delete' => 'ሰርዝ',
            'save' => 'ያስቀምጡ',
            'cancel' => 'ይቅር',
            'submit' => 'ያስገቡ',
            'back' => 'ወደ ኋላ',
            'close' => 'ዝጋ',
            'confirm' => 'ያረጋግጡ',
            'yes' => 'አዎ',
            'no' => 'አይ',
            
            // Seller
            'seller_dashboard' => 'የሻጩ ዳሽቦርድ',
            'seller_profile' => 'የሻጩ መገለጫ',
            'seller_products' => 'ሞያዬ ምርቶች',
            'seller_orders' => 'ሞያዬ ትዕዛዞች',
            'seller_messages' => 'መልዕክቶች',
            'seller_ratings' => 'ደረጃዎች',
            'seller_reviews' => 'ግምገማዎች',
            
            // Admin
            'admin_dashboard' => 'የአስተዳዳሪ ዳሽቦርድ',
            'manage_users' => 'ተጠቃሚዎችን ያስተዳድሩ',
            'manage_categories' => 'ምድቦችን ያስተዳድሩ',
            'manage_orders' => 'ትዕዛዞችን ያስተዳድሩ',
            'view_reports' => 'ሪፖርቶችን ይመልከቱ',
            'system_settings' => 'የስርዓት ቅንብሮች',
            
            // Common
            'loading' => 'በመጫን ላይ...',
            'please_wait' => 'እባክዎ ይጠብቁ...',
            'no_data' => 'ምንም ውሂብ አይገኝም',
            'error_occurred' => 'ስህተት ተከስቷል',
            'try_again' => 'እንደገና ሞክሩ',
            'go_home' => 'ወደ ቤት ይሂዱ',
            'contact_support' => 'ድጋፍ ያነጋግሩ',
            'all_rights_reserved' => 'ሁሉም መብቶች የተጠበቁ ናቸው',
            'ethiopian_ecommerce_excellence' => 'ኢትዮጵያዊ ኢ-ኮሜርስ ምርጥነት',
        ]
    ];
    
    return $translations[$lang] ?? $translations['en'];
}
?>
