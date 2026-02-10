$(document).ready(() => {
  Embed.ready(() => {
    $(document).on('click', '[data-admin-form-rich-content-toolbar-add]', (e) => {
      const elementName = $(e.currentTarget).closest('[data-admin-form-rich-content]').data('admin-form-rich-content');
      const generatedName = Date.now() * Math.random();
      const toolbar = $('[data-admin-form-rich-content-template="toolbar"]').html();
      const type = $(e.currentTarget).data('admin-form-rich-content-toolbar-add');

      const element = $(`[data-admin-form-rich-content-template-name="${elementName}"][data-admin-form-rich-content-template="${type}"]`)
        .html().replaceAll('{{name}}', generatedName);

      const container = $('[data-admin-form-rich-content-template="container"]').html()
        .replaceAll('{{element}}', element)
        .replaceAll('{{toolbar}}', toolbar);

      $(e.currentTarget).closest('[data-admin-form-rich-content-element-container-replacement]').replaceWith(container);

      $('body > .tooltip').remove();
    });

    $(document).on('click', '[data-admin-form-rich-content-element-container-remove]', (e) => {
      const container = $(e.currentTarget).closest('[data-admin-form-rich-content-element-container]');
      const toolbar = $(e.currentTarget).closest('[data-admin-form-rich-content-element-container]').next();

      modal.question(locale('Are sure want to remove?')).then(() => {
        container.remove();
        toolbar.remove();
      });
    });
  });
});