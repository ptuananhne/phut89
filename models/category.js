const db = require('../util/database');

module.exports = class Category {
    static fetchAll() {
        return db.execute('SELECT * FROM categories ORDER BY name ASC');
    }

    static findBySlug(slug) {
        return db.execute('SELECT * FROM categories WHERE slug = ?', [slug]);
    }
    
    // --- BỔ SUNG ---
    // Lấy các thương hiệu thuộc về một danh mục
    static fetchBrandsForCategory(categoryId) {
        const sql = `
            SELECT DISTINCT b.*
            FROM brands b
            INNER JOIN products p ON b.id = p.brand_id
            WHERE p.category_id = ?
            ORDER BY b.name ASC
        `;
        return db.execute(sql, [categoryId]);
    }
};
