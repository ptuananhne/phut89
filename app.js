// ----- FILE: app.js -----

require('dotenv').config();
const express = require('express');
const path = require('path');

const shopRoutes = require('./routes/shop');
// const adminRoutes = require('./routes/admin');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware để truyền dữ liệu chung cho tất cả các view
// Ví dụ: danh sách danh mục cho sidebar
const Category = require('./models/category');
app.use(async (req, res, next) => {
    try {
        const [categories] = await Category.fetchAll();
        res.locals.allCategories = categories;
        res.locals.path = req.path; // Giúp xác định trang active
        next();
    } catch (err) {
        next(err); // Chuyển lỗi cho middleware xử lý lỗi của Express
    }
});


app.set('view engine', 'ejs');
app.set('views', 'views');

app.use(express.static(path.join(__dirname, 'public')));
app.use(express.urlencoded({ extended: true }));

// Sử dụng các routes
app.use(shopRoutes);
// app.use('/admin', adminRoutes);

// Middleware xử lý lỗi 404
app.use((req, res, next) => {
    res.status(404).render('404', { 
        pageTitle: 'Trang không tồn tại',
        allCategories: res.locals.allCategories || []
    });
});


app.listen(PORT, () => {
    console.log(`Server đang chạy tại http://localhost:${PORT}`);
});
