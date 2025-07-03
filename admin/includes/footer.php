<?php /* === Nội dung cho admin/includes/footer.php === */ ?>
</div>
</main>
</div>
<!-- [FIXED] Sửa lại đường dẫn CDN cho các thư viện JS -->
<!-- Thêm jQuery (cần cho Select2 và các plugin khác) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Thêm JS cho Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Thêm SortableJS cho chức năng kéo-thả -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<!-- File JS chính của bạn (phải được tải sau cùng) -->
<script src="<?php echo BASE_URL; ?>/admin/assets/admin_main.js?v=<?php echo time(); ?>"></script>
</body>

</html>