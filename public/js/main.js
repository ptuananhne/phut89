document.addEventListener('DOMContentLoaded', function () {

    /**
     * Khởi tạo Menu cho di động (Hamburger)
     */
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
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileNav.classList.contains('open')) {
                closeMenu();
            }
        });
    })();


    /**
     * Khởi tạo Slideshow Banner
     */
    (function initSlideshow() {
        const container = document.querySelector('.slideshow-container');
        if (!container) return;

        const slides = Array.from(container.querySelectorAll(".mySlides"));
        const dots = Array.from(container.querySelectorAll(".dot"));
        const prevBtn = container.querySelector('.prev');
        const nextBtn = container.querySelector('.next');

        if (slides.length <= 1) {
            if(prevBtn) prevBtn.style.display = 'none';
            if(nextBtn) nextBtn.style.display = 'none';
            const dotsContainer = container.querySelector('.dots-container');
            if(dotsContainer) dotsContainer.style.display = 'none';
            return;
        };

        let slideIndex = 0;
        let slideInterval;

        function showSlides(n) {
            slideIndex = (n + slides.length) % slides.length;

            slides.forEach(slide => slide.style.display = "none");
            dots.forEach(dot => dot.classList.remove("active-dot"));
            
            if (slides[slideIndex]) {
                 slides[slideIndex].style.display = "block";
            }
            if (dots[slideIndex]) {
                dots[slideIndex].classList.add("active-dot");
            }
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

        if (slides.length > 0) {
            showSlides(slideIndex);
            playSlideshow();
        }
    })();

    /**
     * Khởi tạo Carousel cho sản phẩm
     */
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

    /**
     * Khởi tạo Tab cho bản đồ
     */
    (function initMapTabs() {
        const tabsContainer = document.querySelector('.map-tabs');
        if (!tabsContainer) return;
        tabsContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('map-tab-btn')) {
                const mapId = e.target.dataset.map;
                if (!mapId) return;
                document.querySelectorAll('.map-tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.map-pane').forEach(pane => pane.classList.remove('active'));
                e.target.classList.add('active');
                const activePane = document.getElementById(mapId);
                if (activePane) {
                    activePane.classList.add('active');
                }
            }
        });
    })();
    
    /**
     * Khởi tạo Tab cho mô tả sản phẩm
     */
    (function initProductTabs() {
        const tabsContainer = document.querySelector('.product-tabs');
        if (!tabsContainer) return;

        const headers = tabsContainer.querySelectorAll('.tab-header');
        const contents = tabsContainer.querySelectorAll('.tab-content');

        headers.forEach(header => {
            header.addEventListener('click', () => {
                const tabId = header.dataset.tab;

                headers.forEach(h => h.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                header.classList.add('active');
                const activeContent = tabsContainer.querySelector(`#${tabId}`);
                if (activeContent) {
                    activeContent.classList.add('active');
                }
            });
        });
    })();
      (function initProductGallery() {
        const mainImage = document.getElementById('mainProductImage');
        const thumbnailContainer = document.querySelector('.thumbnail-images');
        if (!mainImage || !thumbnailContainer) return;

        thumbnailContainer.addEventListener('click', function(e) {
            const thumbnail = e.target.closest('img');
            if (!thumbnail) return;

            const fullSrc = thumbnail.dataset.fullSrc || thumbnail.src;
            mainImage.src = fullSrc;
            mainImage.alt = thumbnail.alt;

            // Cập nhật class active cho thumbnail
            thumbnailContainer.querySelectorAll('img').forEach(img => img.classList.remove('active'));
            thumbnail.classList.add('active');
        });
          })();
});
