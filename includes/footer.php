  </div>
  <div class="footer-note">CFMTS &middot; Campus Facility Maintenance Tracking System &middot; Built with PHP, PDO &amp; OOP</div>
</div>

<!-- Shared delete-confirm modal -->
<div class="modal-overlay" id="confirmOverlay">
  <div class="modal-box">
    <h3><i class="fa-solid fa-triangle-exclamation" style="color:#c0392b;"></i> Delete Request</h3>
    <p>Are you sure you want to delete <strong id="confirmDeleteLabel">this request</strong>? This action cannot be undone.</p>
    <div class="modal-actions">
      <button type="button" class="btn btn-outline btn-sm" data-modal-close>Cancel</button>
      <a href="#" id="confirmDeleteLink" class="btn btn-danger btn-sm">
        <i class="fa-solid fa-trash"></i> Yes, Delete
      </a>
    </div>
  </div>
</div>

<script src="<?= APP_BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>
