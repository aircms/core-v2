$(document).ready(() => {

  wait.on('[data-admin-form-multiple-text-sortable]', (sortable) => {
    if (!$(sortable).find('[data-admin-form-multiple-text-item]').length) {
      $(sortable).closest('[data-admin-form-multiple-text-sortable-container]').addClass('d-none');
    }

    new Sortable(sortable, {
      animation: 150,
      handle: '[data-admin-form-multiple-text-sort]'
    });
  });

  $(document).on('click', '[data-admin-form-multiple-text-add]', (e) => {
    const container = $(e.currentTarget)
      .closest('[data-admin-form-multiple-text]')
      .find('[data-admin-form-multiple-text-sortable]');

    const name = $(e.currentTarget)
      .closest('[data-admin-form-multiple-text]')
      .data('admin-form-multiple-text');

    const sortableContainer = $(e.currentTarget)
      .closest('[data-admin-form-multiple-text]')
      .find('[data-admin-form-multiple-text-sortable-container]');

    sortableContainer.removeClass('d-none');
    container.append($(`[data-admin-form-multiple-text-template="${name}"]`).html());
  });

  $(document).on('click', '[data-admin-form-multiple-text-remove]', (e) => {
    const item = $(e.currentTarget)
      .closest('[data-admin-form-multiple-text-item]');

    const sortableContainer = $(e.currentTarget)
      .closest('[data-admin-form-multiple-text-sortable-container]');

    modal.question(locale('Are you sure want to remove?')).then(() => {
      item.remove();

      if (!sortableContainer.find('[data-admin-form-multiple-text-item]').length) {
        sortableContainer.addClass('d-none');
      }
    });
  });
});