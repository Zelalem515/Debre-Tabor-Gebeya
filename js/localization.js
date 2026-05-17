/**
 
 */

// Get current language from session/localStorage
function getCurrentLanguage() {
    return localStorage.getItem('language') || 'en';
}

// Set language preference
function setLanguage(lang) {
    localStorage.setItem('language', lang);
}

// Change language
function changeLanguage(lang) {
    setLanguage(lang);
    
    // Send AJAX request to set language in session
    fetch('api/set-language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'language=' + lang
    }).then(response => {
        // Reload page to apply language change
        location.reload();
    }).catch(error => {
        console.error('Error changing language:', error);
    });
}

// Initialize language on page load
document.addEventListener('DOMContentLoaded', function() {
    const currentLang = getCurrentLanguage();
    const languageSelector = document.getElementById('language-selector');
    
    if (languageSelector) {
        languageSelector.value = currentLang;
    }
});
