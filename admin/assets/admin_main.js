document.addEventListener("DOMContentLoaded", function () {
  const BASE_URL = document
    .querySelector('script[src*="admin_main.js"]')
    .src.split("/admin/")[0];
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
      this.confirmBtn.addEventListener("click", () => this._handleConfirm());
    },
    _handleClose(value) {
      this.el.classList.remove("active");
      if (this._resolve) this._resolve(value);
    },
    _handleConfirm() {
      this._handleClose(true);
    },
    alert(title, text) {
      if (!this.el) {
        window.alert(title + "\n" + text);
        return;
      }
      this.title.textContent = title;
      this.body.textContent = text;
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
      this.body.textContent = text;
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
  if (sortableContainer && typeof Sortable !== "undefined") {
    new Sortable(sortableContainer, {
      animation: 150,
      handle: ".sort-handle",
      onEnd: function () {
        const order = Array.from(this.el.children).map((row) => row.dataset.id);
        fetch("categories.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({ action: "update_order", order: order }),
        })
          .then((res) =>
            res.ok ? res.json() : res.json().then((err) => Promise.reject(err))
          )
          .then((data) => {
            if (data.status !== "success")
              modal.alert("Lỗi Sắp xếp", "Không thể lưu thứ tự mới.");
          })
          .catch((error) => {
            console.error("Sort Error:", error);
            modal.alert("Lỗi Nghiêm trọng", error.message || "Lỗi kết nối.");
          });
      },
    });
  }

  const categoryForm = document.getElementById("category-form");
  if (categoryForm) {
    categoryForm.addEventListener("click", async function (e) {
      const deleteBtn = e.target.closest(".btn-delete-term");
      if (!deleteBtn) return;
      const termId = deleteBtn.dataset.termId;
      const termType = deleteBtn.dataset.termType;
      const termName = deleteBtn.dataset.termName;
      const confirmText = termType === "brand" ? "thương hiệu" : "thuộc tính";
      if (
        await modal.confirm(
          "Xác nhận xóa",
          `Bạn có chắc muốn xóa vĩnh viễn ${confirmText} "${termName}"?`
        )
      ) {
        const formData = new FormData();
        formData.append("action", "delete_taxonomy_term");
        formData.append("id", termId);
        formData.append("type", termType);
        formData.append(
          "csrf_token",
          categoryForm.querySelector('input[name="csrf_token"]').value
        );
        fetch("ajax_handler.php", { method: "POST", body: formData })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              modal.alert("Thành công", data.message || "Đã xóa thành công.");
              deleteBtn.parentElement.remove();
            } else {
              modal.alert("Lỗi", data.message || "Không thể xóa mục này.");
            }
          })
          .catch((err) => {
            modal.alert("Lỗi hệ thống", "Không thể kết nối đến máy chủ.");
            console.error(err);
          });
      }
    });
  }

  const attributesContainer = document.querySelector(".attributes-container");
  if (attributesContainer) {
    const attrList = document.getElementById("attributes-list");
    const detailBody = document.getElementById("detail-body");
    const detailTitle = document.getElementById("detail-title");
    const addAttrForm = document.getElementById("add-attribute-form");
    let currentAttrId = null;
    const attrItemTemplate = document.getElementById("attribute-item-template");
    const detailTemplate = document.getElementById("attribute-detail-template");
    const valueItemTemplate = document.getElementById("value-item-template");
    const renderAttributes = () => {
      attrList.innerHTML = "";
      attributesData.forEach((attr) => {
        const template = attrItemTemplate.content.cloneNode(true);
        template.querySelector("li").dataset.id = attr.id;
        template.querySelector(".attribute-name").textContent =
          attr.ten_thuoc_tinh;
        attrList.appendChild(template);
      });
    };
    const renderDetailView = (attrId) => {
      currentAttrId = attrId;
      attrList
        .querySelectorAll("li")
        .forEach((li) => li.classList.remove("active"));
      attrList
        .querySelector(`li[data-id="${attrId}"]`)
        ?.classList.add("active");
      const attr = attributesData.find((a) => a.id === attrId);
      if (!attr) {
        detailBody.innerHTML =
          '<div class="attributes-detail-placeholder"><p>Không tìm thấy thuộc tính.</p></div>';
        return;
      }
      detailTitle.textContent = `Giá trị cho: ${attr.ten_thuoc_tinh}`;
      const detail = detailTemplate.content.cloneNode(true);
      detail.querySelector(".text-primary").textContent = attr.ten_thuoc_tinh;
      const valuesListEl = detail.getElementById("values-list");
      valuesListEl.innerHTML = "";
      if (attr.values) {
        const values = attr.values
          .split("||")
          .map((v) => ({ id: v.split("::")[0], name: v.split("::")[1] }));
        values.forEach((val) => {
          const valueTpl = valueItemTemplate.content.cloneNode(true);
          valueTpl.querySelector("li").dataset.id = val.id;
          valueTpl.querySelector(".value-name").textContent = val.name;
          valuesListEl.appendChild(valueTpl);
        });
      } else {
        valuesListEl.innerHTML =
          '<p class="text-muted">Chưa có giá trị nào.</p>';
      }
      detailBody.innerHTML = "";
      detailBody.appendChild(detail);
    };
    const performAjax = (action, data) => {
      const formData = new FormData();
      formData.append("action", action);
      formData.append("csrf_token", csrfToken);
      for (const key in data) {
        formData.append(key, data[key]);
      }
      return fetch("ajax_handler.php", { method: "POST", body: formData }).then(
        (res) => res.json()
      );
    };
    const enableInlineEdit = (element, onSave) => {
      const originalText = element.textContent;
      const input = document.createElement("input");
      input.type = "text";
      input.value = originalText;
      input.className = "inline-edit-input";
      element.replaceWith(input);
      input.focus();
      const saveChanges = () => {
        const newText = input.value.trim();
        if (newText && newText !== originalText) {
          onSave(newText);
        } else {
          const newSpan = document.createElement("span");
          newSpan.className = element.className;
          newSpan.title = element.title;
          newSpan.textContent = originalText;
          input.replaceWith(newSpan);
        }
      };
      input.addEventListener("blur", saveChanges);
      input.addEventListener("keydown", (e) => {
        if (e.key === "Enter") input.blur();
      });
    };
    addAttrForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const nameInput = document.getElementById("new-attribute-name");
      const name = nameInput.value.trim();
      if (!name) return;
      performAjax("add_attribute", { name: name }).then((data) => {
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
    attrList.addEventListener("click", (e) => {
      const li = e.target.closest("li");
      if (!li) return;
      const attrId = parseInt(li.dataset.id);
      if (e.target.classList.contains("delete-attribute")) {
        modal
          .confirm(
            "Xác nhận xóa",
            "Xóa thuộc tính này sẽ xóa tất cả giá trị con. Bạn có chắc chắn?"
          )
          .then((confirmed) => {
            if (confirmed) {
              performAjax("delete_attribute", { id: attrId }).then((data) => {
                if (data.success) {
                  attributesData = attributesData.filter(
                    (a) => a.id !== attrId
                  );
                  renderAttributes();
                  detailBody.innerHTML =
                    '<div class="attributes-detail-placeholder"><p>Chọn một thuộc tính bên trái...</p></div>';
                  detailTitle.textContent = "Giá trị";
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
                if (currentAttrId === attrId)
                  detailTitle.textContent = `Giá trị cho: ${newName}`;
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
    detailBody.addEventListener("click", (e) => {
      if (e.target.classList.contains("delete-value")) {
        const valueLi = e.target.closest("li");
        const valueId = parseInt(valueLi.dataset.id);
        modal
          .confirm("Xác nhận xóa", "Bạn có muốn xóa giá trị này?")
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
                  valueLi.remove();
                } else {
                  modal.alert("Lỗi", data.message);
                }
              });
            }
          });
      } else if (e.target.classList.contains("value-name")) {
        const valueId = parseInt(e.target.closest("li").dataset.id);
        enableInlineEdit(e.target, (newValue) => {
          performAjax("update_value", { id: valueId, value: newValue }).then(
            (data) => {
              if (data.success) {
                const attr = attributesData.find((a) => a.id === currentAttrId);
                attr.values = attr.values
                  .split("||")
                  .map((v) => {
                    return v.split("::")[0] == valueId
                      ? `${valueId}::${newValue}`
                      : v;
                  })
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
    detailBody.addEventListener("submit", (e) => {
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
          if (attr.values) {
            attr.values += "||" + newValueString;
          } else {
            attr.values = newValueString;
          }
          renderDetailView(currentAttrId);
          valueInput.value = "";
        } else {
          modal.alert("Lỗi", data.message);
        }
      });
    });
    renderAttributes();
  }

  const productForm = document.getElementById("product-form");
  if (productForm) {
    if (typeof $ !== "undefined" && $.fn.select2) {
      $(".select2").select2({
        width: "100%",
        placeholder: "Chọn các thuộc tính",
      });
    }
    const categorySelect = document.getElementById("danh_muc_id");
    const brandSelectEl = document.getElementById("brand-select");
    const mainFormContent = document.getElementById(
      "product-form-main-content"
    );
    const techSpecsContainer = document.getElementById("tech-specs-container");
    const productTypeSelect = document.getElementById("product_type");
    const simpleDataEl = document.getElementById("simple-product-data");
    const variableDataEl = document.getElementById("variable-product-data");
    const simpleIdentifierInput = document.getElementById(
      "ma_dinh_danh_duy_nhat"
    );
    const variantAttrSelect = $("#variant-attributes-select");
    const variantOptionsContainer = document.getElementById(
      "variant-options-container"
    );
    const generateBtn = document.getElementById("generate-variants-btn");
    const variantsTableContainer = document.getElementById(
      "variants-table-container"
    );
    const imageGallery = document.getElementById("image-gallery");

    const initCreatableBrandSelect = () => {
      const selectedCategoryId = categorySelect.value;
      const currentBrandId = brandSelectEl.dataset.currentBrandId || "0";
      if ($(brandSelectEl).hasClass("select2-hidden-accessible")) {
        $(brandSelectEl).select2("destroy");
      }
      let brandsForSelect2 = [];
      if (
        selectedCategoryId &&
        typeof categoryBrandsMap !== "undefined" &&
        categoryBrandsMap[selectedCategoryId]
      ) {
        const allowedBrandIds = categoryBrandsMap[selectedCategoryId];
        const filteredBrands = allBrands.filter((brand) =>
          allowedBrandIds.includes(brand.id)
        );
        brandsForSelect2 = filteredBrands.map((brand) => ({
          id: brand.id,
          text: brand.ten_thuong_hieu,
        }));
      } else {
        brandsForSelect2 = allBrands.map((brand) => ({
          id: brand.id,
          text: brand.ten_thuong_hieu,
        }));
      }
      $(brandSelectEl).select2({
        data: brandsForSelect2,
        tags: true,
        placeholder: "-- Chọn hoặc nhập thương hiệu mới --",
        createTag: function (params) {
          const term = $.trim(params.term);
          if (term === "") return null;
          return { id: term, text: term, newTag: true };
        },
      });
      if (currentBrandId !== "0") {
        const brandExists = brandsForSelect2.some(
          (b) => String(b.id) === String(currentBrandId)
        );
        if (!brandExists) {
          const currentBrand = allBrands.find(
            (b) => String(b.id) === String(currentBrandId)
          );
          if (currentBrand) {
            const newOption = new Option(
              currentBrand.ten_thuong_hieu,
              currentBrand.id,
              true,
              true
            );
            $(brandSelectEl).append(newOption);
          }
        }
        $(brandSelectEl).val(currentBrandId).trigger("change");
      } else {
        $(brandSelectEl).val(null).trigger("change");
      }
    };

    function toggleMainFormVisibility() {
      mainFormContent.classList.toggle(
        "form-section-hidden",
        !categorySelect.value
      );
    }
    function updateBrandOptions() {
      initCreatableBrandSelect();
    }
    function renderTechSpecs() {
      if (!techSpecsContainer) return;
      techSpecsContainer.innerHTML = "";
      const selectedCategoryId = categorySelect.value;
      if (!selectedCategoryId || typeof categoryTechSpecsMap === "undefined")
        return;
      const allowedAttrIds = categoryTechSpecsMap[selectedCategoryId] || [];
      allTechSpecs.forEach((attr) => {
        if (allowedAttrIds.includes(attr.id)) {
          const currentValue =
            typeof productTechSpecs !== "undefined" && productTechSpecs[attr.id]
              ? productTechSpecs[attr.id]
              : "";
          techSpecsContainer.innerHTML += `<div class="form-group"><label>${attr.ten_thuoc_tinh}</label><input type="text" class="form-control" name="attributes[${attr.id}]" value="${currentValue}"></div>`;
        }
      });
    }
    function toggleProductTypeUI() {
      const isSimple = productTypeSelect.value === "simple";
      simpleDataEl.classList.toggle("form-section-hidden", !isSimple);
      variableDataEl.classList.toggle("form-section-hidden", isSimple);
      if (simpleIdentifierInput) {
        simpleIdentifierInput.disabled = !isSimple;
        if (!isSimple) {
          simpleIdentifierInput.value = "";
        }
      }
    }
    function renderAttributeOptions() {
      variantOptionsContainer.innerHTML = "";
      const selectedAttrs = variantAttrSelect.select2("data");
      selectedAttrs.forEach((attr) => {
        const attrId = attr.id;
        const attrName = attr.text;
        const values = attributeValuesMap[attrId] || [];
        let optionsHTML = "";
        if (values.length > 0) {
          values.forEach((val) => {
            optionsHTML += `<option value="${val.id}">${val.gia_tri}</option>`;
          });
        }
        const selectHTML = `<div class="form-group"><label>${attrName}</label><select class="form-control variant-option-select" multiple data-attr-id="${attrId}">${optionsHTML}</select></div>`;
        variantOptionsContainer.innerHTML += selectHTML;
      });
      $(".variant-option-select").select2({
        width: "100%",
        placeholder: `Chọn các giá trị`,
      });
    }
    function getCombinations(arrays) {
      if (!arrays || arrays.length === 0) return [];
      let result = arrays[0].map((item) => [item]);
      for (let i = 1; i < arrays.length; i++) {
        let nextResult = [];
        result.forEach((r) => {
          arrays[i].forEach((item) => {
            nextResult.push([...r, item]);
          });
        });
        result = nextResult;
      }
      return result;
    }
    function renderVariantsTable(combinations, oldData = {}) {
      const tableHTML = `<table class="table"><thead><tr><th>Biến thể</th><th>Giá</th><th>Giá KM</th><th>Mã định danh *</th><th>Số lượng tồn</th><th>Ảnh</th><th class="actions"></th></tr></thead><tbody></tbody></table>`;
      variantsTableContainer.innerHTML = tableHTML;
      const tbody = variantsTableContainer.querySelector("tbody");
      const template = document.getElementById("variant-row-template");
      if (!template) return;
      combinations.forEach((combo, index) => {
        const newRow = template.content.cloneNode(true);
        const optionsText = combo.map((opt) => opt.text).join(" / ");
        const optionsFlat = combo
          .map((opt) => opt.id)
          .sort((a, b) => a - b)
          .join(",");
        const uniqueIndex = Date.now() + index;
        newRow.querySelector('[name*="[options_text]"]').value = optionsText;
        newRow.querySelector(".variant-options-input").value = optionsFlat;
        newRow.querySelectorAll("[name]").forEach((el) => {
          el.name = el.name.replace("__INDEX__", uniqueIndex);
        });
        const imageSelect = newRow.querySelector(".variant-image-select");
        updateVariantImageSelects([imageSelect]);
        if (oldData[optionsFlat]) {
          const data = oldData[optionsFlat];
          newRow.querySelector(".variant-id-input").value = data.id || 0;
          newRow.querySelector('[name*="[gia]"]').value = data.gia || "";
          newRow.querySelector('[name*="[gia_khuyen_mai]"]').value =
            data.gia_khuyen_mai || "";
          newRow.querySelector('[name*="[unique_identifiers]"]').value =
            data.unique_identifiers || "";
          newRow.querySelector('[name*="[so_luong_ton]"]').value =
            data.so_luong_ton || 0;
          if (data.hinh_anh_id) imageSelect.value = data.hinh_anh_id;
        }
        tbody.appendChild(newRow);
      });
    }
    function loadExistingVariantsForEdit() {
      if (
        typeof existingVariants === "undefined" ||
        existingVariants.length === 0
      )
        return;
      const combinations = existingVariants.map((variant) => {
        if (!variant.option_values) return [];
        const optionIds = String(variant.option_values).split(",");
        return optionIds
          .map((optId) => {
            for (const attrId in attributeValuesMap) {
              const found = attributeValuesMap[attrId].find(
                (v) => String(v.id) === String(optId)
              );
              if (found) return { id: found.id, text: found.gia_tri };
            }
            return null;
          })
          .filter(Boolean);
      });
      const existingData = {};
      existingVariants.forEach((variant) => {
        const optionsKey = String(variant.option_values)
          .split(",")
          .sort((a, b) => a - b)
          .join(",");
        existingData[optionsKey] = variant;
      });
      renderVariantsTable(combinations, existingData);
    }
    function updateImageGallery() {
      const container = imageGallery.querySelector(".image-preview-container");
      if (!container) return;
      container.innerHTML = "";
      if (productImages.length === 0) {
        container.innerHTML = '<p class="text-muted">Chưa có ảnh nào.</p>';
        return;
      }
      productImages.forEach((img) => {
        const isChecked = parseInt(img.la_anh_dai_dien, 10) === 1;
        const checkedAttr = isChecked ? "checked" : "";
        const csrfToken = productForm.querySelector(
          'input[name="csrf_token"]'
        ).value;
        const productId = productForm.querySelector('input[name="id"]').value;
        container.innerHTML += `<div class="image-preview" id="image-preview-${img.id}"><label><img src="${BASE_URL}/uploads/products/thumbs/${img.url_hinh_anh}" alt="Ảnh"><input type="radio" name="main_image" value="${img.id}" ${checkedAttr}><span>Đại diện</span></label><button type="button" class="delete-image" data-img-id="${img.id}" data-product-id="${productId}" data-csrf="${csrfToken}">&times;</button></div>`;
      });
    }
    function updateVariantImageSelects(selects = null) {
      if (selects === null) {
        selects = document.querySelectorAll(".variant-image-select");
      }
      if (!selects.length) return;
      selects.forEach((select) => {
        const currentVal = select.value;
        let optionsHTML = '<option value="">-- Ảnh chung --</option>';
        if (typeof productImages !== "undefined") {
          productImages.forEach((img) => {
            optionsHTML += `<option value="${img.id}">${img.url_hinh_anh}</option>`;
          });
        }
        select.innerHTML = optionsHTML;
        if (
          currentVal &&
          select.querySelector(`option[value="${currentVal}"]`)
        ) {
          select.value = currentVal;
        }
      });
    }

    categorySelect.addEventListener("change", () => {
      toggleMainFormVisibility();
      if (categorySelect.value) {
        updateBrandOptions();
        renderTechSpecs();
      }
    });
    productTypeSelect.addEventListener("change", toggleProductTypeUI);
    variantAttrSelect.on("change", () => {
      renderAttributeOptions();
      initializeVariantData();
    });

    if (generateBtn) {
      generateBtn.addEventListener("click", async () => {
        const hasExistingData =
          variantsTableContainer.querySelector("tbody tr");
        if (hasExistingData) {
          if (
            !(await modal.confirm(
              "Tạo lại biến thể?",
              "Thao tác này sẽ tạo lại bảng biến thể. Dữ liệu đã nhập sẽ được giữ lại nếu có tổ hợp tương ứng. Tiếp tục?"
            ))
          )
            return;
        }
        const oldData = {};
        if (hasExistingData) {
          variantsTableContainer.querySelectorAll("tbody tr").forEach((row) => {
            const optionsKey = row.querySelector(
              ".variant-options-input"
            ).value;
            if (optionsKey) {
              oldData[optionsKey] = {
                id: row.querySelector(".variant-id-input").value,
                gia: row.querySelector('[name*="[gia]"]').value,
                gia_khuyen_mai: row.querySelector('[name*="[gia_khuyen_mai]"]')
                  .value,
                unique_identifiers: row.querySelector(
                  '[name*="[unique_identifiers]"]'
                ).value,
                so_luong_ton: row.querySelector('[name*="[so_luong_ton]"]')
                  .value,
                hinh_anh_id: row.querySelector(".variant-image-select").value,
              };
            }
          });
        }
        const selectedOptionsByAttr = {};
        variantOptionsContainer
          .querySelectorAll(".variant-option-select")
          .forEach((select) => {
            const attrId = select.dataset.attrId;
            const selectedData = $(select).select2("data");
            if (selectedData && selectedData.length > 0) {
              selectedOptionsByAttr[attrId] = selectedData.map((d) => ({
                id: d.id,
                text: d.text,
              }));
            }
          });
        const allCombinations = getCombinations(
          Object.values(selectedOptionsByAttr)
        );
        renderVariantsTable(allCombinations, oldData);
      });
    }

    if (variantsTableContainer) {
      variantsTableContainer.addEventListener("input", function (e) {
        if (e.target.classList.contains("variant-identifier-input")) {
          const count = e.target.value
            .split("\n")
            .filter((line) => line.trim() !== "").length;
          const row = e.target.closest("tr");
          if (row) {
            const stockInput = row.querySelector('[name*="[so_luong_ton]"]');
            if (stockInput) stockInput.value = count;
          }
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
      fetch("ajax_handler.php", { method: "POST", body: formData })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            modal.alert("Thành công!", data.message);
            if (data.all_images) productImages = data.all_images;
            updateImageGallery();
            updateVariantImageSelects();
            document.getElementById("hinh_anh").value = "";
            if (data.is_new_product && data.redirect_url) {
              modal.el.querySelector("#modal-close-btn").addEventListener(
                "click",
                () => {
                  window.location.href = data.redirect_url;
                },
                { once: true }
              );
              window.history.replaceState({}, "", data.redirect_url);
              productForm.querySelector('input[name="id"]').value =
                data.product_id;
            }
          } else {
            modal.alert("Lỗi!", data.message || "Không thể lưu sản phẩm.");
          }
        })
        .catch((error) => {
          console.error("Fetch Error:", error);
          modal.alert("Lỗi!", "Lỗi kết nối mạng hoặc lỗi máy chủ.");
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="fas fa-save"></i> Lưu sản phẩm';
        });
    });

    if (imageGallery) {
      imageGallery.addEventListener("click", async function (e) {
        const deleteBtn = e.target.closest(".delete-image");
        if (deleteBtn) {
          e.preventDefault();
          if (
            !(await modal.confirm(
              "Xác nhận xóa",
              "Bạn có chắc muốn xóa ảnh này?"
            ))
          )
            return;
          const formData = new FormData();
          formData.append("action", "delete_image");
          formData.append("img_id", deleteBtn.dataset.imgId);
          formData.append("product_id", deleteBtn.dataset.productId);
          formData.append("csrf_token", deleteBtn.dataset.csrf);
          fetch("ajax_handler.php", { method: "POST", body: formData })
            .then((res) => res.json())
            .then((data) => {
              if (data.success) {
                productImages = productImages.filter(
                  (img) => String(img.id) !== deleteBtn.dataset.imgId
                );
                updateImageGallery();
                updateVariantImageSelects();
              } else {
                modal.alert("Lỗi!", data.message);
              }
            })
            .catch(() => modal.alert("Lỗi!", "Lỗi kết nối mạng."));
        }
      });
    }
    if (variantsTableContainer) {
      variantsTableContainer.addEventListener("click", function (e) {
        if (e.target.classList.contains("delete-variant-btn")) {
          e.target.closest("tr").remove();
        }
      });
    }

    function initializeForm() {
      if (isEditMode) {
        if (initialCategoryId) categorySelect.value = initialCategoryId;
        toggleMainFormVisibility();
        updateBrandOptions();
        renderTechSpecs();
        toggleProductTypeUI();
        if (initialProductType === "variable") {
          variantAttrSelect.trigger("change");
        }
      } else {
        toggleMainFormVisibility();
        updateBrandOptions();
      }
    }
    function initializeVariantData() {
      if (isEditMode && initialProductType === "variable") {
        if (
          typeof existingVariants !== "undefined" &&
          existingVariants.length > 0
        ) {
          const usedOptionIds = new Set(
            existingVariants.flatMap((v) =>
              v.option_values ? v.option_values.split(",") : []
            )
          );
          variantOptionsContainer
            .querySelectorAll(".variant-option-select")
            .forEach((select) => {
              const idsToSelect = [];
              select.querySelectorAll("option").forEach((option) => {
                if (usedOptionIds.has(option.value)) {
                  idsToSelect.push(option.value);
                }
              });
              if (idsToSelect.length > 0) {
                $(select).val(idsToSelect).trigger("change");
              }
            });
        }
        loadExistingVariantsForEdit();
      }
    }
    initializeForm();
  }
});
