// Loading Animation
window.addEventListener('load', function() {
    setTimeout(function() {
        document.getElementById('loading').classList.add('hidden');
    }, 500);
});

// Live Search
let searchTimeout;
const searchInput = document.getElementById('searchInput');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch();
        }, 500);
    });
}

function performSearch() {
    const searchValue = searchInput.value;
    const currentUrl = new URL(window.location.href);
    
    if (searchValue) {
        currentUrl.searchParams.set('search', searchValue);
    } else {
        currentUrl.searchParams.delete('search');
    }
    
    currentUrl.searchParams.delete('page');
    window.location.href = currentUrl.toString();
}

// Sort Products
function sortProducts(sortValue) {
    const currentUrl = new URL(window.location.href);
    
    if (sortValue) {
        currentUrl.searchParams.set('sort', sortValue);
    } else {
        currentUrl.searchParams.delete('sort');
    }
    
    currentUrl.searchParams.delete('page');
    window.location.href = currentUrl.toString();
}

// Toggle Wishlist
function toggleWishlist(button, productId) {
    button.classList.toggle('active');
    
    // Get wishlist from localStorage
    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    
    if (button.classList.contains('active')) {
        // Add to wishlist
        if (!wishlist.includes(productId)) {
            wishlist.push(productId);
        }
        // Add animation
        button.style.transform = 'scale(1.2)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 200);
    } else {
        // Remove from wishlist
        wishlist = wishlist.filter(id => id !== productId);
    }
    
    // Save to localStorage
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
}

// Initialize wishlist state
document.addEventListener('DOMContentLoaded', function() {
    const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    const wishlistBtns = document.querySelectorAll('.wishlist-btn');
    
    wishlistBtns.forEach(btn => {
        const productId = parseInt(btn.closest('.product-card').dataset.id);
        if (wishlist.includes(productId)) {
            btn.classList.add('active');
        }
    });
});

// Smooth scroll for pagination
document.querySelectorAll('a[href^="?"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const href = this.getAttribute('href');
        window.location.href = href;
        
        // Scroll to top smoothly
        window.scrollTo({
            top: 200,
            behavior: 'smooth'
        });
    });
});

// Tabs functionality
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Remove active class from all tabs
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        // Add active class to clicked tab
        this.classList.add('active');
        
        // Filter products based on tab (demo functionality)
        const tabText = this.textContent.trim();
        const currentUrl = new URL(window.location.href);
        
        switch(tabText) {
            case 'Terlaris':
                currentUrl.searchParams.set('sort', 'terlaris');
                break;
            case 'Terbaru':
                currentUrl.searchParams.set('sort', 'terbaru');
                break;
            case 'Diskon':
                currentUrl.searchParams.set('diskon', 'true');
                break;
            default:
                currentUrl.searchParams.delete('sort');
                currentUrl.searchParams.delete('diskon');
        }
        
        currentUrl.searchParams.delete('page');
        window.location.href = currentUrl.toString();
    });
});

// Product card hover effect
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.08)';
    });
});

// Price input validation
document.querySelectorAll('.price-input').forEach(input => {
    input.addEventListener('input', function() {
        if (this.value < 0) {
            this.value = 0;
        }
    });
});

// Responsive sidebar toggle for mobile
function createMobileMenu() {
    if (window.innerWidth <= 768) {
        const sidebar = document.querySelector('.sidebar');
        if (!document.querySelector('.sidebar-toggle')) {
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'sidebar-toggle';
            toggleBtn.innerHTML = '☰ Filter';
            toggleBtn.style.cssText = `
                width: 100%;
                padding: 12px;
                background: white;
                border: 2px solid #ee4d2d;
                color: #ee4d2d;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                margin-bottom: 15px;
                cursor: pointer;
                font-family: 'Poppins', sans-serif;
                transition: all 0.3s;
            `;
            
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                this.textContent = sidebar.classList.contains('show') ? '✕ Tutup Filter' : '☰ Filter';
            });
            
            sidebar.parentNode.insertBefore(toggleBtn, sidebar);
            sidebar.style.display = 'none';
        }
    }
}

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    if (window.innerWidth > 768) {
        if (sidebar) {
            sidebar.style.display = 'block';
            const toggleBtn = document.querySelector('.sidebar-toggle');
            if (toggleBtn) {
                toggleBtn.remove();
            }
        }
    } else {
        createMobileMenu();
    }
});

// Initialize
createMobileMenu();

// Add to cart animation (demo)
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('dblclick', function(e) {
        e.preventDefault();
        const productName = this.querySelector('.product-name').textContent;
        alert(`Produk "${productName}" telah ditambahkan ke keranjang!`);
    });
});