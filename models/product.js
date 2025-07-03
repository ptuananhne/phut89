// ----- FILE: models/product.js -----

const db = require('../util/database');

module.exports = class Product {
    static fetchAll(limit, offset) {
        const intLimit = parseInt(limit, 10) || 12;
        const intOffset = parseInt(offset, 10) || 0;
        return db.execute(`SELECT * FROM products ORDER BY created_at DESC LIMIT ${intOffset}, ${intLimit}`);
    }

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
    
    static fetchFeatured(limit) {
        const intLimit = parseInt(limit, 10) || 12;
        return db.execute(`SELECT * FROM products ORDER BY created_at DESC LIMIT ${intLimit}`);
    }

    static fetchByCategoryId(categoryId, limit) {
        const intLimit = parseInt(limit, 10) || 7;
        return db.execute('SELECT * FROM products WHERE category_id = ? ORDER BY created_at DESC LIMIT ?', [categoryId, intLimit]);
    }

    static fetchImages(productId) {
        // Giả sử bạn có bảng product_images
        return db.execute('SELECT * FROM product_images WHERE product_id = ? ORDER BY id ASC', [productId]);
    }

    static fetchRelated(categoryId, currentProductId) {
        return db.execute(
            'SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT 4',
            [categoryId, currentProductId]
        );
    }
    
    static search(query, limit, offset) {
        const searchTerm = `%${query}%`;
        const intLimit = parseInt(limit, 10) || 12;
        const intOffset = parseInt(offset, 10) || 0;
        const sql = `SELECT * FROM products WHERE name LIKE ? ORDER BY created_at DESC LIMIT ${intOffset}, ${intLimit}`;
        return db.execute(sql, [searchTerm]);
    }
    
    static countSearch(query) {
        const searchTerm = `%${query}%`;
        const sql = 'SELECT COUNT(id) as total FROM products WHERE name LIKE ?';
        return db.execute(sql, [searchTerm]);
    }
    
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
            default:
                sql += ' ORDER BY created_at DESC';
        }
        
        const intLimit = parseInt(limit, 10) || 12;
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