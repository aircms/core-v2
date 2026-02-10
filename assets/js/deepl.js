$(document).ready(() => {
  if (window.deepl.length) {

    const getPhrase = (phrase, language) => {
      return new Promise(resolve => {
        $.post('/' + window.deepl + "/phrase", {phrase: phrase, language: language || getLanguage()}, (res) => {
          if (!res.translation) {
            notify.danger('Deepl currenty is anavailable');
            return;
          }
          resolve(res.translation);
        });
      });
    };

    wait.on('[data-accessory-translate-deepl]', (el) => {
      $(el).click(() => {
        const phrase = getAccessoryValue($(el).data('accessory-deepl'));
        if (!phrase) {
          notify.danger("Phrase is empty");
          return;
        }
        loader.show();
        getPhrase(phrase)
          .then((value) => {
            applyAccessoryValue($(el).data('accessory-deepl'), value);
            notify.success('Phrase replaced');
          })
          .finally(() => loader.hide());
      });
    });
  }
});
