let embedScriptDir = document.currentScript.src.split('?')[0].split('/');
embedScriptDir.pop();
embedScriptDir = embedScriptDir.join('/');

class Embed {
  static template = null;

  static ready(cb) {
    if (Embed.template) {
      cb && cb();
      return;
    }

    $.get(embedScriptDir + '/template.html', (t) => {
      Embed.template = t;
      cb && cb();
    });
  }

  id = null;
  element = null;
  url = null;
  removable = true;
  options = {
    remove: null,
  };

  constructor(selector, options) {
    this.options = {...this.options, ...options};
    this.element = $(selector);

    this.url = this.element.data('admin-embed-src');
    this.id = this.element.data('admin-embed-id');
    this.removable = !!this.element.data('admin-embed-removable');

    this.element.replaceWith(Embed.template.replaceAll('{{url}}', this.url).replaceAll('{{id}}', this.id));
    this.element = $(`[data-admin-embed-item="${this.id}"]`);

    this.element.find('iframe').on('load', (e) => {
      $(e.currentTarget).closest('[data-admin-embed-item]').addClass('show');
    });

    if (this.removable) {
      this.element.find('[data-admin-embed-item-remove]')
        .removeClass('d-none')
        .click(() => {
          modal.question(locale('Remove embed?')).then(() => {
            if (this.options.remove) {
              this.options.remove(this.element);
            }
            this.element.remove();
          });
        });
    }
  }
}

Embed.ready(() => {
  wait.on('[data-admin-embed]', (embed) => new Embed(embed));
});