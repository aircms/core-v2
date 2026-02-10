$(document).ready(() => {

  wait.on('[data-admin-form-multiple-key-value-sortable]', (sortable) => {
    if (!$(sortable).find('[data-admin-form-multiple-key-value-item]').length) {
      $(sortable).closest('[data-admin-form-multiple-key-value-sortable-container]').addClass('d-none');
    }

    new Sortable(sortable, {
      animation: 150,
      handle: '[data-admin-form-multiple-key-value-sort]'
    });
  });

  $(document).on('click', '[data-admin-form-multiple-key-value-remove]', (e) => {
    const container = $(e.currentTarget).closest('[data-admin-form-multiple-key-value-item]');
    const sortableContainer = $(e.currentTarget).closest('[data-admin-form-multiple-key-value-sortable-container]');

    modal.question(locale('Are you sure want to remove?')).then(() => {
      container.remove();
      if (!sortableContainer.find('[data-admin-form-multiple-key-value-item]').length) {
        sortableContainer.addClass('d-none');
      }
    });
  });

  $(document).on('click', '[data-admin-form-multiple-key-value-add]', (e) => {
    const container = $(e.currentTarget)
      .closest('[data-admin-form-multiple-key-value]')
      .find('[data-admin-form-multiple-key-value-sortable]');

    const name =$(e.currentTarget)
      .closest('[data-admin-form-multiple-key-value]')
      .data('admin-form-multiple-key-value');

    container.append(
      $(`[data-admin-form-multiple-key-value-template="${name}"]`)
        .html().replaceAll('{{id}}', Date.now() * Math.random()));

    container.closest('[data-admin-form-multiple-key-value-sortable-container]').removeClass('d-none');
  });
});