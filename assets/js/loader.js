const loader = new class {
  selector = '[data-loader]';
  timeoutHandler = 0;
  template = `
<div data-loader class="loader position-absolute top-0 end-0 bottom-0 start-0 d-flex align-items-center justify-content-center bg-blur-5 transition-3 fade z-i-2002">
    <div class="spinner-border text-primary" role="status"></div>
</div>
`;

  show() {
    if ($(this.selector).is(':visible')) {
      return;
    }
    if (!$(this.selector).length) {
      $('body').append(this.template);
    }
    this.timeoutHandler = setTimeout(() => $(this.selector).addClass('show'), 150);
  };

  hide() {
    const loader = $(this.selector);
    clearTimeout(this.timeoutHandler);
    loader.removeClass('show');
    setTimeout(() => loader.remove(), 500);
  }
}

$(document).ready(() => {
  wait.on('[data-storage-frame]', (storageFrame) => {
    $(storageFrame).on('load', (e) => {
      $(e.currentTarget).addClass('w-100').addClass('h-100');
      loader.hide();
    });
  });
});