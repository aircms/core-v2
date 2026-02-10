$(document).ready(() => {
  $(document).on('click', '[data-admin-form-element-group-multiple-add-item]', (e) => {
    const id = Date.now() * Math.random();

    const name = $(e.currentTarget)
      .closest('[data-admin-form-element-group-multiple]')
      .data('admin-form-element-group-multiple');

    const template = $('[data-admin-form-element-group-multiple-template="' + name + '"]').html()
      .replaceAll('{{groupId}}', id);

    $(e.currentTarget).after(template);
  });

  $(document).on('click', '[data-admin-form-element-group-multiple-group-item-remove]', (e) => {
    const container = $(e.currentTarget).closest('[data-admin-form-element-group-multiple-group-item-container]');

    modal.question(locale('Remove block?')).then(() => {
      const next = container.next();
      container.remove();
      next.remove();
    });
  });
});