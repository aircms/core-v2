const getLanguage = () => {
  let language = null;
  if ($('[data-admin-manage-switch-language]').length) {
    language = $('[data-admin-manage-switch-language]').data('admin-manage-switch-language');
  }
  if (!language && $('[name="language"]').length) {
    language = $('[name="language"]').val();
  }
  if (!language && $('[name="filter[language]"]').length) {
    language = $('[name="filter[language]"]').val();
  }
  return language && language.length ? language : null;
};

$(document).ready(() => {

  const updateValue = (name) => {
    const ids = [];
    const element = $(`[data-admin-form-multiple-model="${name}"]`);

    element.find('[data-admin-form-multiple-model-item-id]')
      .each((i, e) => ids.push($(e).data('admin-form-multiple-model-item-id')));

    const listContainer = element.find('[data-admin-form-multiple-model-list-container]');

    if (ids.length) {
      listContainer.removeClass('d-none');
    } else {
      listContainer.addClass('d-none');
    }

    element.find(`[name="${name}"]`).val(JSON.stringify(ids));
  };

  $(document).on('dblclick', '[data-admin-form-multiple-model-item-id]', (e) => {
    const model = $(e.currentTarget)
      .closest('[data-admin-form-multiple-model-name]')
      .data('admin-form-multiple-model-name');

    const id = $(e.currentTarget).data('admin-form-multiple-model-item-id');

    modal.record(model, id);

    return false;
  });

  $(document).on('click', '[data-admin-form-multiple-model-item-delete]', (e) => {
    const container = $(e.currentTarget)
      .closest('[data-admin-form-multiple-model-item-id]');

    const name = $(e.currentTarget)
      .closest('[data-admin-form-multiple-model]')
      .data('admin-form-multiple-model');

    modal.question(locale('Are you sure want to remove?')).then(() => {
      container.remove();
      updateValue(name);
    });
  });

  $(document).on('click', '[data-admin-form-multiple-model-add]', (e) => {
    const container = $(e.currentTarget).closest('[data-admin-form-multiple-model]');
    const modelList = container.find('[data-admin-form-multiple-model-list]');
    const modelName = container.data('admin-form-multiple-model-name');
    const elementName = container.data('admin-form-multiple-model');
    const containerTemplate = $(`[data-admin-form-multiple-model-template="${elementName}"]`).html();
    const language = getLanguage();
    const filter = language ? {filter: {language}} : {};

    modal.model(modelName, (row) => {
      let modelHtml = containerTemplate;
      Object.keys(row).forEach((key) => {
        if (key === 'image') {
          modelHtml = modelHtml.replaceAll('{{image}}', row.image && row.image.src ? row.image.src : '');
        } else {
          modelHtml = modelHtml.replaceAll('{{' + key + '}}', row[key]);
        }
      });

      modelList.append(modelHtml);

      const item = modelList.find(`[data-admin-form-multiple-model-item-id="${row.id}"]`);

      if (item.find('[data-admin-form-multiple-model-item-activity]').length) {
        if (row.enabled) {
          item.find(`[data-admin-form-multiple-model-item-activity="enabled"]`).removeClass('d-none');
        } else {
          item.find(`[data-admin-form-multiple-model-item-activity="disabled"]`).removeClass('d-none');
        }
      }
      updateValue(elementName);
      notify.success('Added ' + row.title);
    }, filter);
  });

  wait.on('[data-admin-form-multiple-model-list]', (selectList) => {
    const name = $(selectList)
      .closest('[data-admin-form-multiple-model]')
      .data('admin-form-multiple-model');

    updateValue(name);

    new Sortable(selectList, {
      animation: 150,
      onUpdate: () => {
        const name = $(selectList)
          .closest('[data-admin-form-multiple-model]')
          .data('admin-form-multiple-model');

        updateValue(name);
      }
    });
  });

});