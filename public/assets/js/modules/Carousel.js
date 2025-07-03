export default class Carousel {
    constructor(containerSelector) {
        this.container = document.querySelector(containerSelector);
        if (!this.container) return;
        
        this.wrapper = this.container.querySelector('.product-carousel-wrapper, .slideshow-container');
        this.slides = this.container.querySelectorAll('.product-card, .mySlides');
        this.dots = this.container.querySelectorAll('.dot');
        this.prevBtn = this.container.querySelector('.prev');
        this.nextBtn = this.container.querySelector('.next');
        
        this.slideIndex = 1;
        this.touchStartX = 0;
        this.touchEndX = 0;

        this.isSlideshow = this.container.classList.contains('slideshow-container');
        
        if (this.slides.length > 1) {
            this.init();
        }
    }
    
    init() {
        if (this.prevBtn) this.prevBtn.addEventListener('click', () => this.plusSlides(-1));
        if (this.nextBtn) this.nextBtn.addEventListener('click', () => this.plusSlides(1));
        
        if (this.dots.length > 0) {
            this.dots.forEach((dot, index) => {
                dot.addEventListener('click', () => this.currentSlide(index + 1));
            });
        }
        
        if (this.isSlideshow) {
            this.showSlides(this.slideIndex);
            this.play();
        }

        this.wrapper.addEventListener('touchstart', (e) => this.touchStart(e), { passive: true });
        this.wrapper.addEventListener('touchend', (e) => this.touchEnd(e));
    }

    plusSlides(n) {
        if (this.isSlideshow) {
            this.showSlides(this.slideIndex += n);
            this.play();
        } else {
            const scrollAmount = this.wrapper.clientWidth * 0.8 * n;
            this.wrapper.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
    }

    currentSlide(n) {
        this.showSlides(this.slideIndex = n);
        this.play();
    }
    
    showSlides(n) {
        if (n > this.slides.length) { this.slideIndex = 1; }
        if (n < 1) { this.slideIndex = this.slides.length; }
        
        this.slides.forEach(slide => slide.classList.remove("active"));
        this.dots.forEach(dot => dot.classList.remove("active-dot"));
        
        this.slides[this.slideIndex - 1].classList.add("active");
        if (this.dots.length > 0) {
            this.dots[this.slideIndex - 1].classList.add("active-dot");
        }
    }

    play() {
        clearInterval(this.slideInterval);
        this.slideInterval = setInterval(() => this.plusSlides(1), 3000);
    }
    
    touchStart(event) {
        this.touchStartX = event.changedTouches[0].screenX;
    }
    
    touchEnd(event) {
        this.touchEndX = event.changedTouches[0].screenX;
        this.handleSwipe();
    }
    
    handleSwipe() {
        if (this.touchEndX < this.touchStartX - 50) this.plusSlides(1); // Vuốt trái
        if (this.touchEndX > this.touchStartX + 50) this.plusSlides(-1); // Vuốt phải
    }
}