document.addEventListener("DOMContentLoaded", function () {
  const AppConfig = window.AppConfig || {
    BASE_URL: "",
    CSRF_TOKEN: "",
  };

  function e(str) {
    if (str === null || typeof str === "undefined") return "";
    const p = document.createElement("p");
    p.textContent = str;
    return p.innerHTML;
  }

  const modal = {
    el: document.getElementById("custom-modal"),
    title: document.getElementById("modal-title"),
    body: document.getElementById("modal-body-text"),
    confirmBtn: document.getElementById("modal-confirm-btn"),
    closeBtn: document.getElementById("modal-close-btn"),
    _resolve: null,
    init() {
      if (!this.el) return;
      this.el.addEventListener("click", (e) => {
        if (e.target === this.el) this._handleClose(false);
      });
      this.closeBtn.addEventListener("click", () => this._handleClose(false));
      this.confirmBtn.addEventListener("click", () =>
        this._handleConfirm(true)
      );
    },
    _handleClose(value) {
      this.el.classList.remove("active");
      if (this._resolve) {
        this._resolve(value);
        this._resolve = null;
      }
    },
    _handleConfirm() {
      this._handleClose(true);
    },
    alert(title, text) {
      if (!this.el) {
        window.alert(title + "\n" + text);
        return Promise.resolve();
      }
      this.title.textContent = title;
      this.body.innerHTML = text;
      this.confirmBtn.style.display = "none";
      this.closeBtn.textContent = "Đóng";
      this.el.classList.add("active");
      return new Promise((resolve) => {
        this._resolve = resolve;
      });
    },
    confirm(title, text) {
      if (!this.el) {
        return Promise.resolve(window.confirm(title + "\n" + text));
      }
      this.title.textContent = title;
      this.body.innerHTML = text;
      this.confirmBtn.style.display = "inline-flex";
      this.confirmBtn.textContent = "Xác nhận";
      this.closeBtn.textContent = "Hủy";
      this.el.classList.add("active");
      return new Promise((resolve) => {
        this._resolve = resolve;
      });
    },
  };
  modal.init();

  function generateSlug(str) {
    if (!str) return "";
    str = str.replace(/^\s+|\s+$/g, "").toLowerCase();
    const from =
      "àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ·/_,:;";
    const to =
      "aaaaaaaaaaaaaaaaaeeeeeeeeeeeiiiiiooooooooooooooooouuuuuuuuuuuyyyyyd------";
    for (let i = 0, l = from.length; i < l; i++) {
      str = str.replace(new RegExp(from.charAt(i), "g"), to.charAt(i));
    }
    str = str
      .replace(/[^a-z0-9 -]/g, "")
      .replace(/\s+/g, "-")
      .replace(/-+/g, "-");
    return str;
  }

  const nameInputForSlug = document.getElementById("name_for_slug");
  const slugInput = document.getElementById("slug");
  if (nameInputForSlug && slugInput) {
    nameInputForSlug.addEventListener("keyup", function () {
      if (!slugInput.dataset.userModified) {
        slugInput.value = generateSlug(this.value);
      }
    });
    slugInput.addEventListener("input", () => {
      slugInput.dataset.userModified = "true";
    });
  }

  const sortableContainer = document.getElementById("sortable-categories");
  if (sortableContainer) {
    new Sortable(sortableContainer, {
      animation: 150,
      handle: ".sort-handle",
      onEnd: function () {
        const order = Array.from(this.el.children).map((row) => row.dataset.id);
        fetch(`${AppConfig.BASE_URL}/admin/ajax/handle/update_category_order`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            order: order,
            csrf_token: AppConfig.CSRF_TOKEN,
          }),
        }).catch((err) => modal.alert("Lỗi", "Không thể lưu thứ tự mới."));
      },
    });
  }

  document.body.addEventListener("click", function (e) {
    const toggleBtn = e.target.closest(".status-toggle-btn");
    if (toggleBtn) {
      const id = toggleBtn.dataset.id;
      const type = toggleBtn.dataset.type;
      const status = toggleBtn.dataset.currentStatus;
      const formData = new FormData();
      formData.append("id", id);
      formData.append("type", type);
      formData.append("current_status", status);
      formData.append("csrf_token", AppConfig.CSRF_TOKEN);

      fetch(`${AppConfig.BASE_URL}/admin/ajax/handle/toggle_status`, {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            toggleBtn.dataset.currentStatus = data.new_status;
            const isActive = data.new_status == 1;
            toggleBtn.classList.toggle("active", isActive);
            toggleBtn.classList.toggle("inactive", !isActive);
            toggleBtn.textContent = isActive
              ? type === "banner"
                ? "Hiển thị"
                : "Hoạt động"
              : "Ẩn";
          } else {
            modal.alert(
              "Lỗi",
              data.message || "Không thể cập nhật trạng thái."
            );
          }
        })
        .catch((err) => modal.alert("Lỗi", "Lỗi kết nối máy chủ."));
    }
  });

  const attributesPage = document.querySelector(".attributes-container");
  if (attributesPage) {
    const attrListEl = document.getElementById("attributes-list");
    const detailBodyEl = document.getElementById("detail-body");
    const detailTitleEl = document.getElementById("detail-title");
    const addAttrForm = document.getElementById("add-attribute-form");
    const csrfToken = AppConfig.CSRF_TOKEN;
    let attributesData = window.attributesData || [];
    let currentAttrId = null;

    const attrItemTemplate = (attr) =>
      `<li data-id="${
        attr.id
      }"><span class="attribute-name" title="Nhấp để sửa tên">${e(
        attr.ten_thuoc_tinh
      )}</span><button class="btn-inline-delete delete-attribute" title="Xóa thuộc tính này">&times;</button></li>`;
    const detailTemplate = (attr) =>
      `<form id="add-value-form" class="form-group"><label for="new-value-name">Thêm giá trị mới cho <strong class="text-primary">${e(
        attr.ten_thuoc_tinh
      )}</strong></label><div class="input-group"><input type="text" id="new-value-name" class="form-control" placeholder="Ví dụ: Xanh, 128GB..." required><div class="input-group-append"><button type="submit" class="btn btn-primary">Thêm</button></div></div></form><hr><p><strong>Các giá trị hiện có:</strong></p><ul id="values-list" class="values-list"></ul>`;
    const valueItemTemplate = (val) =>
      `<li data-id="${
        val.id
      }"><span class="value-name" title="Nhấp để sửa tên">${e(
        val.name
      )}</span><button class="btn-inline-delete delete-value" title="Xóa giá trị này">&times;</button></li>`;

    const renderAttributes = () => {
      attrListEl.innerHTML =
        attributesData.map((attr) => attrItemTemplate(attr)).join("") ||
        '<li class="list-placeholder">Chưa có thuộc tính nào.</li>';
    };
    const renderDetailView = (attrId) => {
      currentAttrId = attrId;
      attrListEl
        .querySelectorAll("li")
        .forEach((li) => li.classList.remove("active"));
      attrListEl
        .querySelector(`li[data-id="${attrId}"]`)
        ?.classList.add("active");
      const attr = attributesData.find((a) => a.id == attrId);
      if (!attr) {
        detailBodyEl.innerHTML =
          '<div class="attributes-detail-placeholder"><p>Không tìm thấy thuộc tính.</p></div>';
        detailTitleEl.textContent = "Giá trị";
        return;
      }
      detailTitleEl.textContent = `Giá trị cho: ${e(attr.ten_thuoc_tinh)}`;
      detailBodyEl.innerHTML = detailTemplate(attr);
      const valuesListEl = detailBodyEl.querySelector("#values-list");
      const values = attr.values
        ? attr.values.split("||").map((v) => ({
            id: v.split("::")[0],
            name: v.split("::")[1],
          }))
        : [];
      if (values.length > 0) {
        valuesListEl.innerHTML = values
          .map((val) => valueItemTemplate(val))
          .join("");
      } else {
        valuesListEl.innerHTML =
          '<p class="text-muted">Chưa có giá trị nào.</p>';
      }
    };
    const performAjax = (action, data) => {
      const formData = new FormData();
      formData.append("action", action);
      formData.append("csrf_token", csrfToken);
      for (const key in data) {
        formData.append(key, data[key]);
      }
      return fetch(`${AppConfig.BASE_URL}/admin/ajax/handle/${action}`, {
        method: "POST",
        body: formData,
      }).then((res) => res.json());
    };
    const enableInlineEdit = (element, onSave) => {
      const originalText = element.textContent;
      const input = document.createElement("input");
      input.type = "text";
      input.value = originalText;
      input.className = "inline-edit-input";
      element.parentNode.insertBefore(input, element);
      element.style.display = "none";
      input.focus();
      const saveChanges = () => {
        const newText = input.value.trim();
        if (newText && newText !== originalText) {
          onSave(newText);
        } else {
          element.style.display = "";
          input.remove();
        }
      };
      input.addEventListener("blur", saveChanges);
      input.addEventListener("keydown", (e) => {
        if (e.key === "Enter") input.blur();
        if (e.key === "Escape") {
          input.value = originalText;
          input.blur();
        }
      });
    };

    addAttrForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const nameInput = document.getElementById("new-attribute-name");
      const name = nameInput.value.trim();
      if (!name) return;
      performAjax("add_attribute", { name }).then((data) => {
        if (data.success) {
          attributesData.push({
            id: data.id,
            ten_thuoc_tinh: data.name,
            values: null,
          });
          renderAttributes();
          nameInput.value = "";
        } else {
          modal.alert("Lỗi", data.message);
        }
      });
    });
    attrListEl.addEventListener("click", (e) => {
      const li = e.target.closest("li");
      if (!li || li.classList.contains("list-placeholder")) return;
      const attrId = parseInt(li.dataset.id);
      if (e.target.classList.contains("delete-attribute")) {
        modal
          .confirm(
            "Xác nhận xóa",
            "Xóa thuộc tính này sẽ xóa tất cả giá trị con. Bạn chắc chứ?"
          )
          .then((confirmed) => {
            if (confirmed) {
              performAjax("delete_attribute", { id: attrId }).then((data) => {
                if (data.success) {
                  attributesData = attributesData.filter(
                    (a) => a.id !== attrId
                  );
                  renderAttributes();
                  if (currentAttrId === attrId) {
                    detailBodyEl.innerHTML =
                      '<div class="attributes-detail-placeholder"><p>Chọn một thuộc tính...</p></div>';
                    detailTitleEl.textContent = "Giá trị";
                  }
                } else {
                  modal.alert("Lỗi", data.message);
                }
              });
            }
          });
      } else if (e.target.classList.contains("attribute-name")) {
        enableInlineEdit(e.target, (newName) => {
          performAjax("update_attribute", { id: attrId, name: newName }).then(
            (data) => {
              if (data.success) {
                const attr = attributesData.find((a) => a.id === attrId);
                attr.ten_thuoc_tinh = newName;
                renderAttributes();
                if (currentAttrId === attrId) renderDetailView(attrId);
              } else {
                modal.alert("Lỗi", data.message);
                renderAttributes();
              }
            }
          );
        });
      } else {
        renderDetailView(attrId);
      }
    });
    detailBodyEl.addEventListener("submit", (e) => {
      if (e.target.id !== "add-value-form") return;
      e.preventDefault();
      const valueInput = document.getElementById("new-value-name");
      const value = valueInput.value.trim();
      if (!value || !currentAttrId) return;
      performAjax("add_value", {
        attribute_id: currentAttrId,
        value: value,
      }).then((data) => {
        if (data.success) {
          const attr = attributesData.find((a) => a.id === currentAttrId);
          const newValueString = `${data.id}::${data.value}`;
          attr.values = attr.values
            ? `${attr.values}||${newValueString}`
            : newValueString;
          renderDetailView(currentAttrId);
        } else {
          modal.alert("Lỗi", data.message);
        }
      });
    });
    detailBodyEl.addEventListener("click", (e) => {
      const target = e.target;
      const li = target.closest("li");
      if (!li) return;
      const valueId = parseInt(li.dataset.id);
      if (target.classList.contains("delete-value")) {
        modal
          .confirm("Xác nhận xóa", "Bạn muốn xóa giá trị này?")
          .then((confirmed) => {
            if (confirmed) {
              performAjax("delete_value", { id: valueId }).then((data) => {
                if (data.success) {
                  const attr = attributesData.find(
                    (a) => a.id === currentAttrId
                  );
                  attr.values = attr.values
                    .split("||")
                    .filter((v) => v.split("::")[0] != valueId)
                    .join("||");
                  if (attr.values === "") attr.values = null;
                  renderDetailView(currentAttrId);
                } else {
                  modal.alert("Lỗi", data.message);
                }
              });
            }
          });
      } else if (target.classList.contains("value-name")) {
        enableInlineEdit(target, (newValue) => {
          performAjax("update_value", { id: valueId, value: newValue }).then(
            (data) => {
              if (data.success) {
                const attr = attributesData.find((a) => a.id === currentAttrId);
                attr.values = attr.values
                  .split("||")
                  .map((v) =>
                    v.split("::")[0] == valueId ? `${valueId}::${newValue}` : v
                  )
                  .join("||");
                renderDetailView(currentAttrId);
              } else {
                modal.alert("Lỗi", data.message);
                renderDetailView(currentAttrId);
              }
            }
          );
        });
      }
    });
    renderAttributes();
  }

  const productForm = document.getElementById("product-form");
  if (productForm) {
    const AppData = window.AppData || {};
    const categorySelect = document.getElementById("danh_muc_id");
    const mainFormContent = document.getElementById(
      "product-form-main-content"
    );
    const sideFormContent = document.getElementById(
      "product-form-side-content"
    );
    const productTypeSelect = document.getElementById("product_type");
    const simpleDataEl = document.getElementById("simple-product-data");
    const variableDataEl = document.getElementById("variable-product-data");
    const brandSelectEl = $("#brand-select");
    const techSpecsContainer = document.getElementById("tech-specs-container");
    const variantAttrSelect = $("#variant-attributes-select");
    const addVariantBtn = document.getElementById("add-variant-btn");
    const variantsTableBody = document.querySelector(
      "#variants-table-container tbody"
    );
    const imageGalleryContainer = document.querySelector(
      "#image-gallery .image-preview-container"
    );

    if ($.fn.select2) {
      variantAttrSelect.select2({
        width: "100%",
        placeholder: "Chọn các thuộc tính",
      });
    }

    const renderBrandOptions = () => {
      const selectedCategoryId = categorySelect.value;
      const currentBrandId = brandSelectEl.data("current-brand-id") || 0;
      brandSelectEl.empty();
      let brandsForSelect = AppData.allBrands;
      if (selectedCategoryId && AppData.categoryBrandsMap[selectedCategoryId]) {
        const allowedBrandIds = AppData.categoryBrandsMap[selectedCategoryId];
        brandsForSelect = AppData.allBrands.filter((brand) =>
          allowedBrandIds.includes(brand.id)
        );
      }
      brandSelectEl.append(
        new Option("-- Chọn hoặc nhập thương hiệu mới --", "")
      );
      brandsForSelect.forEach((brand) => {
        brandSelectEl.append(new Option(e(brand.ten_thuong_hieu), brand.id));
      });
      if (currentBrandId > 0) {
        if (!brandsForSelect.some((b) => b.id == currentBrandId)) {
          const currentBrand = AppData.allBrands.find(
            (b) => b.id == currentBrandId
          );
          if (currentBrand) {
            brandSelectEl.append(
              new Option(
                e(currentBrand.ten_thuong_hieu),
                currentBrand.id,
                true,
                true
              )
            );
          }
        }
        brandSelectEl.val(currentBrandId);
      }
      if (brandSelectEl.data("select2")) {
        brandSelectEl.select2("destroy");
      }
      brandSelectEl.select2({
        tags: true,
        width: "100%",
        createTag: (params) => ({
          id: params.term,
          text: params.term,
          newTag: true,
        }),
      });
    };
    const renderTechSpecs = () => {
      techSpecsContainer.innerHTML = "";
      const selectedCategoryId = categorySelect.value;
      if (!selectedCategoryId || !AppData.categoryTechSpecsMap) {
        techSpecsContainer.innerHTML =
          '<p class="text-muted">Vui lòng chọn danh mục để xem thông số.</p>';
        return;
      }
      const specsForCategory =
        AppData.categoryTechSpecsMap[selectedCategoryId] || [];
      if (specsForCategory.length === 0) {
        techSpecsContainer.innerHTML =
          '<p class="text-muted">Không có thông số kỹ thuật nào cho danh mục này.</p>';
        return;
      }
      specsForCategory.forEach((attrId) => {
        const attr = AppData.allTechSpecs.find((a) => a.id === attrId);
        if (attr) {
          const value = AppData.product_attributes[attr.id] || "";
          const group = document.createElement("div");
          group.className = "form-group";
          group.innerHTML = `<label>${e(
            attr.ten_thuoc_tinh
          )}</label><input type="text" class="form-control" name="attributes[${
            attr.id
          }]" value="${e(value)}">`;
          techSpecsContainer.appendChild(group);
        }
      });
    };
    const renderImageGallery = () => {
      if (!imageGalleryContainer) return;
      imageGalleryContainer.innerHTML = "";
      if (!AppData.productImages || AppData.productImages.length === 0) {
        imageGalleryContainer.innerHTML =
          '<p class="text-muted">Chưa có ảnh nào.</p>';
        return;
      }
      AppData.productImages.forEach((img) => {
        const isChecked = parseInt(img.la_anh_dai_dien, 10) === 1;
        const div = document.createElement("div");
        div.className = "image-preview";
        div.id = `image-preview-${img.id}`;
        div.innerHTML = `
                  <label>
                      <img src="${
                        AppConfig.BASE_URL
                      }/public/uploads/products/thumbs/${e(
          img.url_hinh_anh
        )}" alt="Ảnh">
                      <input type="radio" name="main_image" value="${img.id}" ${
          isChecked ? "checked" : ""
        }>
                      <span>Đại diện</span>
                  </label>
                  <button type="button" class="delete-image" data-img-id="${
                    img.id
                  }">&times;</button>
              `;
        imageGalleryContainer.appendChild(div);
      });
      updateAllVariantImageSelects();
    };
    const updateAllVariantImageSelects = () => {
      const selects = variantsTableBody.querySelectorAll(
        ".variant-image-select"
      );
      selects.forEach((select) => {
        const currentVal = select.value;
        let optionsHTML = '<option value="">-- Ảnh chung --</option>';
        if (AppData.productImages) {
          AppData.productImages.forEach((img) => {
            optionsHTML += `<option value="${img.id}">${e(
              img.url_hinh_anh
            )}</option>`;
          });
        }
        select.innerHTML = optionsHTML;
        if (
          currentVal &&
          Array.from(select.options).some((opt) => opt.value == currentVal)
        ) {
          select.value = currentVal;
        }
      });
    };
    const addVariantRow = (variantData = null) => {
      const selectedAttributes = variantAttrSelect.select2("data");
      if (selectedAttributes.length === 0) {
        modal.alert(
          "Chưa chọn thuộc tính",
          "Vui lòng chọn ít nhất một thuộc tính (ví dụ: Màu sắc, Kích thước) trước khi thêm biến thể."
        );
        return;
      }
      const template = document.getElementById("variant-row-template");
      const newRow = template.content.cloneNode(true).querySelector("tr");
      const optionsCell = newRow.querySelector(".variant-options-cell");
      const uniqueIndex = Date.now() + Math.random();
      newRow.querySelectorAll('[name*="__INDEX__"]').forEach((el) => {
        el.name = el.name.replace("__INDEX__", uniqueIndex);
      });
      selectedAttributes.forEach((attr) => {
        const attrId = attr.id;
        const values = AppData.attributeValuesMap[attrId] || [];
        const select = document.createElement("select");
        select.className = "form-control variant-option-select";
        select.dataset.attrId = attrId;
        select.innerHTML =
          `<option value="">-- Chọn ${e(attr.text)} --</option>` +
          values
            .map(
              (val) => `<option value="${val.id}">${e(val.gia_tri)}</option>`
            )
            .join("");
        optionsCell.appendChild(select);
      });
      if (variantData) {
        newRow.querySelector(".variant-id-input").value = variantData.id || 0;
        newRow.querySelector('[name$="[gia]"]').value = variantData.gia || "";
        newRow.querySelector('[name$="[gia_khuyen_mai]"]').value =
          variantData.gia_khuyen_mai || "";
        newRow.querySelector('[name$="[unique_identifiers]"]').value =
          variantData.unique_identifiers || "";
        newRow.querySelector('[name$="[so_luong_ton]"]').value =
          variantData.so_luong_ton || "";
        setTimeout(() => {
          newRow.querySelector(".variant-image-select").value =
            variantData.hinh_anh_id || "";
        }, 0);
        const optionValues = variantData.option_values
          ? String(variantData.option_values).split(",")
          : [];
        optionValues.forEach((valId) => {
          const selectWithValue = newRow.querySelector(
            `.variant-option-select option[value="${valId}"]`
          );
          if (selectWithValue) {
            selectWithValue.parentElement.value = valId;
          }
        });
      }
      updateHiddenOptionsInput(newRow);
      variantsTableBody.appendChild(newRow);
      updateAllVariantImageSelects();
    };
    const updateHiddenOptionsInput = (tableRow) => {
      const hiddenInput = tableRow.querySelector(".variant-options-input");
      const selectedValues = Array.from(
        tableRow.querySelectorAll(".variant-option-select")
      )
        .map((select) => select.value)
        .filter(Boolean)
        .sort((a, b) => a - b);
      hiddenInput.value = selectedValues.join(",");
    };
    const loadExistingVariants = () => {
      if (AppData.isEditMode && AppData.existingVariants) {
        AppData.existingVariants.forEach((variant) => addVariantRow(variant));
      }
    };
    const toggleUI = () => {
      const isCategorySelected = !!categorySelect.value;
      mainFormContent.classList.toggle(
        "form-section-hidden",
        !isCategorySelected
      );
      sideFormContent.classList.toggle(
        "form-section-hidden",
        !isCategorySelected
      );
      if (!isCategorySelected) return;
      const isSimple = productTypeSelect.value === "simple";
      simpleDataEl.classList.toggle("form-section-hidden", !isSimple);
      variableDataEl.classList.toggle("form-section-hidden", isSimple);
    };

    categorySelect.addEventListener("change", () => {
      toggleUI();
      renderBrandOptions();
      renderTechSpecs();
    });
    productTypeSelect.addEventListener("change", toggleUI);
    if (addVariantBtn) {
      addVariantBtn.addEventListener("click", () => addVariantRow());
    }
    if (variantsTableBody) {
      variantsTableBody.addEventListener("click", (e) => {
        if (e.target.classList.contains("delete-variant-btn"))
          e.target.closest("tr").remove();
      });
      variantsTableBody.addEventListener("change", (e) => {
        if (e.target.classList.contains("variant-option-select"))
          updateHiddenOptionsInput(e.target.closest("tr"));
      });
      variantsTableBody.addEventListener("input", (e) => {
        if (e.target.classList.contains("variant-identifier-input")) {
          const count = e.target.value
            .split("\n")
            .filter((line) => line.trim() !== "").length;
          const stockInput = e.target
            .closest("tr")
            .querySelector('[name$="[so_luong_ton]"]');
          if (stockInput) stockInput.value = count;
        }
      });
    }

    productForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
      fetch(this.action, { method: "POST", body: formData })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            modal.alert("Thành công!", data.message);
            if (data.all_images) {
              AppData.productImages = data.all_images;
              renderImageGallery();
            }
            if (data.is_new_product && data.redirect_url) {
              window.history.replaceState({}, "", data.redirect_url);
              productForm.action = `${AppConfig.BASE_URL}/admin/ajax/handle/save_product`;
              productForm.querySelector('input[name="id"]').value =
                data.product_id;
            }
          } else {
            modal.alert("Lỗi!", data.message || "Đã có lỗi xảy ra.");
          }
        })
        .catch((err) => {
          console.error("Submit Error:", err);
          modal.alert("Lỗi nghiêm trọng!", "Không thể kết nối đến máy chủ.");
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="fas fa-save"></i> Lưu sản phẩm';
        });
    });

    const initialize = () => {
      toggleUI();
      if (categorySelect.value) {
        renderBrandOptions();
        renderTechSpecs();
      }
      if (
        AppData.isEditMode &&
        AppData.product &&
        AppData.product.loai_san_pham === "variable" &&
        AppData.selectedVariantAttributes
      ) {
        variantAttrSelect
          .val(AppData.selectedVariantAttributes)
          .trigger("change");
      }
      renderImageGallery();
      loadExistingVariants();
    };
    initialize();
  }
});
