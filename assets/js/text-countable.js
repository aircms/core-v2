$(document).ready(() => {
  wait.on('[data-text-countable]', (el) => {
    const count = parseInt($(el).data('text-countable'));

    if (count) {
      const countable = (input) => {
        const max = parseInt($(input).data('text-countable'));
        const count = $(input).find('input, textarea').val().length;

        $(input).find('[data-text-countable="one"]').html(count.toString());
        $(input).find('[data-text-countable="two"]').html(max.toString());

        if (count > max) {
          $(input).find('.text-countable').addClass('overinputed');
        } else {
          $(input).find('.text-countable').removeClass('overinputed');
        }
      };

      $(el).addClass('position-relative');
      $(el).append('<div class="text-countable"><span data-text-countable="one"></span> / <span data-text-countable="two"></span></div>');
      setInterval(() => countable(el), 100);
    }
  });
});