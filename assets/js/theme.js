const theme = new class {
  listeners = [];
  theme = 'dark';

  constructor() {
    this.theme = cookie.get('theme') || this.theme;
  }

  change(cb) {
    this.listeners.push(cb);
  }

  toggle() {
    $(document.body).attr('data-mdb-theme') === 'dark' ?
      this.set('light') :
      this.set('dark');
  }

  set(theme) {
    $(document.body).append('<style data-theme-changer>*{transition: all .1s ease !important;}</style>');
    setTimeout(() => $('[data-theme-changer]').remove(), 500);

    $(document.body).attr('data-mdb-theme', theme);
    cookie.set('theme', theme);

    this.theme = theme;

    this.listeners.forEach((cb) => cb(this.theme));
  }
};

theme.change(() => nav.getCurrentUrl().startsWith('/_storage') && nav.reload());