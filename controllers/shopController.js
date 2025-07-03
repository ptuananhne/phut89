// ----- FILE: controllers/shopController.js -----

const Product = require('../models/product');
const Category = require('../models/category');

const PRODUCTS_PER_PAGE = 12;

// Hàm helper để tạo dữ liệu phân trang
const generatePagination = (currentPage, totalPages, baseUrl = '') => {
    return {
        currentPage: currentPage,
        hasNextPage: currentPage < totalPages,
        hasPreviousPage: currentPage > 1,
        nextPage: currentPage + 1,
        previousPage: currentPage - 1,
        lastPage: totalPages,
        baseUrl: baseUrl
    };
};

exports.getIndex = async (req, res, next) => {
    try {
        const page = +req.query.page || 1;
        const offset = (page - 1) * PRODUCTS_PER_PAGE;
        
        const [[{ total }]] = await Product.countAll();
        const totalPages = Math.ceil(total / PRODUCTS_PER_PAGE);

        const [products] = await Product.fetchAll(PRODUCTS_PER_PAGE, offset);

        res.render('shop/index', {
            pageTitle: 'Trang Chủ',
            products: products,
            pagination: generatePagination(page, totalPages, '/')
        });
    } catch (err) {
        next(err);
    }
};

exports.getProduct = async (req, res, next) => {
    try {
        const productSlug = req.params.slug;
        const [productRows] = await Product.findBySlug(productSlug);
        
        if (productRows.length === 0) {
            return res.status(404).render('404', { pageTitle: 'Sản phẩm không tồn tại' });
        }
        
        const product = productRows[0];
        // Giả sử chưa có các bảng này, ta truyền mảng rỗng
        const images = []; // const [images] = await Product.fetchImages(product.id);
        const attributes = []; // const [attributes] = await Product.fetchAttributes(product.id);
        const [relatedProducts] = await Product.fetchRelated(product.category_id, product.id);

        res.render('shop/sanpham', {
            pageTitle: product.name,
            product: product,
            images: images,
            attributes: attributes,
            relatedProducts: relatedProducts
        });
    } catch (err) {
        next(err);
    }
};

exports.getCategory = async (req, res, next) => {
    try {
        const categorySlug = req.params.slug;
        const page = +req.query.page || 1;
        
        const [categoryRows] = await Category.findBySlug(categorySlug);
        if (categoryRows.length === 0) {
            return res.status(404).render('404', { pageTitle: 'Danh mục không tồn tại' });
        }
        const category = categoryRows[0];
        
        // Giả sử chưa có bảng brands, truyền mảng rỗng
        const brands = []; // const [brands] = await Category.fetchBrandsForCategory(category.id);
        
        const [[{ total }]] = await Product.countFilterByCategory({ categoryId: category.id });
        const totalPages = Math.ceil(total / PRODUCTS_PER_PAGE);
        const offset = (page - 1) * PRODUCTS_PER_PAGE;
        
        const [products] = await Product.filterByCategory({
            categoryId: category.id,
            limit: PRODUCTS_PER_PAGE,
            offset: offset,
            sort: req.query.sort || 'default'
        });
        
        res.render('shop/danhmuc', {
            pageTitle: `Danh mục: ${category.name}`,
            category: category,
            products: products,
            brands: brands,
            pagination: generatePagination(page, totalPages, `/danh-muc/${category.slug}`)
        });
    } catch (err) {
        next(err);
    }
};

exports.getSearch = async (req, res, next) => {
    try {
        const query = req.query.q || '';
        const page = +req.query.page || 1;
        let products = [];
        let totalProducts = 0;

        if (query) {
            const [[{ total }]] = await Product.countSearch(query);
            totalProducts = total;
            const offset = (page - 1) * PRODUCTS_PER_PAGE;
            const [foundProducts] = await Product.search(query, PRODUCTS_PER_PAGE, offset);
            products = foundProducts;
        }

        const totalPages = Math.ceil(totalProducts / PRODUCTS_PER_PAGE);

        res.render('shop/timkiem', {
            pageTitle: `Tìm kiếm cho: ${query}`,
            searchQuery: query,
            products: products,
            totalProducts: totalProducts,
            pagination: generatePagination(page, totalPages, `/timkiem?q=${query}`)
        });
    } catch (err) {
        next(err);
    }
};