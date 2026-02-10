class Tiny {
  static isReady = false;
  static fonts = [];

  static ready(cb) {
    if (!Tiny.isReady) {
      if (window.fontsUrl) {
        $.get('/' + window.fontsUrl + '/fonts', (fonts) => {
          Tiny.fonts = fonts;
          cb && cb();
        });
      } else {
        cb && cb();
      }
    } else {
      cb && cb();
    }
  }

  options = {
    menubar: 'edit insert view format lineheight table tools html',
    toolbar_sticky: false,
    toolbar_items_size: 'small',
    plugins: ['advlist anchor link lists fullscreen code paste textcolor lineheight'],
    toolbar: 'styles fontfamily fontsize lineheight forecolor backcolor bold italic underline strikethrough align link bullist numlist code fullscreen',
    style_formats: [
      {title: 'Heading 1', block: 'h1'},
      {title: 'Heading 2', block: 'h2'},
      {title: 'Heading 3', block: 'h3'},
      {title: 'Heading 4', block: 'h4'},
      {title: 'Heading 5', block: 'h5'},
      {title: 'Paragraph', block: 'p'},
    ],
    paste_as_text: true,
    object_resizing: true,
    image_caption: true,
    language: $('html').attr('lang') === 'ua' ? 'uk' : 'en',

    lineheight_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 36pt",
    font_size_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16px 18pt 24pt 30pt 36pt 48pt 60pt 72pt 96pt",
    font_family_formats: ["Arial=arial,helvetica,sans-serif", Tiny.fonts].filter(n => n).join('; '),
    content_style: "@import url('/" + window.fontsUrl + "/css'); img {width: 100%}",

    setup: (editor) => editor.on('change', () => editor.save())
  }

  constructor(selector, options, cb) {
    if (selector instanceof HTMLElement) {
      const id = Math.random();
      $(selector).attr('data-admin-form-tiny-selector', id);
      selector = '[data-admin-form-tiny-selector="' + id + '"]';
    }

    this.options.selector = selector;

    if (theme.theme === 'dark') {
      this.options.skin = 'oxide-dark';
      this.options.content_css = 'dark';
    }

    if (options.autosize) {
      this.options.plugins += ' autoresize';
    }

    tinymce.init({...this.options, ...options}).then(() => {
      cb && cb();
    });
  }
}

$(document).ready(() => {
  Tiny.ready(() => {
    $(document).on('dblclick', '[data-admin-form-tiny]', function () {
      const preview = $(this).find('[data-admin-form-tiny-preview]');
      const textarea = $(this).find('textarea');

      modal.tiny(textarea.val(), (html) => {
        textarea.val(html);
        preview.html(html);
      });
    });
    wait.on('[data-admin-tiny]', (tiny) => new Tiny(tiny, {
      autosize: true,
      min_height: 500,
      max_height: 700
    }));
  });
});