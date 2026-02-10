$(document).ready(() => {
  if (window.googleTranslate.length) {

    const getPhrase = (phrase, language) => {
      return new Promise((resolve, reject) => {
        $.post('/' + window.googleTranslate + "/phrase", {
          phrase: phrase,
          language: language || getLanguage()
        }, (res) => {
          if (!res.translation) {
            notify.danger('Google Translate currenty is anavailable');
            reject();
            return;
          }
          resolve(res.translation);
        });
      });
    };

    wait.on('[data-accessory-google-translate]', (el) => {
      $(el).click(() => {
        const phrase = getAccessoryValue($(el).data('accessory-google-translate'));
        const input = getAccessoryInput($(el).data('accessory-google-translate'));
        let language = getLanguage();

        if (input.data('phrase-language')) {
          language = input.data('phrase-language');
        }

        if (!phrase) {
          notify.danger("Phrase is empty");
          return;
        }
        loader.show();
        getPhrase(phrase, language)
          .then((value) => {
            applyAccessoryValue($(el).data('accessory-google-translate'), value);
            notify.success('Phrase replaced');
          })
          .finally(() => loader.hide());
      });
    });
  }
});
