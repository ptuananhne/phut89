export default class ProductGallery {
    constructor() {
        this.gallery = document.querySelector('.product-gallery');
        if (!this.gallery) return;

        this.mainImage = this.gallery.querySelector('#mainProductImage');
        this.thumbContainer = this.gallery.querySelector('#thumbnailContainer');

        this.init();
    }

    init() {
        this.createLightbox();

        this.mainImage.addEventListener('click', () => this.openLightbox(this.mainImage.src));
        
        if (this.thumbContainer) {
            this.thumbContainer.addEventListener('click', (e) => {
                const thumbnail = e.target.closest('img');
                if (!thumbnail) return;
                
                const fullSrc = thumbnail.dataset.fullSrc || thumbnail.src;
                this.mainImage.src = fullSrc;
                
                this.thumbContainer.querySelectorAll('img').forEach(img => img.classList.remove('active'));
                thumbnail.classList.add('active');
            });
        }
    }

    createLightbox() {
        this.modalOverlay = document.createElement('div');
        this.modalOverlay.className = 'modal-overlay';
        this.modalOverlay.innerHTML = `
            <div class="modal-content">
                <button class="modal-close-btn" aria-label="Đóng">&times;</button>
                <img src="" alt="Product Image Enlarged" class="modal-image">
            </div>`;
        document.body.appendChild(this.modalOverlay);
        this.modalImage = this.modalOverlay.querySelector('.modal-image');

        this.modalOverlay.addEventListener('click', (e) => {
            if (e.target === this.modalOverlay || e.target.classList.contains('modal-close-btn')) {
                this.closeLightbox();
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modalOverlay.classList.contains('open')) {
                this.closeLightbox();
            }
        });
    }

    openLightbox(src) {
        this.modalImage.src = src;
        this.modalOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    closeLightbox() {
        this.modalOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }
}