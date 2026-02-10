let modalScriptDir = document.currentScript.src.split('?')[0].split('/');
modalScriptDir.pop();
modalScriptDir = modalScriptDir.join('/');

const modal = new class {
  mdbModal = null;
  selector = '[data-modal]';

  sizes = {
    default: '',
    small: 'modal-sm',
    large: 'modal-lg',
    xLarge: 'modal-xl',
    xxLarge: 'modal-xxl',
  };

  styles = {
    default: {header: '', button: 'btn-primary'},
    primary: {header: 'bg-primary', button: 'btn-primary'},
    secondary: {header: 'bg-body-secondary', button: 'btn-secondary'},
    success: {header: 'bg-success', button: 'btn-success'},
    danger: {header: 'bg-danger', button: 'btn-danger'},
    warning: {header: 'bg-warning', button: 'btn-warning'},
    info: {header: 'bg-info', button: 'btn-info'},
    light: {header: 'bg-light text-dark', button: 'btn-light'},
    dark: {header: 'bg-dark', button: 'btn-dark'},
  };

  defaultOptions = {
    style: 'default',
    size: 'default',
    staticBackdrop: true,
    vPositionCenter: false,
    fullscreen: false,
  };

  templates = {
    message: '',
    question: '',
    prompt: '',
    promptBig: '',
    html: '',
    container: '',
    file: '',
    model: '',
    tiny: ''
  };

  embedTemplates = {
    image: '',
    video: '',
    pdf: '',
    text: '',
    iframe: '',
    any: '',
  };

  constructor() {
    $.get(modalScriptDir + '/templates/container.html', (t) => this.templates.container = t);
    $.get(modalScriptDir + '/templates/message.html', (t) => this.templates.message = t);
    $.get(modalScriptDir + '/templates/question.html', (t) => this.templates.question = t);
    $.get(modalScriptDir + '/templates/prompt.html', (t) => this.templates.prompt = t);
    $.get(modalScriptDir + '/templates/promptBig.html', (t) => this.templates.promptBig = t);
    $.get(modalScriptDir + '/templates/html.html', (t) => this.templates.html = t);
    $.get(modalScriptDir + '/templates/file.html', (t) => this.templates.file = t);
    $.get(modalScriptDir + '/templates/model.html', (t) => this.templates.model = t);
    $.get(modalScriptDir + '/templates/tiny.html', (t) => this.templates.tiny = t);

    $.get(modalScriptDir + '/templates/image.html', (t) => this.embedTemplates.image = t);
    $.get(modalScriptDir + '/templates/video.html', (t) => this.embedTemplates.video = t);
    $.get(modalScriptDir + '/templates/pdf.html', (t) => this.embedTemplates.pdf = t);
    $.get(modalScriptDir + '/templates/text.html', (t) => this.embedTemplates.text = t);
    $.get(modalScriptDir + '/templates/iframe.html', (t) => this.embedTemplates.iframe = t);
    $.get(modalScriptDir + '/templates/any.html', (t) => this.embedTemplates.any = t);
  }

  message(message, options) {
    return new Promise(resolve => {
      this.open(this.templates.message, {message}, this.mergeOpts(options, {size: 'small'}), () => resolve());
    });
  }

  question(question, options) {
    return new Promise((resolve, reject) => {
      this.open(this.templates.question, {question}, this.mergeOpts(options, {size: 'small'}));

      let yesClicked = false;
      $(this.selector).find('[data-yes]').on('click', () => {
        resolve();
        yesClicked = true;
        this.hide();
      });
      $(this.selector)[0].addEventListener('hidden.mdb.modal', () => !yesClicked && reject());
    });
  }

  prompt(title, label, options) {
    return new Promise((resolve) => {
      this.open(this.templates.prompt, {title, label}, this.mergeOpts(options, {}));
      $(this.selector).find('[data-admin-modal-prompt]').on('submit', (e) => {
        const val = $(e.currentTarget).find('input[type=text]').val();
        if (val.length) {
          resolve(val);
          this.hide();
        }
        return false;
      });
    });
  }

  promptBig(title, label, options) {
    return new Promise((resolve) => {
      this.open(this.templates.promptBig, {title, label}, this.mergeOpts(options, {}));
      $(this.selector).find('[data-admin-modal-prompt-big]').on('submit', (e) => {
        const val = $(e.currentTarget).find('textarea').val();
        if (val.length) {
          resolve(val);
          this.hide();
        }
        return false;
      });
    });
  }

  tiny(content, cb) {
    const html = this.templates.tiny.replaceAll('{{content}}', content);
    this.open(html, {title: locale('Edit HTML'), html}, this.mergeOpts({size: 'xxLarge'}, {}));
    $(this.selector).find('[data-save]').click(() => {
      cb && cb($(this.selector).find('[data-admin-tiny]').val());
      this.hide();
    });
  }

  html(title, content, options, cb) {
    return new Promise(resolve => {
      this.open(this.templates.html, {title, content}, this.mergeOpts(options, {}), cb);
      resolve();
    });
  }

  image(src, alt, title) {
    return this.html(locale('Image'), this.replace(this.embedTemplates.image, {
      src,
      alt,
      title
    }), {size: 'xLarge'}).then(() => {
      setTimeout(() => wheelzoom(document.querySelector('.modal img[data-zoom]')), 300);
    });
  }

  video(src, alt, title) {
    return this.html(locale('Video'), this.replace(this.embedTemplates.video, {src, alt, title}), {size: 'xLarge'});
  }

  pdf(src, alt, title) {
    return this.html('PDF', this.replace(this.embedTemplates.pdf, {src, alt, title}), {size: 'xLarge'});
  }

  htmlAjax(src, title) {
    return new Promise(() => {
      $.get(src, (content) => {
        this.html(title, content, {size: 'xLarge'});
      });
    });
  }

  csv(src, alt, title) {
    return new Promise(() => {
      $.get(src, (content) => {
        this.html(locale('CSV'), this.replace(this.embedTemplates.text, {
          content: csvToTable(content, {classes: "csv table table-bordered"}),
          alt,
          title
        }), {size: 'xLarge'});
      });
    });
  }

  text(src, alt, title) {
    return new Promise(() => {
      $.get(src, (content) => {
        this.html(locale('Text'), this.replace(this.embedTemplates.text, {content, alt, title}), {size: 'xLarge'});
      });
    });
  }

  iframe(src, title, cb) {
    this.html(title || locale('Embed'), this.replace(this.embedTemplates.iframe, {src}), {size: 'xxLarge'}, cb).then(() => {
      $(this.selector).find('iframe').on('load', (e) => {
        setTimeout(() => {
          $(e.currentTarget).addClass('show');
          $('[data-modal-loader]').removeClass('show');
          setTimeout(() => $('[data-modal-loader]').remove(), 300);
        }, 300);
      });
    });
  }

  any(src, alt, title, mime) {
    return this.html(locale('Any'), this.replace(this.embedTemplates.any, {src, alt, title, mime}), {size: 'xLarge'});
  }

  embed(src, alt, title, mime) {
    if (!mime) {
      return this.iframe(src);

    } else if (mime.includes('image')) {
      return this.image(src, alt, title);

    } else if (mime.includes('video')) {
      return this.video(src, alt, title);

    } else if (mime.includes('pdf')) {
      return this.pdf(src, alt, title);

    } else if (mime.includes('csv')) {
      return this.csv(src, alt, title);

    } else if (mime.includes('text')) {
      return this.text(src, alt, title);
    }
    return this.any(src, alt, title, mime);
  }

  file(url, key, isMultiple, path, cb) {
    const modalHtml = this.templates.file
      .replaceAll('{{url}}', url)
      .replaceAll('{{key}}', key)
      .replaceAll('{{theme}}', theme.theme)
      .replaceAll('{{path}}', path ?? '/')
      .replaceAll('{{isMultiple}}', isMultiple ? '1' : '0');

    modal.html(locale('Select file'), modalHtml, {size: 'xxLarge'}).then(() => {
      $('[data-admin-file-modal]').on('load', (e) => {
        $(e.currentTarget).addClass('show');
        $(window).off('message').on('message', (message) => {
          if (message.originalEvent.data.file && cb) {
            cb(message.originalEvent.data.file);
          }
        });
      });
    });
  }

  record(model, id) {
    const modalHtml = this.templates.model.replaceAll('{{url}}', `/${model.split('\\').slice(-1)[0]}/manage?id=` + id + '&isQuickManage=1');
    modal.html(locale('View row'), modalHtml, {size: 'xxLarge'}, () => {
      if ($('[data-admin-table-form]').length) {
        nav.reload();
      }
    }).then(() => {
      $('[data-admin-model-modal]').on('load', (e) => {
        $('[data-modal-loader]').removeClass('show');
        setTimeout(() => $('[data-modal-loader]').remove(), 300);
        $(e.currentTarget).addClass('show');
      });
    });
  }

  model(model, cb, filter = {}) {
    const modalHtml = this.templates.model.replaceAll('{{url}}', `/${model.split('\\').slice(-1)[0]}/select?` + $.param(filter));
    modal.html(locale('Select row'), modalHtml, {size: 'xxLarge'}).then(() => {
      $('[data-admin-model-modal]').on('load', (e) => {
        $(e.currentTarget).addClass('show');
        $('[data-modal-loader]').removeClass('show');
        setTimeout(() => $('[data-modal-loader]').remove(), 300);
        $(window).off('message').on('message', (message) => {
          if (message.originalEvent.data.row && cb) {
            cb(message.originalEvent.data.row);
          }
        });
      });
    });
  }

  open(template, vars, options, cb = null) {
    this.hide(() => {
      const modal = this.addModal(template, vars, options);

      this.mdbModal = new mdb.Modal(modal);
      this.mdbModal.show();

      modal[0].addEventListener('hidden.mdb.modal', () => {
        cb && cb();
        modal.remove();
      });
    });
  }

  mergeOpts(uOpts, bOpts) {
    return {...this.defaultOptions, ...bOpts, ...uOpts};
  }

  addModal(template, vars, options) {
    const templateOptions = {
      ...vars, ...{
        styleHeader: this.styles[options.style].header,
        styleButton: this.styles[options.style].button,
      }
    };

    const modalContent = this.replace(
      template,
      templateOptions
    );

    const modalOptions = {
      size: options.size && this.sizes[options.size] ? this.sizes[options.size] : '',
      staticBackdrop: options.staticBackdrop ? 'data-mdb-backdrop="static"' : '',
      vPositionCenter: options.vPositionCenter ? 'modal-dialog-centered' : '',
      fullscreen: options.fullscreen ? 'modal-fullscreen' : '',
      content: modalContent
    };

    const modal = this.replace(
      this.templates.container,
      modalOptions
    );

    $('body').append(modal);
    return $(this.selector);
  }

  hide(cb) {
    if ($(this.selector).length) {
      this.mdbModal.hide();
      setTimeout(() => cb && cb(), 700);
    } else cb && cb();
  }

  replace(t, vars = {}) {
    $.each(vars, (k, v) => t = t.replaceAll('{{' + k + '}}', v));
    return t;
  }
};

$(document).ready(() => {

  document.addEventListener('focusin', (e) => {
    if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
      e.stopImmediatePropagation();
    }
  });

  wait.on('[data-admin-embed-modal]', (image) => {
    if ($(image).data('admin-embed-modal-initialized')) {
      return;
    }
    $(image).attr('data-admin-embed-modal-initialized', 'true');

    $(image).on($(image).data('admin-embed-modal-event') || 'click', (event) => {
      const alt = $(event.currentTarget).data('admin-embed-modal-alt') || 'Untitled';
      const title = $(event.currentTarget).data('admin-embed-modal-title') || '';
      const src = $(event.currentTarget).data('admin-embed-modal-src');
      const mime = $(event.currentTarget).data('admin-embed-modal-mime') || 'image/jpeg';

      modal.embed(src, alt, title, mime);
    });
  });
});