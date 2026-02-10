const notify = new class {
  selector = '[data-admin-notify]';

  template = `
<div data-admin-notify class="position-absolute top-0 start-50 translate-middle-x z-i-2002 shadow-5-strong mt-3 p-3 fade d-flex align-items-start alert alert-{{style}}">
  <h6 class="m-0 p-0 small">{{content}}</h6>
  <div class="ms-3 d-none" data-admin-notify-button>
    <a class="btn btn-sm btn-{{style}}" data-mdb-ripple-init>Okay</a>
  </div>
</div>`;

  styles = {
    primary: 'primary',
    secondary: 'secondary',
    success: 'success',
    danger: 'danger',
    warning: 'warning',
    info: 'info',
    light: 'light',
    dark: 'dark',
  };

  defaultOptions = {
    style: this.styles.light,
    dismissible: true,
    dismissDelay: 5000,
    button: false
  };

  success(title, cb) {
    this.show(title, {style: this.styles.success}, cb);
  }

  danger(title, cb) {
    this.show(title, {style: this.styles.danger}, cb);
  }

  show(message, options = {}, cb) {
    this.hide(() => {
      options = {...this.defaultOptions, ...options};

      const notification = this.template
        .replaceAll('{{style}}', options.style)
        .replaceAll('{{content}}', message);

      $(document.body).append(notification);

      setTimeout(() => $(this.selector).addClass('show'));

      const button = $(this.selector).find('[data-admin-notify-button]');

      if (options.button) {
        button.removeClass('d-none');
        button.find('a').on('click', () => this.hide(cb));
      }

      if (options.dismissible) {
        setTimeout(() => this.hide(cb), options.dismissDelay);
      }
    });
  }

  hide(cb) {
    if ($(this.selector).length) {
      $(this.selector).removeClass('show');
      setTimeout(() => {
        $(this.selector).remove();
        cb && cb();
      }, 300);

    } else {
      cb && cb();
    }
  }
}
