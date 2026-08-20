document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-out',
        once: true
    });

    // Navbar Scroll Effect
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Counter Animation
    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;

        const timer = setInterval(function() {
            current += increment;
            if (current >= target) {
                element.textContent = target.toLocaleString();
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current).toLocaleString();
            }
        }, 16);
    }

    // Intersection Observer for Counter Animation
    const counterObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                const counter = entry.target.querySelector('.counter');
                if (counter) {
                    animateCounter(counter);
                    counterObserver.unobserve(entry.target);
                }
            }
        });
    }, { threshold: 0.5 });

    // Observe all stat cards
    document.querySelectorAll('.stat-card, .counter-box, .social-link').forEach(function(card) {
        counterObserver.observe(card);
    });

    // GSAP Animations
    if (typeof gsap !== 'undefined') {
        // Hero section animations
        gsap.from('.hero-headline', {
            duration: 1,
            x: -50,
            opacity: 0,
            ease: 'power3.out',
            delay: 0.2
        });

        gsap.from('.hero-subheadline', {
            duration: 1,
            x: -50,
            opacity: 0,
            ease: 'power3.out',
            delay: 0.4
        });

        gsap.from('.hero-image-wrapper', {
            duration: 1,
            scale: 0.8,
            opacity: 0,
            ease: 'elastic.out(1, 0.5)',
            delay: 0.6
        });

        gsap.from('.btn-yellow', {
            duration: 0.8,
            y: 30,
            opacity: 0,
            ease: 'power3.out',
            delay: 0.8
        });

        // Floating veggies enhanced animation
        gsap.to('.veggie-1', { y: 'random(-30, 30)', duration: 3, yoyo: true, repeat: -1, ease: 'power1.inOut' });
        gsap.to('.veggie-2', { y: 'random(-25, 25)', duration: 2.5, yoyo: true, repeat: -1, ease: 'power1.inOut', delay: 0.5 });
        gsap.to('.veggie-3', { y: 'random(-35, 35)', duration: 3.5, yoyo: true, repeat: -1, ease: 'power1.inOut', delay: 1 });
        gsap.to('.veggie-4', { y: 'random(-20, 20)', duration: 2.8, yoyo: true, repeat: -1, ease: 'power1.inOut', delay: 1.5 });
        gsap.to('.veggie-5', { y: 'random(-28, 28)', duration: 3.2, yoyo: true, repeat: -1, ease: 'power1.inOut', delay: 2 });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Auto-hide alerts (kept from original)
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (typeof bootstrap !== 'undefined') {
                const bootstrapAlert = new bootstrap.Alert(alert);
                bootstrapAlert.close();
            }
        }, 5000);
    });
});
