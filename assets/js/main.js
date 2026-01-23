/**
 * Home Page JavaScript Enhancements
 * Provides interactive features for better UX
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all home page features
    initImageLoading();
    initNewsletterForm();
    initSmoothScrolling();
    initProductCards();
    initCategoryCards();
    initIntersectionObserver();
    initMobileMenu();
    initNewArrivals();
    initCategories();
});

/**
 * Initialize New Arrivals specific features
 */
function initNewArrivals() {
    initFilterToggle();
    initQuickView();
    initAddToCartFeedback();
    initLazyLoading();
    initPriceRange();
}

/**
 * Initialize Categories specific features
 */
function initCategories() {
    initCategoryLazyLoading();
    initCategoryAnalytics();
}

/**
 * Lazy loading for category images
 */
function initCategoryLazyLoading() {
    const categoryImages = document.querySelectorAll('.category-image[loading="lazy"]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                const src = img.getAttribute('data-src');
                if (src) {
                    img.src = src;
                    img.classList.add('loaded');
                }
                observer.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px 0px'
    });

    categoryImages.forEach(img => {
        imageObserver.observe(img);
    });
}

/**
 * Analytics tracking for category interactions
 */
function initCategoryAnalytics() {
    const categoryLinks = document.querySelectorAll('.category-link');

    categoryLinks.forEach(link => {
        // Track impressions
        if (typeof gtag !== 'undefined') {
            gtag('event', 'view_item_list', {
                event_category: 'engagement',
                event_label: link.getAttribute('data-category-name'),
                custom_parameters: {
                    category_id: link.getAttribute('data-category-id'),
                    category_slug: link.getAttribute('data-category-slug')
                }
            });
        }

        // Track clicks
        link.addEventListener('click', function(e) {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'select_content', {
                    event_category: 'engagement',
                    event_label: this.getAttribute('data-category-name'),
                    content_type: 'category',
                    custom_parameters: {
                        category_id: this.getAttribute('data-category-id'),
                        category_slug: this.getAttribute('data-category-slug')
                    }
                });
            }
        });
    });
}

/**
 * Filter toggle functionality
 */
function initFilterToggle() {
    const toggle = document.querySelector('.filter-toggle');
    const panel = document.getElementById('new-arrivals-filters');

    if (!toggle || !panel) return;

    toggle.addEventListener('click', function() {
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !isExpanded);
        panel.hidden = isExpanded;
    });
}

/**
 * Quick view modal functionality
 */
function initQuickView() {
    const modal = document.createElement('div');
    modal.className = 'quick-view-modal';
    modal.innerHTML = `
        <div class="quick-view-content">
            <button class="quick-view-close" aria-label="Close quick view">&times;</button>
            <div class="quick-view-body">
                <div class="quick-view-image">
                    <img src="" alt="" id="quick-view-img">
                </div>
                <div class="quick-view-details">
                    <h3 id="quick-view-title"></h3>
                    <div id="quick-view-price"></div>
                    <div id="quick-view-description"></div>
                    <div class="quick-view-actions">
                        <button id="quick-view-add-to-cart" class="add-to-cart-btn">Add to Cart</button>
                        <a href="" id="quick-view-view-full" class="btn btn-secondary">View Full Product</a>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    const closeBtn = modal.querySelector('.quick-view-close');
    const addToCartBtn = modal.querySelector('#quick-view-add-to-cart');

    // Open modal
    document.addEventListener('click', function(e) {
        if (e.target.closest('.quick-view-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.quick-view-btn');
            const productId = btn.dataset.productId;
            openQuickView(productId);
        }
    });

    // Close modal
    closeBtn.addEventListener('click', closeQuickView);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeQuickView();
    });

    // Add to cart from modal
    addToCartBtn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        addToCart(productId, this);
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) {
            closeQuickView();
        }
    });
}

function openQuickView(productId) {
    // Fetch product data (simplified - in real implementation, use AJAX)
    const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
    if (!card) return;

    const img = card.querySelector('.primary-image');
    const title = card.querySelector('.product-title a');
    const price = card.querySelector('.product-price');
    const description = card.querySelector('.product-attributes') || document.createElement('div');

    document.getElementById('quick-view-img').src = img.src;
    document.getElementById('quick-view-img').alt = img.alt;
    document.getElementById('quick-view-title').textContent = title.textContent;
    document.getElementById('quick-view-price').innerHTML = price.innerHTML;
    document.getElementById('quick-view-description').textContent = description.textContent || 'Beautiful new arrival perfect for any occasion.';
    document.getElementById('quick-view-view-full').href = title.href;
    document.getElementById('quick-view-add-to-cart').dataset.productId = productId;

    document.querySelector('.quick-view-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeQuickView() {
    document.querySelector('.quick-view-modal').classList.remove('open');
    document.body.style.overflow = '';
}

/**
 * Add to cart feedback
 */
function initAddToCartFeedback() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-to-cart-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.add-to-cart-btn');
            const productId = btn.dataset.productId;
            addToCart(productId, btn);
        }
    });
}

function addToCart(productId, btn) {
    // Show loading state
    const originalText = btn.textContent;
    btn.textContent = 'Adding...';
    btn.disabled = true;

    // Simulate AJAX call (replace with actual WooCommerce AJAX)
    setTimeout(() => {
        btn.textContent = 'Added!';
        btn.classList.add('added');
        btn.disabled = false;

        // Reset after animation
        setTimeout(() => {
            btn.textContent = originalText;
            btn.classList.remove('added');
        }, 2000);

        // Update cart count (simplified)
        updateCartCount();
    }, 1000);
}

function updateCartCount() {
    const countElement = document.querySelector('.mini-cart-count');
    if (countElement) {
        const currentCount = parseInt(countElement.textContent) || 0;
        countElement.textContent = currentCount + 1;
        countElement.setAttribute('aria-live', 'polite');
    }
}

/**
 * Lazy loading with IntersectionObserver
 */
function initLazyLoading() {
    const images = document.querySelectorAll('img[loading="lazy"]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => {
        imageObserver.observe(img);
    });
}

/**
 * Price range slider
 */
function initPriceRange() {
    const minRange = document.getElementById('price-min');
    const maxRange = document.getElementById('price-max');
    const display = document.getElementById('price-display');

    if (!minRange || !maxRange || !display) return;

    function updateDisplay() {
        const min = parseInt(minRange.value);
        const max = parseInt(maxRange.value);
        display.textContent = `$${min} - $${max}`;
    }

    minRange.addEventListener('input', updateDisplay);
    maxRange.addEventListener('input', updateDisplay);
    updateDisplay();
}

/**
 * Handle image loading states
 */
function initImageLoading() {
    const images = document.querySelectorAll('.product-image, .category-card');

    images.forEach(img => {
        if (img.tagName === 'IMG') {
            // For actual img elements
            img.addEventListener('load', function() {
                this.classList.add('loaded');
            });

            // If already loaded
            if (img.complete) {
                img.classList.add('loaded');
            }
        } else {
            // For background images (category cards)
            img.classList.add('loaded');
        }
    });
}

/**
 * Handle newsletter form submission
 */
function initNewsletterForm() {
    const form = document.getElementById('newsletter-form');
    const submitBtn = document.getElementById('newsletter-submit');
    const messageDiv = document.getElementById('newsletter-message');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');

    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const email = form.email.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // Reset previous messages
        messageDiv.style.display = 'none';
        messageDiv.className = 'newsletter-message';

        // Basic validation
        if (!email || !emailRegex.test(email)) {
            showMessage('Please enter a valid email address.', 'error');
            form.email.focus();
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';
        submitBtn.classList.add('loading');

        // Simulate form submission (replace with actual AJAX call)
        setTimeout(() => {
            // Simulate success (replace with actual response handling)
            const success = Math.random() > 0.2; // 80% success rate for demo

            if (success) {
                showMessage('Thank you for subscribing! Check your email for confirmation.', 'success');
                form.reset();
            } else {
                showMessage('Something went wrong. Please try again later.', 'error');
            }

            // Reset button state
            submitBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
            submitBtn.classList.remove('loading');
        }, 2000);
    });

    function showMessage(text, type) {
        messageDiv.textContent = text;
        messageDiv.className = `newsletter-message ${type}`;
        messageDiv.style.display = 'block';
        messageDiv.focus();

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }
    }
}

/**
 * Smooth scrolling for anchor links
 */
function initSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Add interactive features to product cards
 */
function initProductCards() {
    const productCards = document.querySelectorAll('.product-card');

    productCards.forEach((card, index) => {
        // Add staggered animation delay
        card.style.setProperty('--card-index', index);

        // Add click tracking (for analytics)
        card.addEventListener('click', function(e) {
            // Only track if not clicking on add to cart button
            if (!e.target.closest('.add-to-cart-btn')) {
                // Add visual feedback
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            }
        });

        // Add to cart button enhancement
        const addToCartBtn = card.querySelector('.add-to-cart-btn');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function(e) {
                e.preventDefault();

                // Add loading state
                const originalText = this.textContent;
                this.textContent = 'Adding...';
                this.disabled = true;

                // Simulate AJAX call
                setTimeout(() => {
                    this.textContent = 'Added to Cart!';
                    this.style.background = '#48bb78';

                    setTimeout(() => {
                        this.textContent = originalText;
                        this.disabled = false;
                        this.style.background = '';
                    }, 2000);
                }, 1000);
            });
        }
    });
}

/**
 * Add interactive features to category cards
 */
function initCategoryCards() {
    const categoryCards = document.querySelectorAll('.category-card');

    categoryCards.forEach((card, index) => {
        // Add staggered animation delay
        card.style.setProperty('--category-index', index);

        // Add hover sound effect (visual feedback)
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.03)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
}

/**
 * Intersection Observer for scroll animations
 */
function initIntersectionObserver() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe sections for scroll animations
    const sections = document.querySelectorAll('.featured-products, .categories-section, .newsletter-section');
    sections.forEach(section => {
        observer.observe(section);
    });
}

/**
 * Add keyboard navigation improvements
 */
document.addEventListener('keydown', function(e) {
    // Close modals or messages with Escape key
    if (e.key === 'Escape') {
        const message = document.querySelector('.newsletter-message[style*="display: block"]');
        if (message) {
            message.style.display = 'none';
        }
    }
});

/**
 * Performance optimization: Lazy load images
 */
function lazyLoadImages() {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });

    // Add lazy loading to images with data-src attribute
    const lazyImages = document.querySelectorAll('img[data-src]');
    lazyImages.forEach(img => {
        imageObserver.observe(img);
    });
}

/**
 * Mobile menu toggle functionality
 */
function initMobileMenu() {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const menu = document.querySelector('.site-navigation ul');

    if (!toggle || !menu) return;

    toggle.addEventListener('click', function() {
        const isOpen = menu.classList.contains('is-open');
        const expanded = this.getAttribute('aria-expanded') === 'true';

        menu.classList.toggle('is-open');
        this.setAttribute('aria-expanded', !expanded);
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!toggle.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    // Close menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && menu.classList.contains('is-open')) {
            menu.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
}

// Initialize lazy loading
lazyLoadImages();