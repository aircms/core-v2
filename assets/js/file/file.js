let fileScriptDir = document.currentScript.src.split('?')[0].split('/');
fileScriptDir.pop();
fileScriptDir = fileScriptDir.join('/');

class File {
  static template = null;

  static ready(cb) {
    if (File.template) {
      cb && cb();
      return;
    }

    $.get(fileScriptDir + '/template.html', (t) => {
      File.template = t;
      cb && cb();
    });
  }

  options = {
    change: null,
    remove: null
  };

  file = {
    size: 0,
    mime: "",
    path: "",
    time: 0,
    src: '',
    thumbnail: "",
    title: '',
    alt: '',
    dims: {
      width: 0,
      height: 0
    }
  };

  element = null;
  id = null;
  storageUrl = null;

  constructor(selector, options) {
    this.element = $(selector);

    if (this.element.data('admin-file-initialized')) {
      return
    }

    this.element.attr('data-admin-file-initialized', 'true');

    this.options = {...this.options, ...options};
    this.storageUrl = this.element.data('admin-file-storage-url');

    const fileJSON = $(selector).html();
    this.file = JSON.parse(fileJSON);

    this.id = parseInt(Math.random() * Date.now());

    this.render();

    wait.on(`[data-admin-file-preview="${this.id}"]`, (preview) => {
      $(preview).click(() => $(`[data-admin-file-container="${this.id}"]`).dblclick());
    });

    wait.on(`[data-admin-file-edit-captions="${this.id}"]`, (editCaptions) => {
      $(editCaptions).click(() => {
        $.get(fileScriptDir + '/edit-captions.html', (form) => {
          form = form.replaceAll('{{alt}}', this.file.alt).replaceAll('{{title}}', this.file.title);

          modal.html('Edit captions', form).then(() => {
            const form = $('[data-admin-file-edit-captions-form]');
            setTimeout(() => form.find('[name="alt"]').focus(), 500);

            const titleElement = getTitle(this.element);
            let descriptionElement = getTitle(this.element, 'description');

            if (!descriptionElement) {
              descriptionElement = getTitle(this.element, 'subTitle');
            }

            if (titleElement || descriptionElement) {
              const fillAutomaticallyButton = $('[data-admin-file-edit-captions-form-automarically]');
              fillAutomaticallyButton.removeClass('d-none');

              fillAutomaticallyButton.on('click', () => {
                form.find('[name="alt"]').val(titleElement);
                form.find('[name="title"]').val(descriptionElement);
              });
            }

            form.on('submit', (e) => {
              this.file.alt = $(e.currentTarget).find('[name="alt"]').val();
              this.file.title = $(e.currentTarget).find('[name="title"]').val();
              this.render();
              this.options?.change(this.element, this.file);
              modal.hide();
              return false;
            });
          });
        });
      });
    });

    wait.on(`[data-admin-file-remove="${this.id}"]`, (remove) => {
      $(remove).click(() => modal.question('Remove file?').then(() => {
        if (this.options.remove) {
          this.options.remove(this.element, this.file);
        }
        this.element.remove();
      }));
    });
  }

  render() {
    let thumbnail = this.file.thumbnail;
    if (!thumbnail.startsWith('http')) {
      thumbnail = this.storageUrl + this.file.thumbnail;
    }

    let src = this.file.src;
    if (!src.startsWith('http')) {
      src = this.storageUrl + this.file.src;
    }

    this.element.replaceWith(
      File.template
        .replaceAll('{{alt}}', this.file.alt)
        .replaceAll('{{title}}', this.file.title)
        .replaceAll('{{displayedTitle}}', this.file.title || '<p class="text-muted small" data-locale="(no title)"></p>')
        .replaceAll('{{mime}}', this.file.mime)
        .replaceAll('{{path}}', this.file.path)
        .replaceAll('{{src}}', src)
        .replaceAll('{{thumbnail}}', thumbnail)
        .replaceAll('{{id}}', this.id)
        .replaceAll('{{size}}', this.formatBytes(this.file.size))
        .replaceAll('{{width}}', this.file.dims.width)
        .replaceAll('{{height}}', this.file.dims.height)
        .replaceAll('{{time}}', this.formatTime(this.file.time))
        .replaceAll('{{fileJSON}}', JSON.stringify(this.file))
        .replaceAll('{{mime}}', this.file.mime));

    this.element = $(`[data-admin-file-container="${this.id}"]`);
  }

  formatBytes(bytes, decimals = 2) {
    if (!+bytes) return '0b';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['b', 'kb', 'mb', 'gb'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))}${sizes[i]}`;
  }

  formatTime(timestamp) {
    const date = new Date(timestamp * 1000);

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${day}/${month}/${year} ${hours}:${minutes}`;
  }
}