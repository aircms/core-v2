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
    plugins: ['advlist anchor link lists fullscreen code'],
    toolbar: 'styles fontfamily fontsize lineheight forecolor backcolor bold italic underline strikethrough align link bullist numlist code fullscreen',
    style_formats: [{title: 'Heading 1', block: 'h1'}, {title: 'Heading 2', block: 'h2'}, {
      title: 'Heading 3', block: 'h3'
    }, {title: 'Heading 4', block: 'h4'}, {title: 'Heading 5', block: 'h5'}, {title: 'Paragraph', block: 'p'},],
    paste_as_text: false,
    object_resizing: true,
    image_caption: true,
    language: $('html').attr('lang') === 'ua' ? 'uk' : 'en',

    lineheight_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 36pt",
    font_size_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16px 18pt 24pt 30pt 36pt 48pt 60pt 72pt 96pt",
    font_family_formats: ["Arial=arial,helvetica,sans-serif"],

    valid_styles: {
      '*': 'font-weight,font-style,text-decoration,color,background-color'
    },

    content_style: "strong, b {font-weight: 700 !important;}",

    formats: {
      bold: {inline: 'strong'}
    },

    schema: 'html5',
    valid_elements: '*[*]',
    extended_valid_elements: 'span[style|class],a[href|target|rel|style|class],strong/b,em/i,u,h1,h2,h3,h4,h5,h6,p,br,div[style|class]',
    valid_children: '+body[meta],+div[h1|h2|h3|h4|h5|h6|p|a|span|strong|em|u|br],+a[span|strong|em|u|#text]',
    invalid_styles: {},

    forced_root_block: false,
    verify_html: false,
    cleanup: false,

    setup(editor) {
      editor.on('change', () => {
        editor.save();
      });

      editor.on('paste', (e) => {

        const clipboard = e.clipboardData || window.clipboardData;
        if (!clipboard) return;

        const html = clipboard.getData('text/html');
        if (!html) return;

        e.preventDefault();
        editor.selection.setContent(html, {format: 'raw'});
      });
    }
  }

  constructor(selector, options, cb) {

    if (window.fontsUrl) {
      this.options.content_style = "@import url('/" + window.fontsUrl + "/css');";
    }

    if (Tiny.fonts) {
      this.options.font_family_formats.push(Tiny.fonts);
    }

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

    tinymce.init({...this.options, ...options}).then(() => cb && cb());
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
      autosize: true, min_height: 500, max_height: 700
    }));
  });
});


function rtfToHtml(rtf) {
  if (!rtf || typeof rtf !== 'string') {
    return '';
  }

  const tokens = parseRtfToTokens(rtf);
  if (!tokens.length) {
    return '';
  }

  return tokensToHtml(tokens);
}

function parseRtfToTokens(rtf) {
  const result = [];

  const knownDestinations = new Set(['fonttbl', 'colortbl', 'stylesheet', 'info', 'pict', 'object', 'header', 'footer', 'generator', 'xmlopen', 'xmlattrname', 'xmlattrvalue', 'xmlclose', 'themedata', 'datastore', 'rsidtbl', 'listtable', 'listoverridetable', 'revtbl', 'mmathPr', 'latentstyles']);

  const defaultState = () => ({
    bold: false, italic: false, underline: false, skip: false
  });

  let state = defaultState();
  const stack = [];
  let i = 0;
  let pendingStarDestination = false;

  function pushText(text) {
    if (!text || state.skip) return;
    result.push({
      type: 'text', text, bold: state.bold, italic: state.italic, underline: state.underline
    });
  }

  while (i < rtf.length) {
    const ch = rtf[i];

    if (ch === '{') {
      stack.push({
        state: {...state}, pendingStarDestination
      });
      pendingStarDestination = false;
      i++;
      continue;
    }

    if (ch === '}') {
      const prev = stack.pop();
      if (prev) {
        state = prev.state;
        pendingStarDestination = false;
      }
      i++;
      continue;
    }

    if (ch !== '\\') {
      pushText(ch);
      i++;
      continue;
    }

    // Мы на backslash
    i++;

    if (i >= rtf.length) break;

    const next = rtf[i];

    // escaped chars
    if (next === '\\' || next === '{' || next === '}') {
      pushText(next);
      i++;
      continue;
    }

    // hex char: \'hh
    if (next === "'") {
      const hex = rtf.slice(i + 1, i + 3);
      if (/^[0-9a-fA-F]{2}$/.test(hex)) {
        const code = parseInt(hex, 16);
        pushText(String.fromCharCode(code));
        i += 3;
        continue;
      }
    }

    // destination marker \*
    if (next === '*') {
      pendingStarDestination = true;
      i++;
      continue;
    }

    // control symbol
    if (!/[a-zA-Z]/.test(next)) {
      switch (next) {
        case '~':
          pushText('\u00A0');
          break;
        case '-':
          pushText('\u00AD');
          break;
        case '_':
          pushText('\u2011');
          break;
        default:
          break;
      }
      i++;
      continue;
    }

    // control word
    let word = '';
    while (i < rtf.length && /[a-zA-Z]/.test(rtf[i])) {
      word += rtf[i];
      i++;
    }

    let sign = 1;
    if (rtf[i] === '-') {
      sign = -1;
      i++;
    }

    let numStr = '';
    while (i < rtf.length && /[0-9]/.test(rtf[i])) {
      numStr += rtf[i];
      i++;
    }

    const hasParam = numStr.length > 0;
    const param = hasParam ? sign * parseInt(numStr, 10) : null;

    if (rtf[i] === ' ') {
      i++;
    }

    if (pendingStarDestination || knownDestinations.has(word)) {
      state.skip = true;
      pendingStarDestination = false;
      continue;
    }

    switch (word) {
      case 'rtf':
      case 'ansi':
      case 'ansicpg':
      case 'deff':
      case 'viewkind':
      case 'uc':
      case 'pard':
      case 'plain':
      case 'f':
      case 'fs':
      case 'cf':
      case 'highlight':
      case 'lang':
      case 'ltrpar':
      case 'rtlpar':
      case 'sa':
      case 'sb':
      case 'li':
      case 'ri':
      case 'fi':
      case 'ql':
      case 'qr':
      case 'qj':
      case 'qc':
        break;

      case 'b':
        state.bold = !hasParam || param !== 0;
        break;

      case 'i':
        state.italic = !hasParam || param !== 0;
        break;

      case 'ul':
        state.underline = !hasParam || param !== 0;
        break;

      case 'ulnone':
        state.underline = false;
        break;

      case 'par':
        result.push({type: 'par'});
        break;

      case 'line':
        result.push({type: 'line'});
        break;

      case 'tab':
        pushText('\t');
        break;

      case 'emdash':
        pushText('—');
        break;

      case 'endash':
        pushText('–');
        break;

      case 'lquote':
        pushText('‘');
        break;

      case 'rquote':
        pushText('’');
        break;

      case 'ldblquote':
        pushText('“');
        break;

      case 'rdblquote':
        pushText('”');
        break;

      case 'u': {
        if (hasParam) {
          let code = param;
          if (code < 0) code = 65536 + code;
          pushText(String.fromCharCode(code));

          // пропускаем fallback char после \uN
          if (i < rtf.length) {
            if (rtf[i] === '\\') {
              // fallback может быть control sequence, не трогаем
            } else {
              i++;
            }
          }
        }
        break;
      }

      default:
        break;
    }
  }

  return mergeAdjacentTextTokens(result);
}

function mergeAdjacentTextTokens(tokens) {
  const merged = [];

  for (const token of tokens) {
    if (token.type !== 'text') {
      merged.push(token);
      continue;
    }

    const prev = merged[merged.length - 1];
    if (prev && prev.type === 'text' && prev.bold === token.bold && prev.italic === token.italic && prev.underline === token.underline) {
      prev.text += token.text;
    } else {
      merged.push({...token});
    }
  }

  return merged;
}

function tokensToHtml(tokens) {
  const paragraphs = [];
  let currentParagraph = [];

  for (const token of tokens) {
    if (token.type === 'par') {
      paragraphs.push(currentParagraph);
      currentParagraph = [];
      continue;
    }

    if (token.type === 'line') {
      currentParagraph.push({type: 'html', html: '<br>'});
      continue;
    }

    currentParagraph.push(token);
  }

  if (currentParagraph.length) {
    paragraphs.push(currentParagraph);
  }

  const html = paragraphs
    .map((paragraphTokens) => {
      const inner = paragraphTokens
        .map((token) => {
          if (token.type === 'html') {
            return token.html;
          }

          if (token.type !== 'text') {
            return '';
          }

          let text = escapeHtml(token.text)
            .replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;')
            .replace(/\n/g, '<br>');

          if (!text.trim() && !text.includes('&nbsp;')) {
            return text;
          }

          if (token.underline) {
            text = `<u>${text}</u>`;
          }
          if (token.italic) {
            text = `<em>${text}</em>`;
          }
          if (token.bold) {
            text = `<strong>${text}</strong>`;
          }

          return text;
        })
        .join('');

      if (!inner.trim()) {
        return '';
      }

      return `<p>${inner}</p>`;
    })
    .filter(Boolean)
    .join('');

  return html;
}

function escapeHtml(str) {
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}