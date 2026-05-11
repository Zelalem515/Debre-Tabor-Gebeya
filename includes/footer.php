<?php
/**
 * Footer Component
 */

$current_language = get_language();
?>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3><?php echo translate('site_name', $current_language); ?></h3>
            <p><?php echo translate('hero_subtitle', $current_language); ?></p>
        </div>
        
        <div class="footer-section">
            <h4><?php echo translate('categories', $current_language); ?></h4>
            <ul>
                <li><a href="#">Electronics</a></li>
                <li><a href="#">Clothing</a></li>
                <li><a href="#">Books</a></li>
                <li><a href="#">Home & Garden</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4><?php echo translate('info', $current_language); ?></h4>
            <ul>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4><?php echo translate('language', $current_language); ?></h4>
            <select onchange="changeLanguage(this.value)">
                <option value="en" <?php echo $current_language === 'en' ? 'selected' : ''; ?>>English</option>
                <option value="am" <?php echo $current_language === 'am' ? 'selected' : ''; ?>>አማርኛ</option>
            </select>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; 2024 DEBRETABOR GEBEYA. All rights reserved.</p>
    </div>
</footer>

<script>
    function changeLanguage(lang) {
        fetch('<?php echo SITE_URL; ?>api/set-language.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'language=' + lang
        }).then(response => {
            location.reload();
        });
    }
</script>
