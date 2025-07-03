// ----- FILE: app.js -----

require('dotenv').config();
const express = require('express');
const path = require('path');

const shopRoutes = require('./routes/shop');
const Category = require('./models/category');

const app = express();
const PORT = process.env.PORT || 3000;

app.set('view engine', 'ejs');
app.set('views', 'views');

app.use(express.static(path.join(__dirname, 'public')));
app.use(express.urlencoded({ extended: true }));

// Middleware để truyền dữ liệu chung cho tất cả các view
app.use(async (req, res, next) => {
    try {
        const [categories] = await Category.fetchAll();
        res.locals.allCategories = categories;
        res.locals.path = req.path;
        next();
    } catch (err) {
        next(err);
    }
});

app.use(shopRoutes);

// Middleware xử lý lỗi 404
app.use((req, res, next) => {
    res.status(404).render('404', { 
        pageTitle: 'Trang không tồn tại',
        path: '/404'
    });
});

app.listen(PORT, () => {
    console.log(`Server đang chạy tại http://localhost:${PORT}`);
});