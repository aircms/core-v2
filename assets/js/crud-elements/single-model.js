$(document).ready(() => {

  $(document).on('dblclick', '[data-admin-form-single-model-item-id]', (e) => {
    const model = $(e.currentTarget)
      .closest('[data-admin-form-single-model-name]')
      .data('admin-form-single-model-name');

    const id = $(e.currentTarget).data('admin-form-single-model-item-id');

    modal.record(model, id);

    return false;
  });

  $(document).on('click', '[data-admin-form-single-model-item-delete]', (e) => {
    const container = $(e.currentTarget).closest('[data-admin-form-single-model-list-container]');
    modal.question(locale('Are you sure want to remove?')).then(() => {
      container.html('');
    });
  });

  $(document).on('click', '[data-admin-form-single-model-add]', function () {
    const container = $(this).closest('[data-admin-form-single-model]');
    const modelName = container.data('admin-form-single-model-name');
    const language = getLanguage();
    const filter = language ? {filter: {language}} : {};
    const elementName = container.data('admin-form-single-model');
    const containerTemplate = $(`[data-admin-form-single-model-template="${elementName}"]`).html();
    const valueContainer = container.find('[data-admin-form-single-model-list-container]');

    modal.model(modelName, (row) => {
      let modelHtml = containerTemplate;
      Object.keys(row).forEach((key) => {
        if (key === 'image') {
          modelHtml = modelHtml.replaceAll('{{image}}', row.image && row.image.src ? row.image.src : '');
        } else {
          modelHtml = modelHtml.replaceAll('{{' + key + '}}', row[key]);
        }
      });

      valueContainer.html(modelHtml);

      notify.success('Added ' + row.title);
      modal.hide();

    }, filter);
  });

  // $(document).on('click', '[data-admin-form-single-model-add]', (e) => {
  //   const container = $(e.currentTarget).closest('[data-admin-form-single-model]');
  //   const modelList = container.find('[data-admin-form-single-model-list]');
  //   const modelName = container.data('admin-form-multiple-model-name');
  //   const elementName = container.data('admin-form-multiple-model');
  //   const containerTemplate = $(`[data-admin-form-single-model-template="${elementName}"]`).html();
  //   const language = getLanguage();
  //   const filter = language ? {filter: {language}} : {};
  //
  //   modal.model(modelName, (row) => {
  //     let modelHtml = containerTemplate;
  //     Object.keys(row).forEach((key) => {
  //       if (key === 'image') {
  //         modelHtml = modelHtml.replaceAll('{{image}}', row.image && row.image.src ? row.image.src : '');
  //       } else {
  //         modelHtml = modelHtml.replaceAll('{{' + key + '}}', row[key]);
  //       }
  //     });
  //
  //     modelList.append(modelHtml);
  //
  //     const item = modelList.find(`[data-admin-form-single-model-item-id="${row.id}"]`);
  //
  //     if (item.find('[data-admin-form-single-model-item-activity]').length) {
  //       if (row.enabled) {
  //         item.find(`[data-admin-form-single-model-item-activity="enabled"]`).removeClass('d-none');
  //       } else {
  //         item.find(`[data-admin-form-single-model-item-activity="disabled"]`).removeClass('d-none');
  //       }
  //     }
  //     updateValue(elementName);
  //     notify.success('Added ' + row.title);
  //     modal.hide();
  //   }, filter);
  // });
});