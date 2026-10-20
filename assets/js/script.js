// CFMTS front-end helpers (no framework, plain JS)

document.addEventListener('DOMContentLoaded', function () {
  var overlay = document.getElementById('confirmOverlay');
  var confirmLink = document.getElementById('confirmDeleteLink');
  var confirmLabel = document.getElementById('confirmDeleteLabel');

  document.querySelectorAll('[data-delete-url]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var url = btn.getAttribute('data-delete-url');
      var label = btn.getAttribute('data-delete-label') || 'this request';
      if (confirmLink) confirmLink.setAttribute('href', url);
      if (confirmLabel) confirmLabel.textContent = label;
      if (overlay) overlay.classList.add('active');
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (overlay) overlay.classList.remove('active');
    });
  });

  if (overlay) {
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) overlay.classList.remove('active');
    });
  }

  document.querySelectorAll('.alert[data-autohide]').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity 0.4s ease';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 400);
    }, 3500);
  });

  var buildingSelect = document.getElementById('building_id');
  var roomSelect = document.getElementById('room_id');
  if (buildingSelect && roomSelect) {
    buildingSelect.addEventListener('change', function () {
      var buildingId = buildingSelect.value;
      Array.from(roomSelect.options).forEach(function (opt) {
        if (!opt.value) { opt.hidden = false; return; }
        opt.hidden = buildingId && opt.getAttribute('data-building') !== buildingId;
      });
      var current = roomSelect.selectedOptions[0];
      if (current && current.hidden) {
        roomSelect.value = '';
      }
    });
  }
});
