export default class Toast {
    constructor() {
        this.toastElement = document.getElementById('toast-notification');
        if (!this.toastElement) {
            console.error("Toast notification element not found!");
        }
    }

    show(message, type = 'info', duration = 3000) {
        if (!this.toastElement) return;

        this.toastElement.textContent = message;
        this.toastElement.className = 'toast-notification show'; // Reset class
        this.toastElement.classList.add(type); // 'success' or 'danger'

        setTimeout(() => {
            this.toastElement.classList.remove('show');
        }, duration);
    }
}