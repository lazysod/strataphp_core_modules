// Modern CMS Theme JS
// Place all custom theme JS here. No inline scripts in templates.

document.addEventListener('DOMContentLoaded', function () {
  // Example: Add theme-specific JS here

  // Mobile menu functionality for Modern CMS Theme
    function toggleMobileMenu()
    {
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.getElementById('nav-menu');
        const overlay = document.getElementById('mobile-overlay');
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
    }
    function closeMobileMenu()
    {
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.getElementById('nav-menu');
        const overlay = document.getElementById('mobile-overlay');
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    document.addEventListener('DOMContentLoaded', function () {
        const navLinks = document.querySelectorAll('.cms-nav a');
        navLinks.forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) {
                closeMobileMenu();
            }
        });
    });
});
