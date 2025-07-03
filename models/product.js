const db = require('../util/database');

module.exports = class Product {
    // Lấy tất cả sản phẩm (hỗ trợ phân trang)
    static fetchAll(limit, offset) {
        const intLimit = parseInt(limit, 10) || 12;
        const intOffset = parseInt(offset, 10) || 0;
        return db.execute(`SELECT * FROM products ORDER BY created_at DESC LIMIT ${intOffset}, ${intLimit}`);
    }

    // Đếm tổng số sản phẩm
    static countAll() {
        return db.execute('SELECT COUNT(id) as total FROM products');
    }
    
    // Tìm sản phẩm bằng slug
    static findBySlug(slug) {
        const sql = `
            SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.slug = ?
        `;
        return db.execute(sql, [slug]);
    }
    
    // Lấy các sản phẩm nổi bật
    static fetchFeatured(limit) {
        const intLimit = parseInt(limit, 10) || 12;
        return db.execute(`SELECT * FROM products ORDER BY view_count DESC, created_at DESC LIMIT ${intLimit}`);
    }
     // Tăng lượt xem cho sản phẩm
    static incrementViewCount(productId) {
        return db.execute('UPDATE products SET view_count = view_count + 1 WHERE id = ?', [productId]);
    }
    // Lấy sản phẩm theo danh mục (ĐÃ SỬA LỖI)
    static fetchByCategoryId(categoryId, limit) {
        const intLimit = parseInt(limit, 10) || 7;
        return db.execute(`SELECT * FROM products WHERE category_id = ? ORDER BY created_at DESC LIMIT ${intLimit}`, [categoryId]);
    }

    // Lấy tất cả hình ảnh của một sản phẩm
    static fetchImages(productId) {
        return db.execute('SELECT * FROM product_images WHERE product_id = ? ORDER BY id ASC', [productId]);
    }

    // Lấy các sản phẩm liên quan
    static fetchRelated(categoryId, currentProductId) {
        return db.execute(
            'SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT 4',
            [categoryId, currentProductId]
        );
    }
    
    // Tìm kiếm sản phẩm
    static search(query, limit, offset) {
        const searchTerm = `%${query}%`;
        const intLimit = parseInt(limit, 10) || 12;
        const intOffset = parseInt(offset, 10) || 0;
        const sql = `SELECT * FROM products WHERE name LIKE ? ORDER BY created_at DESC LIMIT ${intOffset}, ${intLimit}`;
        return db.execute(sql, [searchTerm]);
    }
    
    // Đếm kết quả tìm kiếm
    static countSearch(query) {
        const searchTerm = `%${query}%`;
        const sql = 'SELECT COUNT(id) as total FROM products WHERE name LIKE ?';
        return db.execute(sql, [searchTerm]);
    }
    
    // Lọc sản phẩm trong trang danh mục
    static filterByCategory({ categoryId, brandId, sort, limit, offset }) {
        let sql = 'SELECT * FROM products WHERE category_id = ?';
        const params = [categoryId];

        if (brandId) {
            sql += ' AND brand_id = ?';
            params.push(brandId);
        }

        switch(sort) {
            case 'price_asc':
                sql += ' ORDER BY price ASC';
                break;
            case 'price_desc':
                sql += ' ORDER BY price DESC';
                break;
            case 'view_desc': // Bổ sung sắp xếp theo lượt xem
                sql += ' ORDER BY view_count DESC, created_at DESC';
                break;
            default: // Mặc định là 'default' (mới nhất)
                sql += ' ORDER BY created_at DESC';
                break;
        }
        
        const intLimit = parseInt(limit, 10) || 8;
        const intOffset = parseInt(offset, 10) || 0;
        sql += ` LIMIT ${intOffset}, ${intLimit}`;
        
        return db.execute(sql, params);
    }
    
    // Đếm sản phẩm trong trang danh mục
   static filterByCategory({ categoryId, brandId, sort, limit, offset }) {
    let sql = 'SELECT * FROM products WHERE category_id = ?';
    const params = [categoryId];

    // Thêm điều kiện lọc theo thương hiệu nếu có
    if (brandId) {
        sql += ' AND brand_id = ?';
        params.push(brandId);
    }

    // Thêm điều kiện sắp xếp
    switch(sort) {
        case 'price_asc':
            sql += ' ORDER BY price ASC';
            break;
        case 'price_desc':
            sql += ' ORDER BY price DESC';
            break;
        default: // Mặc định là 'default' (mới nhất)
            sql += ' ORDER BY created_at DESC';
            break;
    }
    
    // Thêm phân trang
    const intLimit = parseInt(limit, 10) || 8;
    const intOffset = parseInt(offset, 10) || 0;
    sql += ` LIMIT ${intOffset}, ${intLimit}`;
    
    return db.execute(sql, params);
}
static countFilterByCategory({ categoryId, brandId }) {
    let sql = 'SELECT COUNT(id) as total FROM products WHERE category_id = ?';
    const params = [categoryId];

    if (brandId) {
        sql += ' AND brand_id = ?';
        params.push(brandId);
    }
    
    return db.execute(sql, params);
}
};
