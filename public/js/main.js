document.addEventListener('DOMContentLoaded', function () {

    // 1. Slideshow Banner
    (function initSlideshow() {
        const container = document.querySelector('.slideshow-container');
        if (!container) return;

        const slides = container.querySelectorAll(".mySlides");
        const dots = container.querySelectorAll(".dot");
        const prevBtn = container.querySelector('.prev');
        const nextBtn = container.querySelector('.next');

        if (slides.length <= 1) {
            if(prevBtn) prevBtn.style.display = 'none';
            if(nextBtn) nextBtn.style.display = 'none';
            if(dots.length > 0) container.querySelector('.dots-container').style.display = 'none';
            return;
        };

        let slideIndex = 0;
        let slideInterval;

        function showSlides(n) {
            slideIndex = (n + slides.length) % slides.length;

            slides.forEach(slide => slide.classList.remove("active"));
            dots.forEach(dot => dot.classList.remove("active-dot"));
            
            slides[slideIndex].classList.add("active");
            dots[slideIndex].classList.add("active-dot");
        }

        function playSlideshow() {
            clearInterval(slideInterval);
            slideInterval = setInterval(() => showSlides(slideIndex + 1), 4000);
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                showSlides(slideIndex - 1);
                playSlideshow();
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                showSlides(slideIndex + 1);
                playSlideshow();
            });
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSlides(index);
                playSlideshow();
            });
        });

        showSlides(slideIndex);
        playSlideshow();
    })();

    // 2. Product Carousel Navigation
    (function initProductCarousels() {
        const carouselContainers = document.querySelectorAll('.product-carousel-container');

        carouselContainers.forEach(container => {
            const wrapper = container.querySelector('.product-carousel-wrapper');
            const prevBtn = container.querySelector('.carousel-nav-btn.prev');
            const nextBtn = container.querySelector('.carousel-nav-btn.next');

            if (!wrapper || !prevBtn || !nextBtn) return;
            
            const getScrollAmount = () => wrapper.clientWidth * 0.8;

            nextBtn.addEventListener('click', () => {
                wrapper.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
            });

            prevBtn.addEventListener('click', () => {
                wrapper.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
            });
        });
    })();
       (function initMobileMenu() {
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const mobileNav = document.getElementById('mobile-nav');
        const closeBtn = document.getElementById('mobile-nav-close');
        if (!hamburgerBtn || !mobileNav || !closeBtn) return;

        const overlay = document.getElementById('mobile-nav-overlay');
        if (!overlay) return;

        const openMenu = () => {
            mobileNav.classList.add('open');
            overlay.classList.add('open');
            document.body.classList.add('mobile-nav-open');
        };

        const closeMenu = () => {
            mobileNav.classList.remove('open');
            overlay.classList.remove('open');
            document.body.classList.remove('mobile-nav-open');
        };

        hamburgerBtn.addEventListener('click', openMenu);
        closeBtn.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);
    })();
});
