$(document).ready(() => {
  wait.on('[data-locale]', (localelizedElement) => {
    $(localelizedElement).text(locale($(localelizedElement).data('locale')));
  });
});

$(document).on('click', '[data-save-phrases]', function () {
  const phrases = [];
  $('[data-phrase-key]').each(function () {
    if ($(this).val() !== $(this).data('phrase-value')) {
      phrases.push({
        key: $(this).data('phrase-key'),
        value: $(this).val(),
        language: $(this).data('phrase-language')
      });
    }
  });

  $.post($(this).data('save-phrases-url'), {phrases: phrases})
    .done(() => {
      notify.success('Saved successfully!');
      nav.reload();
    })
    .fail(() => notify.danger('Somethign went wrong!'));

  return false;
});