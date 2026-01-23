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
});

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

// Initialize lazy loading
lazyLoadImages();