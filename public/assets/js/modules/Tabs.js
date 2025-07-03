export default class Tabs {
    constructor(containerSelector) {
        this.container = document.querySelector(containerSelector);
        if (!this.container) return;
        
        this.headers = this.container.querySelectorAll('.tab-header');
        this.contents = this.container.querySelectorAll('.tab-content');
        
        if(this.headers.length > 0 && this.contents.length > 0) {
            this.init();
        }
    }

    init() {
        this.headers.forEach(header => {
            header.addEventListener('click', () => {
                const tabName = header.dataset.tab;
                this.activate(tabName);
            });
        });
    }

    activate(tabName) {
        this.headers.forEach(h => h.classList.toggle('active', h.dataset.tab === tabName));
        this.contents.forEach(c => c.classList.toggle('active', c.id === tabName));
    }
}