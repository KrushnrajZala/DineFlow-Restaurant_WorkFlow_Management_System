function toggleForm(id) {
  var el = document.getElementById(id);
  if (!el) return;
  var isOpen = el.style.display === 'block' || el.classList.contains('open');
  document.querySelectorAll('.add-item-form, .pay-form').forEach(function (f) {
    f.style.display = 'none';
    f.classList.remove('open');
  });
  if (!isOpen) {
    el.style.display = 'block';
    el.classList.add('open');
  }
}

function toggleChatBox(id) {
  var el = document.getElementById(id);
  if (!el) return;
  el.classList.toggle('chat-open');
}

function toggleMobileNav() {
  var nav = document.querySelector('.navbar');
  if (nav) nav.classList.toggle('active');
}

function toggleDrop(id) {
  var el = document.getElementById(id);
  if (!el) return;
  var isOpen = el.classList.contains('open');
  document.querySelectorAll('.nav-drop').forEach(function (d) { d.classList.remove('open'); });
  if (!isOpen) el.classList.add('open');
}

document.addEventListener('click', function (e) {
  if (!e.target.closest('.nav-drop')) {
    document.querySelectorAll('.nav-drop').forEach(function (d) { d.classList.remove('open'); });
  }
});

function filterMenu(cat) {
  document.querySelectorAll('.filter-chip').forEach(function (b) {
    b.classList.toggle('active', b.getAttribute('data-filter') === cat);
  });
  document.querySelectorAll('[data-category]').forEach(function (c) {
    c.style.display = (cat === 'all' || c.getAttribute('data-category') === cat) ? '' : 'none';
  });
}

function searchMenu(q) {
  q = (q || '').toLowerCase().trim();
  document.querySelectorAll('[data-category]').forEach(function (c) {
    var name = (c.getAttribute('data-name') || c.textContent || '').toLowerCase();
    c.style.display = !q || name.indexOf(q) !== -1 ? '' : 'none';
  });
}

function filterSelectOptions(inputId, selectId) {
  var q = (document.getElementById(inputId).value || '').toLowerCase();
  var sel = document.getElementById(selectId);
  if (!sel) return;
  Array.prototype.forEach.call(sel.options, function (opt) {
    if (!opt.value) return;
    opt.hidden = q && opt.text.toLowerCase().indexOf(q) === -1;
  });
}

function dfSwalConfirm(msg) {
  if (typeof Swal === 'undefined') {
    return Promise.resolve({ isConfirmed: window.confirm(msg) });
  }
  return Swal.fire({
    title: 'Please confirm',
    text: msg || 'Are you sure?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No',
    buttonsStyling: false,
    customClass: {
      popup: 'dineflow-swal',
      confirmButton: 'dineflow-swal-confirm',
      cancelButton: 'dineflow-swal-cancel'
    }
  });
}

// Wire all forms with class swal-confirm
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form.swal-confirm').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var msg = form.getAttribute('data-msg') || 'Are you sure?';
      var f = form;
      dfSwalConfirm(msg).then(function (r) {
        if (r.isConfirmed) {
          // avoid re-triggering
          f.classList.remove('swal-confirm');
          HTMLFormElement.prototype.submit.call(f);
        }
      });
    });
  });

  // Convert leftover onsubmit="return confirm(...)" if any slipped through
  document.querySelectorAll('form[onsubmit*="confirm"]').forEach(function (form) {
    var on = form.getAttribute('onsubmit') || '';
    var m = on.match(/confirm\(['"]([^'"]*)['"]\)/);
    form.removeAttribute('onsubmit');
    form.classList.add('swal-confirm');
    if (m) form.setAttribute('data-msg', m[1]);
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var msg = form.getAttribute('data-msg') || 'Are you sure?';
      var f = form;
      dfSwalConfirm(msg).then(function (r) {
        if (r.isConfirmed) {
          f.classList.remove('swal-confirm');
          HTMLFormElement.prototype.submit.call(f);
        }
      });
    });
  });
});

function toggleOrderDetails(id, bar) {
  var el = document.getElementById(id);
  if (!el) return;
  el.classList.toggle('open');
  if (bar) bar.classList.toggle('open');
}
