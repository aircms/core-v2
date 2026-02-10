$(document).ready(() => {

  wait.on('[data-admin-form-positioning-sortable]', (sortable) => {
    new Sortable(sortable, {
      animation: 150,
      onUpdate: () => {
        $(sortable).find('[data-admin-position-form-counter]').each((i, e) => $(e).html(i + 1));
      }
    });
  });

  $(document).on('click', '[data-admin-from-positioning-reset]', () => {
    const form = $('[data-admin-position-form]');
    nav.nav(form.data('admin-position-form'));
  });

  $(document).on('click', '[data-admin-from-positioning-save]', () => {
    const form = $('[data-admin-position-form]');
    if (form.length) {
      form.submit();
    }
  });

  $(document).on('keydown', (e) => {
    const saveButton = $('[data-admin-from-positioning-save]');
    if (saveButton.length && e.key === 's' && e.ctrlKey) {
      saveButton.click();
      e.preventDefault();
    }
  });

  $(document).on('submit', '[data-admin-position-form]', (e) => {
    const url = $(e.currentTarget).data('admin-position-form');
    const data = $(e.currentTarget).serialize();

    $.post(url, data, (url) => {
      notify.success(locale('Record position saved'));
      nav.nav(url);
    });

    return false;
  });
})