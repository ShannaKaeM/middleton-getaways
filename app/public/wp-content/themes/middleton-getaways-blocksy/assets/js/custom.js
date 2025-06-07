/**
 * Middleton Getaways - Custom JavaScript
 * 
 * @package MiddletonGetaways
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        initSmoothScrolling();
        initPropertyFilters();
        initContactForm();
        initImageLightbox();
        initScrollAnimations();
    });

    /**
     * Smooth scrolling for anchor links
     */
    function initSmoothScrolling() {
        $('a[href^="#"]').on('click', function(e) {
            const target = $(this.getAttribute('href'));
            
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 800);
            }
        });
    }

    /**
     * Property filtering functionality
     */
    function initPropertyFilters() {
        $('.property-filter').on('change', function() {
            const category = $('#property-category').val();
            const location = $('#property-location').val();
            const priceRange = $('#price-range').val();
            
            filterProperties(category, location, priceRange);
        });
    }

    /**
     * Filter properties via AJAX
     */
    function filterProperties(category, location, priceRange) {
        const $grid = $('.property-grid');
        const $loader = $('.property-loader');
        
        $loader.show();
        $grid.addClass('loading');
        
        $.ajax({
            url: mgBlocksy.ajaxurl,
            type: 'POST',
            data: {
                action: 'filter_properties',
                category: category,
                location: location,
                price_range: priceRange,
                nonce: mgBlocksy.nonce
            },
            success: function(response) {
                if (response.success) {
                    $grid.html(response.data);
                    initScrollAnimations(); // Re-initialize animations for new content
                } else {
                    console.error('Property filtering failed:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            },
            complete: function() {
                $loader.hide();
                $grid.removeClass('loading');
            }
        });
    }

    /**
     * Enhanced contact form handling
     */
    function initContactForm() {
        $('.contact-form').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const $messages = $form.find('.form-messages');
            
            // Disable submit button and show loading
            $submitBtn.prop('disabled', true).text('Sending...');
            $messages.removeClass('success error').empty();
            
            // Validate form
            if (!validateContactForm($form)) {
                $submitBtn.prop('disabled', false).text('Send Message');
                return;
            }
            
            // Submit form via AJAX
            $.ajax({
                url: mgBlocksy.ajaxurl,
                type: 'POST',
                data: $form.serialize() + '&action=submit_contact_form&nonce=' + mgBlocksy.nonce,
                success: function(response) {
                    if (response.success) {
                        $messages.addClass('success').text('Thank you! Your message has been sent.');
                        $form[0].reset();
                    } else {
                        $messages.addClass('error').text(response.data || 'An error occurred. Please try again.');
                    }
                },
                error: function() {
                    $messages.addClass('error').text('An error occurred. Please try again.');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text('Send Message');
                }
            });
        });
    }

    /**
     * Validate contact form
     */
    function validateContactForm($form) {
        let isValid = true;
        
        $form.find('.required').each(function() {
            const $field = $(this);
            const value = $field.val().trim();
            
            if (!value) {
                showFieldError($field, 'This field is required.');
                isValid = false;
            } else if ($field.attr('type') === 'email' && !isValidEmail(value)) {
                showFieldError($field, 'Please enter a valid email address.');
                isValid = false;
            } else {
                clearFieldError($field);
            }
        });
        
        return isValid;
    }

    /**
     * Show field error
     */
    function showFieldError($field, message) {
        $field.addClass('error');
        $field.siblings('.field-error').remove();
        $field.after('<span class="field-error">' + message + '</span>');
    }

    /**
     * Clear field error
     */
    function clearFieldError($field) {
        $field.removeClass('error');
        $field.siblings('.field-error').remove();
    }

    /**
     * Validate email format
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Initialize image lightbox
     */
    function initImageLightbox() {
        $('.property-gallery img, .gallery-item img').on('click', function(e) {
            e.preventDefault();
            
            const src = $(this).attr('src') || $(this).data('src');
            const alt = $(this).attr('alt') || '';
            
            showLightbox(src, alt);
        });
        
        // Close lightbox
        $(document).on('click', '.lightbox-overlay, .lightbox-close', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });
        
        // Close lightbox with ESC key
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC key
                closeLightbox();
            }
        });
    }

    /**
     * Show image lightbox
     */
    function showLightbox(src, alt) {
        const lightboxHtml = `
            <div class="lightbox-overlay">
                <div class="lightbox-content">
                    <button class="lightbox-close">&times;</button>
                    <img src="${src}" alt="${alt}" />
                </div>
            </div>
        `;
        
        $('body').append(lightboxHtml).addClass('lightbox-open');
    }

    /**
     * Close lightbox
     */
    function closeLightbox() {
        $('.lightbox-overlay').fadeOut(300, function() {
            $(this).remove();
            $('body').removeClass('lightbox-open');
        });
    }

    /**
     * Initialize scroll animations
     */
    function initScrollAnimations() {
        const $animatedElements = $('.animate-on-scroll');
        
        if ($animatedElements.length === 0) return;
        
        // Check if element is in viewport
        function isInViewport($element) {
            const elementTop = $element.offset().top;
            const elementBottom = elementTop + $element.outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height();
            
            return elementBottom > viewportTop && elementTop < viewportBottom;
        }
        
        // Animate elements on scroll
        function animateOnScroll() {
            $animatedElements.each(function() {
                const $element = $(this);
                
                if (isInViewport($element) && !$element.hasClass('animated')) {
                    $element.addClass('animated');
                }
            });
        }
        
        // Initial check
        animateOnScroll();
        
        // Check on scroll
        $(window).on('scroll', throttle(animateOnScroll, 100));
    }

    /**
     * Throttle function for performance
     */
    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    /**
     * Initialize mobile menu toggle
     */
    function initMobileMenu() {
        $('.mobile-menu-toggle').on('click', function() {
            $(this).toggleClass('active');
            $('.main-navigation').toggleClass('mobile-open');
            $('body').toggleClass('menu-open');
        });
        
        // Close mobile menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.main-navigation, .mobile-menu-toggle').length) {
                $('.mobile-menu-toggle').removeClass('active');
                $('.main-navigation').removeClass('mobile-open');
                $('body').removeClass('menu-open');
            }
        });
    }

    /**
     * Initialize property search
     */
    function initPropertySearch() {
        $('.property-search-form').on('submit', function(e) {
            e.preventDefault();
            
            const searchTerm = $(this).find('input[name="search"]').val();
            const category = $(this).find('select[name="category"]').val();
            const location = $(this).find('select[name="location"]').val();
            
            // Redirect to properties page with search parameters
            const searchParams = new URLSearchParams();
            if (searchTerm) searchParams.append('search', searchTerm);
            if (category) searchParams.append('category', category);
            if (location) searchParams.append('location', location);
            
            window.location.href = '/properties/?' + searchParams.toString();
        });
    }

    /**
     * Initialize all mobile functionality
     */
    initMobileMenu();
    initPropertySearch();

})(jQuery);
