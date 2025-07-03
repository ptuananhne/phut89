// ----- FILE: controllers/shopController.js -----

const Product = require('../models/product');
const Category = require('../models/category');

const PRODUCTS_PER_PAGE = 12;

// Hàm helper để tạo dữ liệu phân trang
const generatePagination = (currentPage, totalPages) => {
    return {
        currentPage: currentPage,
        hasNextPage: currentPage < totalPages,
        hasPreviousPage: currentPage > 1,
        nextPage: currentPage + 1,
        previousPage: currentPage - 1,
        lastPage: totalPages,
    };
};

exports.getIndex = async (req, res, next) => {
    try {
        const [[{ total }]] = await Product.countAll();
        const totalPages = Math.ceil(total / PRODUCTS_PER_PAGE);

        const [products] = await Product.fetchAll(PRODUCTS_PER_PAGE, 0);

        const banners = [
            { title: 'Banner 1', link: '#', imageUrl: 'https://placehold.co/1200x400/2fdf18/ffffff?text=Banner+1' },
            { title: 'Banner 2', link: '#', imageUrl: 'https://placehold.co/1200x400/333333/ffffff?text=Banner+2' }
        ];

        res.render('shop/index', {
            pageTitle: 'Trang Chủ',
            banners: banners,
            products: products,
            pagination: generatePagination(1, totalPages)
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
        const [images] = await Product.fetchImages(product.id);
        const [attributes] = await Product.fetchAttributes(product.id);
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
        const [brands] = await Category.fetchBrandsForCategory(category.id);
        
        const [[{ total }]] = await Product.countFilterByCategory({ categoryId: category.id });
        const totalPages = Math.ceil(total / PRODUCTS_PER_PAGE);
        const offset = (page - 1) * PRODUCTS_PER_PAGE;
        
        const [products] = await Product.filterByCategory({
            categoryId: category.id,
            limit: PRODUCTS_PER_PAGE,
            offset: offset
        });
        
        res.render('shop/danhmuc', {
            pageTitle: `Danh mục: ${category.name}`,
            category: category,
            products: products,
            brands: brands,
            pagination: generatePagination(page, totalPages)
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
            pagination: generatePagination(page, totalPages)
        });
    } catch (err) {
        next(err);
    }
};
