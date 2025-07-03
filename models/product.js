// ----- FILE: models/product.js -----

const db = require('../util/database');

module.exports = class Product {
    // Lấy tất cả sản phẩm (hỗ trợ phân trang)
    static fetchAll(limit, offset) {
        return db.execute('SELECT * FROM products ORDER BY created_at DESC LIMIT ? OFFSET ?', [limit, offset]);
    }

    // Đếm tổng số sản phẩm
    static countAll() {
        return db.execute('SELECT COUNT(id) as total FROM products');
    }
    
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

    static fetchImages(productId) {
        return db.execute('SELECT * FROM product_images WHERE product_id = ? ORDER BY is_default DESC, id ASC', [productId]);
    }

    static fetchAttributes(productId) {
        const sql = `
            SELECT a.name as attr_name, pa.value as attr_value
            FROM product_attributes pa
            JOIN attributes a ON pa.attribute_id = a.id
            WHERE pa.product_id = ?
        `;
        return db.execute(sql, [productId]);
    }

    // Lấy sản phẩm liên quan
    static fetchRelated(categoryId, currentProductId) {
        return db.execute(
            'SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT 4',
            [categoryId, currentProductId]
        );
    }
    
    // Tìm kiếm sản phẩm
    static search(query, limit, offset) {
        const searchTerm = `%${query}%`;
        const sql = 'SELECT * FROM products WHERE name LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?';
        return db.execute(sql, [searchTerm, limit, offset]);
    }
    
    static countSearch(query) {
        const searchTerm = `%${query}%`;
        const sql = 'SELECT COUNT(id) as total FROM products WHERE name LIKE ?';
        return db.execute(sql, [searchTerm]);
    }

    // Lọc sản phẩm trong một danh mục
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
            default: // Mới nhất
                sql += ' ORDER BY created_at DESC';
        }
        
        sql += ' LIMIT ? OFFSET ?';
        params.push(limit, offset);

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

