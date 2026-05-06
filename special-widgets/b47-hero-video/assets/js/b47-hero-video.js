/**
 * B47 Hero Video Widget - Scoped JavaScript
 * All functionality is scoped to .base47-widget-b47-hero-video
 */

(function() {
    'use strict';

    /**
     * Initialize B47 Hero Video Widget
     */
    function initB47HeroVideo() {
        // Find all B47 Hero Video widgets on the page
        const widgets = document.querySelectorAll('.base47-widget-b47-hero-video');
        
        widgets.forEach(function(widget) {
            initSingleWidget(widget);
        });
    }

    /**
     * Initialize a single widget instance
     * @param {Element} widget - The widget container element
     */
    function initSingleWidget(widget) {
        const image = widget.querySelector('.tp-hero-image img');
        
        if (image) {
            // Handle image loading states
            handleImageLoading(image, widget);
            
            // Add lazy loading if supported
            addLazyLoading(image);
        }

        // Initialize category animation controls
        initCategoryAnimations(widget);
        
        // Add resize handler for responsive behavior
        addResizeHandler(widget);
    }

    /**
     * Handle image loading states
     * @param {Element} image - The image element
     * @param {Element} widget - The widget container
     */
    function handleImageLoading(image, widget) {
        image.addEventListener('load', function() {
            widget.classList.remove('image-loading');
            widget.classList.add('image-ready');
        });

        image.addEventListener('error', function() {
            widget.classList.remove('image-loading');
            widget.classList.add('image-error');
            console.error('Bfolio Hero Image: Failed to load image');
            
            // Add fallback placeholder
            addImageFallback(image);
        });

        // Set loading state
        if (!image.complete) {
            widget.classList.add('image-loading');
        } else if (image.naturalWidth === 0) {
            widget.classList.add('image-error');
            addImageFallback(image);
        } else {
            widget.classList.add('image-ready');
        }
    }

    /**
     * Add lazy loading for performance
     * @param {Element} image - The image element
     */
    function addLazyLoading(image) {
        if ('loading' in HTMLImageElement.prototype) {
            image.loading = 'lazy';
        } else if ('IntersectionObserver' in window) {
            // Fallback for browsers without native lazy loading
            const imageObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        imageObserver.unobserve(img);
                    }
                });
            });

            imageObserver.observe(image);
        }
    }

    /**
     * Initialize category animations
     * @param {Element} widget - The widget container
     */
    function initCategoryAnimations(widget) {
        const categoryContainer = widget.querySelector('.tp-hero-cetagory');
        
        if (categoryContainer) {
            // Pause animation on hover
            categoryContainer.addEventListener('mouseenter', function() {
                const animatedElements = categoryContainer.querySelectorAll('.animate-right');
                animatedElements.forEach(function(element) {
                    element.style.animationPlayState = 'paused';
                });
            });

            // Resume animation when not hovering
            categoryContainer.addEventListener('mouseleave', function() {
                const animatedElements = categoryContainer.querySelectorAll('.animate-right');
                animatedElements.forEach(function(element) {
                    element.style.animationPlayState = 'running';
                });
            });
        }
    }

    /**
     * Add resize handler for responsive behavior
     * @param {Element} widget - The widget container
     */
    function addResizeHandler(widget) {
        let resizeTimeout;
        
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                handleResize(widget);
            }, 250);
        });
    }

    /**
     * Handle resize events
     * @param {Element} widget - The widget container
     */
    function handleResize(widget) {
        const image = widget.querySelector('.tp-hero-image img');
        
        if (image) {
            // Recalculate image dimensions if needed
            const container = image.parentElement;
            const containerWidth = container.offsetWidth;
            
            // Adjust image size for mobile if needed
            if (window.innerWidth <= 991) {
                image.style.width = '100%';
                image.style.height = 'auto';
            }
        }
    }

    /**
     * Add image fallback for failed loads
     * @param {Element} image - The image element
     */
    function addImageFallback(image) {
        // Create a simple placeholder
        const fallbackUrl = 'data:image/svg+xml;base64,' + btoa(`
            <svg width="420" height="340" xmlns="http://www.w3.org/2000/svg">
                <rect width="100%" height="100%" fill="#f0f0f0"/>
                <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="16" 
                      fill="#666" text-anchor="middle" dy=".3em">Image not available</text>
            </svg>
        `);
        
        image.src = fallbackUrl;
        image.alt = 'Placeholder image';
    }

    /**
     * Initialize when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initB47HeroVideo);
    } else {
        initB47HeroVideo();
    }

    /**
     * Re-initialize when new widgets are added dynamically
     */
    if (window.MutationObserver) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        if (node.classList && node.classList.contains('base47-widget-b47-hero-video')) {
                            initSingleWidget(node);
                        } else {
                            // Check if any child elements are B47 Hero Video widgets
                            const childWidgets = node.querySelectorAll && node.querySelectorAll('.base47-widget-b47-hero-video');
                            if (childWidgets && childWidgets.length > 0) {
                                childWidgets.forEach(initSingleWidget);
                            }
                        }
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Expose initialization function globally for manual initialization
    window.Base47B47HeroVideo = {
        init: initB47HeroVideo,
        initWidget: initSingleWidget
    };

})();