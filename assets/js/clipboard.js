const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text).catch(() => {
    modal.message(locale('Oops... Looks like your browser does not support clipboard operations.'), {style: 'danger'});
  });
};