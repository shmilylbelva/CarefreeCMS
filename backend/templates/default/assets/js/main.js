/**
 * Carefree Theme - Main JavaScript
 * 极光主题主脚本
 *
 * Features:
 * - Theme toggle (Dark/Light mode)
 * - Mobile navigation
 * - Search modal
 * - Reading progress bar
 * - Scroll effects
 * - Back to top button
 * - Smooth animations
 */

(function() {
    'use strict';

    // ========================================
    // DOM Ready
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        initThemeToggle();
        initMobileNav();
        initSearchModal();
        initReadingProgress();
        initScrollEffects();
        initBackToTop();
        initRevealAnimations();
    });

    // ========================================
    // Theme Toggle (Dark/Light Mode)
    // ========================================
    function initThemeToggle() {
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        if (!themeToggle) return;

        // Get saved theme or system preference
        const savedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const initialTheme = savedTheme || (systemPrefersDark ? 'dark' : 'light');

        // Apply initial theme
        html.setAttribute('data-theme', initialTheme);

        // Toggle theme on click
        themeToggle.addEventListener('click', function() {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            // Animate the toggle
            themeToggle.classList.add('rotating');
            setTimeout(() => themeToggle.classList.remove('rotating'), 300);
        });

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            if (!localStorage.getItem('theme')) {
                html.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            }
        });
    }

    // ========================================
    // Mobile Navigation
    // ========================================
    function initMobileNav() {
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mainNav = document.getElementById('mainNav');
        const body = document.body;

        if (!mobileMenuToggle || !mainNav) return;

        mobileMenuToggle.addEventListener('click', function() {
            const isOpen = mainNav.classList.contains('active');

            mainNav.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
            body.classList.toggle('nav-open');

            // Update aria-expanded
            mobileMenuToggle.setAttribute('aria-expanded', !isOpen);
        });

        // Close mobile nav when clicking outside
        document.addEventListener('click', function(e) {
            if (mainNav.classList.contains('active') &&
                !mainNav.contains(e.target) &&
                !mobileMenuToggle.contains(e.target)) {
                mainNav.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
                body.classList.remove('nav-open');
            }
        });

        // Close mobile nav on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024 && mainNav.classList.contains('active')) {
                mainNav.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
                body.classList.remove('nav-open');
            }
        });

        // Handle dropdown menus on mobile
        const dropdownItems = mainNav.querySelectorAll('.has-dropdown > a');
        dropdownItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                if (window.innerWidth <= 1024) {
                    const parent = this.parentElement;
                    const isOpen = parent.classList.contains('dropdown-open');

                    // Close other dropdowns
                    mainNav.querySelectorAll('.dropdown-open').forEach(function(el) {
                        el.classList.remove('dropdown-open');
                    });

                    if (!isOpen) {
                        e.preventDefault();
                        parent.classList.add('dropdown-open');
                    }
                }
            });
        });
    }

    // ========================================
    // Search Modal
    // ========================================
    function initSearchModal() {
        const searchTrigger = document.getElementById('searchTrigger');
        const searchModal = document.getElementById('searchModal');
        const searchClose = document.getElementById('searchClose');
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');

        if (!searchTrigger || !searchModal) return;

        // Open search modal
        searchTrigger.addEventListener('click', function() {
            searchModal.classList.add('active');
            document.body.classList.add('modal-open');
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 100);
            }
        });

        // Close search modal
        function closeSearchModal() {
            searchModal.classList.remove('active');
            document.body.classList.remove('modal-open');
        }

        if (searchClose) {
            searchClose.addEventListener('click', closeSearchModal);
        }

        // Close on backdrop click
        searchModal.addEventListener('click', function(e) {
            if (e.target === searchModal) {
                closeSearchModal();
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchModal.classList.contains('active')) {
                closeSearchModal();
            }
        });

        // Keyboard shortcut to open search (Ctrl/Cmd + K)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchModal.classList.add('active');
                document.body.classList.add('modal-open');
                if (searchInput) {
                    setTimeout(() => searchInput.focus(), 100);
                }
            }
        });
    }

    // ========================================
    // Reading Progress Bar
    // ========================================
    function initReadingProgress() {
        const progressBar = document.getElementById('readingProgress');

        if (!progressBar) return;

        function updateProgress() {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;

            progressBar.style.width = Math.min(progress, 100) + '%';
        }

        window.addEventListener('scroll', throttle(updateProgress, 10));
        updateProgress();
    }

    // ========================================
    // Scroll Effects (Header)
    // ========================================
    function initScrollEffects() {
        const header = document.getElementById('siteHeader');

        if (!header) return;

        let lastScrollY = 0;

        function handleScroll() {
            const scrollY = window.scrollY;

            // Add/remove scrolled class
            if (scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            // Hide/show header on scroll (optional)
            if (scrollY > lastScrollY && scrollY > 200) {
                header.classList.add('header-hidden');
            } else {
                header.classList.remove('header-hidden');
            }

            lastScrollY = scrollY;
        }

        window.addEventListener('scroll', throttle(handleScroll, 10));
        handleScroll();
    }

    // ========================================
    // Back to Top Button
    // ========================================
    function initBackToTop() {
        const backToTop = document.getElementById('backToTop');

        if (!backToTop) return;

        // Show/hide button based on scroll
        function toggleBackToTop() {
            if (window.scrollY > 500) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        }

        window.addEventListener('scroll', throttle(toggleBackToTop, 100));
        toggleBackToTop();

        // Scroll to top on click
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ========================================
    // Reveal Animations (Intersection Observer)
    // ========================================
    function initRevealAnimations() {
        const revealElements = document.querySelectorAll('[data-reveal]');

        if (revealElements.length === 0) return;

        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        revealElements.forEach(function(el) {
            observer.observe(el);
        });
    }

    // ========================================
    // Utility: Throttle Function
    // ========================================
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

    // ========================================
    // Utility: Debounce Function
    // ========================================
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    // ========================================
    // Smooth Scroll for Anchor Links
    // ========================================
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                const offsetTop = targetElement.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ========================================
    // Lazy Load Images
    // ========================================
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                    }
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(function(img) {
            imageObserver.observe(img);
        });
    }

    // ========================================
    // Copy to Clipboard Utility
    // ========================================
    window.copyToClipboard = function(text) {
        return navigator.clipboard.writeText(text).then(function() {
            return true;
        }).catch(function() {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            const success = document.execCommand('copy');
            document.body.removeChild(textarea);
            return success;
        });
    };

    // ========================================
    // Print current year
    // ========================================
    document.querySelectorAll('[data-year]').forEach(function(el) {
        el.textContent = new Date().getFullYear();
    });

})();
