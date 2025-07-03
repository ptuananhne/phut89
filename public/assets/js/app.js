/**
 * ===============================================================
 * APP.JS (MAIN ENTRY POINT)
 *
 * Tệp này nhập và khởi tạo tất cả các module JavaScript.
 * Đây là tệp duy nhất cần được nhúng vào HTML.
 * ===============================================================
 */

import Toast from "./modules/Toast.js";
import MobileNav from "./modules/MobileNav.js";
import Tabs from "./modules/Tabs.js";
import Carousel from "./modules/Carousel.js";
import ProductGallery from "./modules/ProductGallery.js";
import VariantSelector from "./modules/VariantSelector.js";

document.addEventListener("DOMContentLoaded", () => {
  // Khởi tạo các module chung cho toàn trang
  const toast = new Toast();
  window.AppToast = toast;
  new MobileNav();

  // Khởi tạo tất cả các carousel/slideshow
  if (document.querySelector(".slideshow-container")) {
    new Carousel(".slideshow-container");
  }
  document.querySelectorAll(".product-carousel-container").forEach((el) => {
    new Carousel(el);
  });

  // Khởi tạo hệ thống tab
  if (document.querySelector(".map-tabs")) {
    new Tabs(".map-tabs");
  }
  if (document.querySelector(".product-tabs")) {
    new Tabs(".product-tabs");
  }

  // [FIXED] Sửa lại selector để khớp với layout mới của trang sản phẩm
  if (document.querySelector(".product-page-layout")) {
    new ProductGallery();
    new VariantSelector();
  }

  // Xử lý sự kiện chung: sao chép vào clipboard với fallback
  document.body.addEventListener("click", async (e) => {
    const copyButton = e.target.closest("[data-copy-text]");
    if (copyButton) {
      e.preventDefault();
      const textToCopy = copyButton.dataset.copyText;

      try {
        // Ưu tiên API mới, chỉ hoạt động trên HTTPS
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(textToCopy);
        } else {
          // Fallback cho HTTP hoặc trình duyệt cũ
          const textArea = document.createElement("textarea");
          textArea.value = textToCopy;
          textArea.style.position = "absolute";
          textArea.style.left = "-9999px";
          document.body.appendChild(textArea);
          textArea.select();
          document.execCommand("copy");
          document.body.removeChild(textArea);
        }
        toast.show("Đã sao chép nội dung!", "success");
      } catch (err) {
        toast.show("Không thể sao chép tự động.", "danger");
        console.error("Clipboard copy failed: ", err);
      }

      // Mở link sau khi sao chép nếu có
      if (copyButton.href && copyButton.target === "_blank") {
        window.open(copyButton.href, "_blank");
      }
    }
  });

  // Sticky Header sử dụng IntersectionObserver cho hiệu năng tốt hơn
  const siteHeader = document.getElementById("site-header");
  if (siteHeader) {
    const observer = new IntersectionObserver(
      ([e]) => e.target.classList.toggle("scrolled", e.intersectionRatio < 1),
      { threshold: [0.99] }
    );
    observer.observe(siteHeader);
  }

  console.log("Phút 89 website is ready! (Modular JS Fixed)");
});
