const deepSeek = new class {
  run(name, value, preData) {
    return new Promise((resolve) => {

      loader.show();
      $.post('/' + window.deepSeek + "/prompt", {name, value, preData, language: getLanguage()}, (html) => {
        loader.hide();
        side.open("Deep seek", html, () => {
          const deepSeekForm = $('[data-deep-seek-form]');

          deepSeekForm.find('[data-deep-seek-ask]').click(() => {
            loader.show();
            $.post('/' + window.deepSeek + "/ask", deepSeekForm.serialize())
              .done((content) => {
                if (!content?.answer?.length) {
                  notify.show("DeepSeek confused about the answer, please try again.", {style: this.styles.danger});
                  return;
                }
                deepSeekForm.find('[name="value"]').val(content.answer);
              })
              .fail((err) => notify.danger("Something went wrong with DeepSeek"))
              .always(() => loader.hide())
          });

          deepSeekForm.find('[data-deep-seek-apply]').click(() => {
            resolve(deepSeekForm.find('[name="value"]').val());
            side.hide();
          });
        });
      });
    });
  }
};

wait.on('[data-accessory-help-deep-seek]', (el) => $(el).click(() => {
  const accessoryItem = $(el).data('accessory-help-deep-seek');
  const input = getAccessoryInput(accessoryItem);
  const currentValue = getAccessoryValue(accessoryItem);
  const currentName = input.attr('name');

  const form = $('[data-admin-from-manage]');

  if (form.length) {
    loader.show();
    $.post('/' + getController() + '/data', form.serialize())
      .done((recordData) => {
        deepSeek
          .run(currentName, currentValue, recordData)
          .then((value) => applyAccessoryValue(accessoryItem, value));
      })
      .fail(() => notify.danger('Unexpected error during getting model data'))
      .always(() => loader.hide());

  } else {
    deepSeek
      .run(currentName, currentValue)
      .then((value) => applyAccessoryValue(accessoryItem, value));
  }
}));

wait.on('[data-accessory-translate-deep-seek]', (el) => $(el).click(() => {
  const accessoryItem = $(el).data('accessory-translate-deep-seek');
  const phrase = getAccessoryValue(accessoryItem);

  loader.show();
  $.post('/' + window.deepSeek + '/phrase', {phrase, language: getLanguage()})
    .done((value) => {
      if (value.translation) {
        applyAccessoryValue(accessoryItem, value.translation);
        notify.success(locale('Translation replaced'));
        return;
      }
      notify.danger('Unexpected error during getting translation');
    })
    .fail(() => notify.danger('Unexpected error during getting translation'))
    .always(() => loader.hide());
}));