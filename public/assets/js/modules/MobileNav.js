export default class MobileNav {
    constructor() {
        this.hamburgerBtn = document.getElementById('hamburger-btn');
        this.mobileNav = document.getElementById('mobile-nav');
        this.closeBtn = document.getElementById('mobile-nav-close');
        this.overlay = document.getElementById('mobile-nav-overlay');

        if (this.hamburgerBtn && this.mobileNav && this.closeBtn && this.overlay) {
            this.init();
        }
    }

    init() {
        this.hamburgerBtn.addEventListener('click', () => this.open());
        this.closeBtn.addEventListener('click', () => this.close());
        this.overlay.addEventListener('click', () => this.close());
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });
    }

    isOpen() {
        return this.mobileNav.classList.contains('open');
    }

    open() {
        this.mobileNav.classList.add('open');
        this.overlay.classList.add('open');
        document.body.classList.add('mobile-nav-open');
        this.hamburgerBtn.setAttribute('aria-expanded', 'true');
    }

    close() {
        this.mobileNav.classList.remove('open');
        this.overlay.classList.remove('open');
        document.body.classList.remove('mobile-nav-open');
        this.hamburgerBtn.setAttribute('aria-expanded', 'false');
    }
}
