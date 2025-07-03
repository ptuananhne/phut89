// ----- FILE: routes/shop.js -----

const express = require('express');
const shopController = require('../controllers/shopController');
const router = express.Router();

router.get('/', shopController.getIndex);
router.get('/san-pham/:slug', shopController.getProduct);
router.get('/danh-muc/:slug', shopController.getCategory);
router.get('/timkiem', shopController.getSearch);

module.exports = router;