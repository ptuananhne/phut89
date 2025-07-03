// ----- FILE: models/category.js -----

const db = require('../util/database');

module.exports = class Category {
    static fetchAll() {
        return db.execute('SELECT * FROM categories ORDER BY name ASC');
    }

    static findBySlug(slug) {
        return db.execute('SELECT * FROM categories WHERE slug = ?', [slug]);
    }
    
    // Lấy các thương hiệu thuộc về một danh mục
    static fetchBrandsForCategory(categoryId) {
        const sql = `
            SELECT DISTINCT b.*
            FROM brands b
            INNER JOIN category_brand cb ON b.id = cb.brand_id
            WHERE cb.category_id = ?
            ORDER BY b.name ASC
        `;
        return db.execute(sql, [categoryId]);
    }
};