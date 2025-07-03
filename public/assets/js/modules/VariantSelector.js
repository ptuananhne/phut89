// FILE: /public/assets/js/modules/VariantSelector.js
// MÔ TẢ: Module xử lý toàn bộ logic phức tạp cho việc chọn biến thể sản phẩm
//        trên trang chi tiết sản phẩm.

export default class VariantSelector {
  constructor() {
    const dataElement = document.getElementById("variantsData");
    if (!dataElement) return;

    try {
      this.data = JSON.parse(dataElement.textContent);
    } catch (e) {
      console.error("Lỗi khi đọc dữ liệu JSON của biến thể:", e);
      return;
    }

    this.elements = {
      price: document.getElementById("productPrice"),
      oldPrice: document.getElementById("productOldPrice"),
      stock: document.getElementById("stockStatus"),
      mainImage: document.getElementById("mainProductImage"),
      thumbContainer: document.getElementById("thumbnailContainer"),
      optionsContainer: document.getElementById("variantOptionsContainer"),
      discountWrapper: document.getElementById("discountPercentageWrapper"),
    };
    this.selectedOptions = {};

    if (this.data && this.elements.optionsContainer) {
      this.init();
    }
  }

  init() {
    this.renderOptions();
    this.selectDefaultVariant();
    this.addEventListeners();
  }

  renderOptions() {
    if (!this.data.options) return;
    this.elements.optionsContainer.innerHTML = "";
    this.data.options.forEach((option) => {
      const group = document.createElement("div");
      group.className = "option-group";
      group.innerHTML = `<label class="option-label">${option.name}</label>`;

      const choices = document.createElement("div");
      choices.className = "option-choices";

      const values = this.data.optionValues.filter(
        (v) => v.option_id === option.id
      );
      values.forEach((val) => {
        let choiceHTML;
        if (val.image) {
          choiceHTML = `
                      <div class="option-choice option-choice-swatch" data-option-id="${option.id}" data-value-id="${val.id}">
                          <img src="${val.image}" alt="${val.value}" loading="lazy" onerror="this.style.display='none'">
                          <div class="swatch-info">
                              <span class="swatch-name">${val.value}</span>
                          </div>
                      </div>`;
        } else {
          choiceHTML = `<div class="option-choice" data-option-id="${option.id}" data-value-id="${val.id}">${val.value}</div>`;
        }
        choices.innerHTML += choiceHTML;
      });
      group.appendChild(choices);
      this.elements.optionsContainer.appendChild(group);
    });
  }

  selectDefaultVariant() {
    const defaultVariant =
      this.data.variants.find((v) => v.stock > 0) || this.data.variants[0];
    if (!defaultVariant) {
      this.updateAll();
      return;
    }
    defaultVariant.options.forEach((valueId) => {
      const optionValue = this.data.optionValues.find((v) => v.id === valueId);
      if (optionValue) {
        this.selectedOptions[optionValue.option_id] = valueId;
      }
    });
    this.updateAll();
  }

  addEventListeners() {
    this.elements.optionsContainer.addEventListener("click", (e) => {
      const choice = e.target.closest(".option-choice");
      if (!choice) return;

      const optionId = parseInt(choice.dataset.optionId, 10);
      const valueId = parseInt(choice.dataset.valueId, 10);

      if (this.selectedOptions[optionId] === valueId) return;

      this.selectedOptions[optionId] = valueId;

      const currentVariant = this.findMatchingVariant();

      if (!currentVariant) {
        const fallbackVariant = this.data.variants.find(
          (v) => v.options.includes(valueId) && v.stock > 0
        );

        if (fallbackVariant) {
          this.selectedOptions = {};
          fallbackVariant.options.forEach((optValId) => {
            const optionValue = this.data.optionValues.find(
              (v) => v.id === optValId
            );
            if (optionValue) {
              this.selectedOptions[optionValue.option_id] = optValId;
            }
          });
        }
      }

      this.updateAll();
    });
  }

  updateAll() {
    const currentVariant = this.findMatchingVariant(true);
    this.updateDisplay(currentVariant);
    this.updateAvailability();
    this.updateSelectedStyles();
  }

  findMatchingVariant(ignoreStock = false) {
    const selectedValues = Object.values(this.selectedOptions);
    if (selectedValues.length < this.data.options.length) return null;
    return this.data.variants.find((variant) => {
      const hasAllOptions = selectedValues.every((val) =>
        variant.options.includes(val)
      );
      return ignoreStock ? hasAllOptions : hasAllOptions && variant.stock > 0;
    });
  }

  /**
   * [CRITICAL FIX] Sửa lại logic hiển thị giá để sử dụng đúng tên trường từ JSON
   */
  updateDisplay(variant) {
    // Luôn dọn dẹp các giá trị cũ
    this.elements.oldPrice.textContent = "";
    if (this.elements.discountWrapper) {
      this.elements.discountWrapper.innerHTML = "";
    }

    if (variant) {
      // [FIX] Khôi phục lại tên trường gốc: 'price' và 'original_price'
      const price = parseFloat(variant.price || 0);
      const originalPrice = parseFloat(variant.original_price || 0);

      // Giá hiển thị chính là giá của biến thể.
      this.elements.price.textContent = this.formatPrice(price);

      this.elements.stock.textContent =
        variant.stock > 0 ? "Còn hàng" : "Hết hàng";
      this.elements.stock.className = `stock-status ${
        variant.stock > 0 ? "in-stock" : "out-of-stock"
      }`;

      // Chỉ hiển thị giá gốc (gạch đi) và % giảm giá nếu có khuyến mãi hợp lệ
      if (originalPrice > price) {
        this.elements.oldPrice.textContent = this.formatPrice(originalPrice);

        if (this.elements.discountWrapper) {
          const percentage = Math.round(
            ((originalPrice - price) / originalPrice) * 100
          );
          const discountEl = document.createElement("span");
          discountEl.className = "discount-percentage";
          discountEl.textContent = `-${percentage}%`;
          this.elements.discountWrapper.appendChild(discountEl);
        }
      }

      const image = this.data.images.find((img) => img.id === variant.image_id);
      if (image && this.elements.mainImage.src !== image.url) {
        this.elements.mainImage.src = image.url;
        this.updateActiveThumbnail(image.url);
      }
    } else {
      this.elements.price.textContent = "Không có sẵn";
      this.elements.stock.textContent = "Không có sẵn";
      this.elements.stock.className = "stock-status out-of-stock";
    }
  }

  updateAvailability() {
    const allChoices =
      this.elements.optionsContainer.querySelectorAll(".option-choice");
    allChoices.forEach((choice) => {
      const valueId = parseInt(choice.dataset.valueId, 10);
      const optionId = parseInt(choice.dataset.optionId, 10);

      const otherSelectedValues = [];
      for (const key in this.selectedOptions) {
        if (parseInt(key) !== optionId) {
          otherSelectedValues.push(this.selectedOptions[key]);
        }
      }

      const isPossible = this.data.variants.some((variant) => {
        if (variant.stock <= 0) return false;
        const hasThisChoice = variant.options.includes(valueId);
        const hasOthers = otherSelectedValues.every((v) =>
          variant.options.includes(v)
        );
        return hasThisChoice && hasOthers;
      });
      choice.classList.toggle("disabled", !isPossible);
    });
  }
  updateSelectedStyles() {
    const allChoices =
      this.elements.optionsContainer.querySelectorAll(".option-choice");
    allChoices.forEach((choice) => {
      const optionId = parseInt(choice.dataset.optionId, 10);
      const valueId = parseInt(choice.dataset.valueId, 10);
      choice.classList.toggle(
        "selected",
        this.selectedOptions[optionId] === valueId
      );
    });
  }

  updateActiveThumbnail(url) {
    if (!this.elements.thumbContainer) return;
    this.elements.thumbContainer.querySelectorAll("img").forEach((thumb) => {
      thumb.classList.toggle(
        "active",
        (thumb.dataset.fullSrc || thumb.src) === url
      );
    });
  }

  formatPrice(number) {
    if (isNaN(number) || number === null) return "";
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(number);
  }
}
