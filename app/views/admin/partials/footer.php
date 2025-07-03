<?php
// FILE: /app/views/admin/partials/footer.php
// MÔ TẢ: Nạp các file Javascript cho trang Admin.

?>
<!-- Thêm jQuery (cần cho Select2 và các plugin khác) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Thêm JS cho Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Thêm SortableJS cho chức năng kéo-thả -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<!-- File JS chính của admin -->
<script src="<?php echo BASE_URL; ?>/admin-assets/js/admin_main.js?v=<?php echo filemtime(ROOT_PATH . '/public/admin-assets/js/admin_main.js'); ?>"></script>

