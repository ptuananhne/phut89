const Product = require('../models/product');
const Category = require('../models/category');

const PRODUCTS_PER_PAGE = 8;

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
        const [
            [featuredProducts],
            [allCategories]
        ] = await Promise.all([
            Product.fetchFeatured(12),
            Category.fetchAll()
        ]);

        const categoriesForHomepage = await Promise.all(allCategories.map(async (category) => {
            const [products] = await Product.fetchByCategoryId(category.id, 7);
            return {
                ...category,
                products: products
            };
        }));
        
        const banners = [
            { title: 'Banner 1', link: '#', imageUrl: 'https://placehold.co/1200x400/2fdf18/ffffff?text=Banner+1' },
            { title: 'Banner 2', link: '#', imageUrl: 'https://placehold.co/1200x400/333333/ffffff?text=Banner+2' }
        ];

        res.render('shop/index', {
            pageTitle: 'Trang Chủ',
            banners: banners,
            featuredProducts: featuredProducts,
            categories: categoriesForHomepage,
        });

    } catch (err) {
        console.log(err);
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
        
        // Lấy đồng thời các dữ liệu liên quan
        const [
            [images],
            [attributes],
            [relatedProducts]
        ] = await Promise.all([
            Product.fetchImages(product.id),
            Product.fetchAttributes(product.id), // Lấy thông số kỹ thuật
            Product.fetchRelated(product.category_id, product.id)
        ]);
        
        // Tăng lượt xem cho sản phẩm
        await Product.incrementViewCount(product.id);

        res.render('shop/sanpham', {
            pageTitle: product.name,
            product: product,
            images: images,
            attributes: attributes, // Truyền attributes cho view
            relatedProducts: relatedProducts
        });
    } catch (err)
 {
        console.log(err);
        next(err);
    }
};

exports.getCategory = async (req, res, next) => {
    try {
        console.log('--- [CONTROLLER] Bắt đầu getCategory ---');
        const categorySlug = req.params.slug;
        const page = +req.query.page || 1;
        const brandId = +req.query.brand || null;
        const sortOption = req.query.sort || 'view_desc';

        console.log('[CONTROLLER] Các bộ lọc đầu vào:', { page, brandId, sortOption });

        const [categoryRows] = await Category.findBySlug(categorySlug);
        if (categoryRows.length === 0) {
            return res.status(404).render('404', { pageTitle: 'Danh mục không tồn tại' });
        }
        const category = categoryRows[0];
        
        const [brands] = await Category.fetchBrandsForCategory(category.id);
        
        const filterOptions = { categoryId: category.id, brandId: brandId };
        const [[{ total }]] = await Product.countFilterByCategory(filterOptions);
        const totalPages = Math.ceil(total / PRODUCTS_PER_PAGE);
        const offset = (page - 1) * PRODUCTS_PER_PAGE;

        console.log('[CONTROLLER] Tính toán phân trang:', { totalProducts: total, totalPages, offset });
        
        const [products] = await Product.filterByCategory({
            ...filterOptions,
            sort: sortOption,
            limit: PRODUCTS_PER_PAGE,
            offset: offset,
        });
        
        console.log(`[CONTROLLER] Đã lấy được ${products.length} sản phẩm từ database.`);
        
        let paginationUrl = `/danh-muc/${category.slug}?sort=${sortOption}`;
        if (brandId) {
            paginationUrl += `&brand=${brandId}`;
        }
        
        res.render('shop/danhmuc', {
            pageTitle: `Danh mục: ${category.name}`,
            category: category,
            products: products,
            brands: brands,
            currentBrandId: brandId,
            currentSort: sortOption,
            pagination: generatePagination(page, totalPages, paginationUrl)
        });
    } catch (err) {
        console.log('[CONTROLLER LỖI]', err);
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
        const paginationUrl = `/timkiem?q=${query}`;

        res.render('shop/timkiem', {
            pageTitle: `Tìm kiếm cho: ${query}`,
            searchQuery: query,
            products: products,
            totalProducts: totalProducts,
            pagination: generatePagination(page, totalPages, paginationUrl)
        });
    } catch (err) {
        next(err);
    }
};
