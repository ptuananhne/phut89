// ----- FILE: models/product.js -----

const mongoose = require('mongoose');

// Schema là một đối tượng định nghĩa cấu trúc của một document trong MongoDB.
const Schema = mongoose.Schema;

// Tạo một schema mới cho sản phẩm
const productSchema = new Schema({
    // Định nghĩa các trường (fields) và kiểu dữ liệu của chúng
    title: {
        type: String,
        required: true // Bắt buộc phải có
    },
    price: {
        type: Number,
        required: true
    },
    description: {
        type: String,
        required: true
    },
    imageUrl: {
        type: String,
        required: true
    }
    // Chúng ta có thể thêm các trường khác sau này, ví dụ:
    // category: {
    //     type: String,
    //     required: true
    // }
});

// Xuất ra một Model từ Schema đã tạo.
// Mongoose sẽ tự động tạo một collection trong MongoDB có tên là 'products' (số nhiều của 'Product')
module.exports = mongoose.model('Product', productSchema);
