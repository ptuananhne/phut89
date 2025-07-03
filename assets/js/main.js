/**
 * main.js
 * Chứa tất cả các mã JavaScript chính cho trang web.
 */

document.addEventListener('DOMContentLoaded', function () {

    /**
     * Hàm hiển thị thông báo toast.
     */
    function displayToast(message, type = 'info', duration = 3000) {
        const toast = document.getElementById('toast-notification');
        if (!toast) return;

        toast.textContent = message;
        toast.className = 'toast-notification show';
        toast.classList.add(type);

        setTimeout(() => {
            toast.classList.remove('show');
        }, duration);
    }

    /**
     * Hàm sao chép văn bản vào clipboard.
     */
    async function copyToClipboard(text) {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            }
            displayToast('Đã sao chép nội dung vào khay nhớ tạm!', 'success');
        } catch (err) {
            displayToast('Không thể sao chép tự động.', 'danger');
            console.error('Clipboard copy failed: ', err);
        }
    }


    // --- KHỞI TẠO CÁC MODULE ---

    // 1. Menu di động (Hamburger)
    (function initMobileMenu() {
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const mobileNav = document.getElementById('mobile-nav');
        // SỬA ĐỔI: Lấy thêm nút đóng mới
        const closeBtn = document.getElementById('mobile-nav-close');
        if (!hamburgerBtn || !mobileNav || !closeBtn) return;

        const overlay = document.querySelector('.mobile-nav-overlay');
        if (!overlay) return;

        const openMenu = () => {
            mobileNav.classList.add('open');
            overlay.classList.add('open');
            document.body.classList.add('mobile-nav-open');
            hamburgerBtn.setAttribute('aria-expanded', 'true');
        };

        const closeMenu = () => {
            mobileNav.classList.remove('open');
            overlay.classList.remove('open');
            document.body.classList.remove('mobile-nav-open');
            hamburgerBtn.setAttribute('aria-expanded', 'false');
        };

        hamburgerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            openMenu();
        });

        // SỬA ĐỔI: Gán sự kiện cho nút đóng mới
        closeBtn.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileNav.classList.contains('open')) {
                closeMenu();
            }
        });
    })();


    // 2. Slideshow Banner (có chức năng vuốt)
    (function initSlideshow() {
        const container = document.querySelector('.slideshow-container');
        if (!container) return;

        const slides = container.querySelectorAll(".mySlides");
        const dots = container.querySelectorAll(".dot");
        if (slides.length <= 1) return;

        let slideIndex = 1;
        let slideInterval;
        let touchStartX = 0;
        let touchEndX = 0;

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }

        function currentSlide(n) {
            showSlides(slideIndex = n);
        }

        function showSlides(n) {
            if (n > slides.length) { slideIndex = 1; }
            if (n < 1) { slideIndex = slides.length; }

            slides.forEach(slide => slide.classList.remove("active"));
            dots.forEach(dot => dot.classList.remove("active-dot"));
            
            slides[slideIndex - 1].classList.add("active");
            dots[slideIndex - 1].classList.add("active-dot");
        }

        function playSlideshow() {
            clearInterval(slideInterval);
            slideInterval = setInterval(() => plusSlides(1), 3000);
        }
        
        container.addEventListener('click', function(e) {
            const prevButton = e.target.closest('.prev');
            const nextButton = e.target.closest('.next');
            const dotButton = e.target.closest('.dot');
            
            if (nextButton) {
                plusSlides(1);
                playSlideshow();
            } else if (prevButton) {
                plusSlides(-1);
                playSlideshow();
            } else if (dotButton) {
                const dotIndex = Array.from(dots).indexOf(dotButton);
                if (dotIndex !== -1) {
                    currentSlide(dotIndex + 1);
                    playSlideshow();
                }
            }
        });
        
        container.addEventListener('touchstart', (event) => {
            touchStartX = event.changedTouches[0].screenX;
        }, { passive: true });

        container.addEventListener('touchend', (event) => {
            touchEndX = event.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            if (touchEndX < touchStartX - 50) { // Vuốt sang trái
                plusSlides(1);
                playSlideshow();
            }
            if (touchEndX > touchStartX + 50) { // Vuốt sang phải
                plusSlides(-1);
                playSlideshow();
            }
        }

        showSlides(slideIndex);
        playSlideshow();
    })();


    // 3. Tab bản đồ
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

    // 4. Sticky Header
    (function initStickyHeader() {
        const siteHeader = document.getElementById('site-header');
        if (!siteHeader) return;
        const observer = new IntersectionObserver(
            ([e]) => e.target.classList.toggle('scrolled', e.intersectionRatio < 1),
            { threshold: [1] }
        );
        observer.observe(siteHeader);
    })();

    // 5. Lightbox cho ảnh sản phẩm
    (function initProductGallery() {
        const gallery = document.querySelector('.product-gallery');
        const mainImage = document.getElementById('mainProductImage');
        if (!gallery || !mainImage) return;
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'modal-overlay';
        modalOverlay.innerHTML = `
            <div class="modal-content">
                <button class="modal-close-btn" aria-label="Đóng">&times;</button>
                <img src="" alt="Product Image Enlaged" class="modal-image">
            </div>`;
        document.body.appendChild(modalOverlay);
        const modalImage = modalOverlay.querySelector('.modal-image');
        const openModal = (src) => {
            modalImage.src = src;
            modalOverlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        };
        const closeModal = () => {
            modalOverlay.classList.remove('open');
            document.body.style.overflow = '';
        };
        mainImage.addEventListener('click', () => openModal(mainImage.src));
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay || e.target.classList.contains('modal-close-btn')) {
                closeModal();
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalOverlay.classList.contains('open')) {
                closeModal();
            }
        });
        const thumbnailContainer = gallery.querySelector('.thumbnail-images');
        if (thumbnailContainer) {
            thumbnailContainer.addEventListener('click', function(e) {
                const thumbnail = e.target.closest('img');
                if (!thumbnail) return;
                const fullSrc = thumbnail.dataset.fullSrc || thumbnail.src;
                mainImage.src = fullSrc;
                thumbnailContainer.querySelectorAll('img').forEach(img => img.classList.remove('active'));
                thumbnail.classList.add('active');
            });
        }
    })();
    
    // 6. Xử lý nút sao chép (Zalo)
    document.body.addEventListener('click', function(e){
        const copyButton = e.target.closest('[data-copy-text]');
        if(copyButton) {
            e.preventDefault();
            const textToCopy = copyButton.dataset.copyText;
            copyToClipboard(textToCopy);
            if (copyButton.href) {
                 window.open(copyButton.href, '_blank');
            }
        }
    });

    // 7. Khởi tạo điều hướng cho Carousel sản phẩm
    (function initProductCarousels() {
        const carouselContainers = document.querySelectorAll('.product-carousel-container');

        carouselContainers.forEach(container => {
            const wrapper = container.querySelector('.product-carousel-wrapper');
            const prevBtn = container.querySelector('.carousel-nav-btn.prev');
            const nextBtn = container.querySelector('.carousel-nav-btn.next');

            if (!wrapper || !prevBtn || !nextBtn) {
                if(prevBtn) prevBtn.style.display = 'none';
                if(nextBtn) nextBtn.style.display = 'none';
                return;
            }
            
            const getScrollAmount = () => wrapper.clientWidth * 0.8;

            nextBtn.addEventListener('click', () => {
                wrapper.scrollBy({
                    left: getScrollAmount(),
                    behavior: 'smooth'
                });
            });

            prevBtn.addEventListener('click', () => {
                wrapper.scrollBy({
                    left: -getScrollAmount(),
                    behavior: 'smooth'
                });
            });
        });
    })();


    console.log('Phút 89 website is ready!');
    
});
