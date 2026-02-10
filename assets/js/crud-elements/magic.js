$(document).on('click', '[data-admin-from-manage-magic]', function () {

  const applyData = (data) => {
    const form = $('[data-admin-from-manage]');
    Object.keys(data).forEach((key) => {
      if (key === 'meta') {
        Object.keys(data[key]).forEach((metaKey) => {
          form.find('[name="meta[' + metaKey + ']"]').val(data[key][metaKey]);
        });

      } else if (key === 'content') {
        form.find('[name="content"]').parent().find('[data-admin-form-tiny-preview]').html(data[key]);

      } else if (key === 'richContent') {
        data[key].forEach(({value}) => {
          $('[data-admin-form-rich-content-container="richContent"] [data-admin-form-rich-content-element-container-replacement]:last-of-type [data-admin-form-rich-content-toolbar-add="html"]').click();
          const lastContainer = $('[data-admin-form-rich-content-container="richContent"] [data-admin-form-rich-content-element-container]').last();
          lastContainer.find('textarea').val(value);
          lastContainer.find('[data-admin-form-tiny-preview]').html(value);
        });

      } else {
        const input = form.find('[name="' + key + '"]');
        if (input.length) {
          input.val(data[key]);
        }
      }
    });

    form.find('input, textarea').each(function () {
      $(this).focus();
      setTimeout(() => $(this).blur(), 10);
    });
  };

  const storageUrl = $(this).closest('[data-admin-from-manage-magic-container]').data('admin-form-storage-add-url');
  const storageKey = $(this).closest('[data-admin-from-manage-magic-container]').data('admin-form-storage-add-key');
  const ai = $(this).data('admin-from-manage-magic');

  if (ai === 'openAi') {
    modal.file(storageUrl, storageKey, false, null, (file) => {
      loader.show();
      $.post('/' + getController() + '/openAi', {file})
        .fail((err) => notify.danger(err))
        .always(() => loader.hide())
        .done((data) => {
          modal.hide();
          applyData(data);
        });
    });

  } else if (ai === 'deepSeek') {
    modal.promptBig("DeepSeek", "Prompt").then((prompt) => {
      loader.show();
      $.post('/' + getController() + '/deepSeek', {prompt})
        .fail((err) => notify.danger(err))
        .always(() => loader.hide())
        .done((data) => applyData(data));
    });
  }
});