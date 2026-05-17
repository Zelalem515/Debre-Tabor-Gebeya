/**

 */

// Utility function to show notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '80px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.maxWidth = '400px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Utility function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-ET', {
        style: 'currency',
        currency: 'ETB'
    }).format(amount);
}

// Utility function to format date
function formatDate(date) {
    return new Intl.DateTimeFormat('en-ET', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(new Date(date));
}

// Load featured products on homepage
document.addEventListener('DOMContentLoaded', function() {
    const featuredProductsContainer = document.getElementById('featured-products');
    
    if (featuredProductsContainer) {
        loadFeaturedProducts();
    }
});

function loadFeaturedProducts() {
    fetch('api/get-featured-products.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.products) {
                displayProducts(data.products, 'featured-products');
            }
        })
        .catch(error => {
            console.error('Error loading featured products:', error);
        });
}

function displayProducts(products, containerId) {
    const container = document.getElementById(containerId);
    
    if (!container) return;
    
    container.innerHTML = '';
    
    products.forEach(product => {
        const productCard = createProductCard(product);
        container.appendChild(productCard);
    });
}

function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    
    const inStock = product.stock_quantity > 0;
    const stockClass = inStock ? 'stock-in' : 'stock-out';
    const stockText = inStock ? 'In Stock' : 'Out of Stock';
    
    card.innerHTML = `
        <div class="product-image">
            ${product.image_path ? 
                `<img src="${product.image_path}" alt="${product.name}">` :
                `<div class="product-image-placeholder">📦</div>`
            }
        </div>
        <div class="product-info">
            <h3 class="product-name">${product.name}</h3>
            <p class="product-description">${product.description.substring(0, 100)}...</p>
            <div class="product-price">${formatCurrency(product.price)}</div>
            <div class="product-stock">
                <span class="stock-status ${stockClass}">${stockText}</span>
            </div>
            <div class="product-actions">
                <a href="customer/product-detail.php?id=${product.id}" class="btn btn-secondary btn-sm">View Details</a>
                ${inStock ? 
                    `<button class="btn btn-primary btn-sm" onclick="addToCart(${product.id})">Add to Cart</button>` :
                    `<button class="btn btn-primary btn-sm" disabled>Out of Stock</button>`
                }
            </div>
        </div>
    `;
    
    return card;
}

function addToCart(productId) {
    fetch('api/add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&quantity=1'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart!', 'success');
            updateCartCount();
        } else {
            showNotification(data.message || 'Error adding to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding to cart', 'error');
    });
}

function updateCartCount() {
    fetch('api/get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartIcon = document.querySelector('.cart-count');
            if (cartIcon) {
                cartIcon.textContent = data.count || 0;
            }
        })
        .catch(error => console.error('Error:', error));
}

// Initialize on page load
window.addEventListener('load', function() {
    updateCartCount();
});
