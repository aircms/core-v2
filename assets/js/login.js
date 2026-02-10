$(document).ready(() => {
  $('.fade').addClass('show');

  $('form').on('submit', function () {
    const returnUrl = $(this).data('return-url').length ? $(this).data('return-url') : '/';
    loader.show();
    $.post('/_auth', $(this).serialize())
      .done(() => window.location = returnUrl)
      .fail(() => {
        $('[data-error]').removeClass('d-none');
        loader.hide();
      });
    return false;
  });
});