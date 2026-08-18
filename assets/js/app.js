document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.getElementById('navToggle');
  var sidebar = document.getElementById('sidebar');
  if (toggle && sidebar) {
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });
  }

  // Confirm before deleting a record
  document.querySelectorAll('form.confirm-delete').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!confirm('Delete this record? This cannot be undone.')) {
        e.preventDefault();
      }
    });
  });
});
