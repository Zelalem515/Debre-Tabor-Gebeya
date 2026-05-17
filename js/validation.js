/**

 */

// Validate email format
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validate password strength
function validatePassword(password) {
    if (password.length < 8) return false;
    if (!/[A-Z]/.test(password)) return false;
    if (!/[a-z]/.test(password)) return false;
    if (!/[0-9]/.test(password)) return false;
    return true;
}

// Validate phone number
function validatePhone(phone) {
    const re = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/;
    return re.test(phone);
}

// Validate price
function validatePrice(price) {
    return !isNaN(price) && price > 0;
}

// Validate quantity
function validateQuantity(quantity) {
    return !isNaN(quantity) && quantity > 0 && Number.isInteger(Number(quantity));
}

// Validate address
function validateAddress(address) {
    return address && address.trim().length >= 5;
}

// Sanitize input
function sanitizeInput(input) {
    const div = document.createElement('div');
    div.textContent = input;
    return div.innerHTML;
}

// Show field error
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorElement = document.getElementById(fieldId + '-error');
    
    if (field) {
        field.classList.add('error');
    }
    
    if (errorElement) {
        errorElement.textContent = message;
    }
}

// Clear field error
function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    const errorElement = document.getElementById(fieldId + '-error');
    
    if (field) {
        field.classList.remove('error');
    }
    
    if (errorElement) {
        errorElement.textContent = '';
    }
}

// Clear all errors
function clearAllErrors() {
    document.querySelectorAll('.error-message').forEach(el => {
        el.textContent = '';
    });
    
    document.querySelectorAll('.error').forEach(el => {
        el.classList.remove('error');
    });
}

// Add CSS for error state
const style = document.createElement('style');
style.textContent = `
    input.error,
    textarea.error,
    select.error {
        border-color: #e74c3c !important;
        background-color: #fff5f5;
    }
`;
document.head.appendChild(style);
