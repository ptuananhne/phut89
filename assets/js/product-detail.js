document.addEventListener("DOMContentLoaded", () => {
  /**
   * Class to handle variant selection on the product detail page.
   */
  class VariantSelector {
    constructor() {
      const dataElement = document.getElementById("variantsData");
      if (!dataElement) return;

      try {
        this.data = JSON.parse(dataElement.textContent);
      } catch (e) {
        console.error("Error parsing variants JSON:", e);
        return;
      }

      this.elements = {
        price: document.getElementById("productPrice"),
        oldPrice: document.getElementById("productOldPrice"),
        stock: document.getElementById("stockStatus"),
        mainImage: document.getElementById("mainProductImage"),
        thumbContainer: document.getElementById("thumbnailContainer"),
        optionsContainer: document.getElementById("variantOptionsContainer"),
      };
      this.selectedOptions = {};

      if (!this.data || !this.elements.optionsContainer) return;
      this.init();
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
            const firstVariantWithThisColor = this.data.variants.find((v) =>
              v.options.includes(val.id)
            );
            const priceText = firstVariantWithThisColor
              ? this.formatPrice(firstVariantWithThisColor.price)
              : "";
            // [FIXED] Added onerror handler to gracefully hide missing swatch images
            choiceHTML = `
                            <div class="option-choice option-choice-swatch" data-option-id="${option.id}" data-value-id="${val.id}">
                                <img src="${val.image}" alt="${val.value}" loading="lazy" onerror="this.style.display='none'">
                                <div class="swatch-info">
                                    <span class="swatch-name">${val.value}</span>
                                    <span class="swatch-price">${priceText}</span>
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
        this.elements.price.textContent = "Hết hàng";
        this.elements.stock.textContent = "Hết hàng";
        this.elements.stock.className = "stock-status out-of-stock";
        this.elements.optionsContainer
          .querySelectorAll(".option-choice")
          .forEach((c) => c.classList.add("disabled"));
        return;
      }
      defaultVariant.options.forEach((valueId) => {
        const optionValue = this.data.optionValues.find(
          (v) => v.id === valueId
        );
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

        let currentVariant = this.findMatchingVariant(true);

        if (!currentVariant) {
          const fallbackVariant = this.data.variants.find(
            (v) => v.stock > 0 && v.options.includes(valueId)
          );

          if (fallbackVariant) {
            this.selectedOptions = {};
            fallbackVariant.options.forEach((vId) => {
              const optValue = this.data.optionValues.find((v) => v.id === vId);
              if (optValue) {
                this.selectedOptions[optValue.option_id] = vId;
              }
            });
          }
        }

        this.updateAll();
      });

      this.elements.thumbContainer.addEventListener("click", (e) => {
        const thumb = e.target.closest("img");
        if (thumb) {
          this.elements.mainImage.src = thumb.dataset.fullSrc;
          this.elements.thumbContainer
            .querySelectorAll("img")
            .forEach((i) => i.classList.remove("active"));
          thumb.classList.add("active");
        }
      });
    }

    updateAll() {
      this.updateAvailability();
      const currentVariant = this.findMatchingVariant(true);
      this.updateDisplay(currentVariant);
      this.updateSelectedStyles();
    }

    findMatchingVariant(ignoreStock = false) {
      const selectedValues = Object.values(this.selectedOptions);
      if (selectedValues.length < this.data.options.length) return null;
      return this.data.variants.find((variant) => {
        const hasAllOptions = selectedValues.every((val) =>
          variant.options.includes(val)
        );
        if (ignoreStock) {
          return hasAllOptions;
        }
        return hasAllOptions && variant.stock > 0;
      });
    }

    updateDisplay(variant) {
      if (variant) {
        this.elements.price.textContent = this.formatPrice(variant.price);
        this.elements.oldPrice.textContent =
          variant.old_price > 0 ? this.formatPrice(variant.old_price) : "";

        if (variant.stock > 0) {
          this.elements.stock.textContent = "Còn hàng";
          this.elements.stock.className = "stock-status in-stock";
        } else {
          this.elements.stock.textContent = "Hết hàng";
          this.elements.stock.className = "stock-status out-of-stock";
        }

        const image = this.data.images.find(
          (img) => img.id === variant.image_id
        );
        if (image && this.elements.mainImage.src !== image.url) {
          this.elements.mainImage.src = image.url;
          this.updateActiveThumbnail(image.url);
        }
      } else {
        this.elements.price.textContent = "Không có sẵn";
        this.elements.oldPrice.textContent = "";
        this.elements.stock.textContent = "Không có sẵn";
        this.elements.stock.className = "stock-status out-of-stock";
      }
    }

    updateAvailability() {
      this.data.options.forEach((optionGroup) => {
        const choicesInGroup = this.elements.optionsContainer.querySelectorAll(
          `.option-choice[data-option-id="${optionGroup.id}"]`
        );

        choicesInGroup.forEach((choice) => {
          const currentChoiceValueId = parseInt(choice.dataset.valueId, 10);
          const selectionsInOtherGroups = [];
          for (const key in this.selectedOptions) {
            if (parseInt(key, 10) !== optionGroup.id) {
              selectionsInOtherGroups.push(this.selectedOptions[key]);
            }
          }

          const isPossible = this.data.variants.some((variant) => {
            if (variant.stock <= 0) return false;

            const hasCurrentChoice =
              variant.options.includes(currentChoiceValueId);
            const hasOtherSelections = selectionsInOtherGroups.every((v) =>
              variant.options.includes(v)
            );
            return hasCurrentChoice && hasOtherSelections;
          });

          choice.classList.toggle("disabled", !isPossible);
        });
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
      this.elements.thumbContainer.querySelectorAll("img").forEach((thumb) => {
        thumb.classList.toggle("active", thumb.dataset.fullSrc === url);
      });
    }

    formatPrice(number) {
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(number);
    }
  }

  class TabSystem {
    constructor() {
      this.container = document.querySelector(".product-tabs");
      if (!this.container) return;
      this.headers = this.container.querySelectorAll(".tab-header");
      this.contents = this.container.querySelectorAll(".tab-content");
      this.addEventListeners();
    }
    addEventListeners() {
      this.headers.forEach((header) => {
        header.addEventListener("click", () => {
          const tabName = header.dataset.tab;
          this.activateTab(tabName);
        });
      });
    }
    activateTab(tabName) {
      this.headers.forEach((h) =>
        h.classList.toggle("active", h.dataset.tab === tabName)
      );
      this.contents.forEach((c) =>
        c.classList.toggle("active", c.id === tabName)
      );
    }
  }

  if (document.getElementById("variantsData")) {
    new VariantSelector();
  }
  if (document.querySelector(".product-tabs")) {
    new TabSystem();
  }
});
