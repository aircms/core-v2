let sideScriptDir = document.currentScript.src.split('?')[0].split('/');
sideScriptDir.pop();
sideScriptDir = sideScriptDir.join('/');

const side = new class {
  mdbModals = [];
  template = null;

  sizes = {
    default: 'modal-xl',
    small: 'modal-sm',
    large: 'modal-lg',
    xLarge: 'modal-xl',
    xxLarge: 'modal-xxl',
  }

  constructor() {
    $.get(sideScriptDir + '/template.html', (t) => this.template = t);
  }

  open(title, content, openCb, closeCb, options) {
    const sideId = "side" + Math.random();
    const selector = '[data-side="' + sideId + '"]';
    const side = this.replace(this.template, {
      sideId,
      title,
      content,
      size: options?.size || this.sizes.default
    });

    $('body').append(side);

    const mdbModal = new mdb.Modal(selector);
    mdbModal.show();

    openCb && openCb();

    this.mdbModals.push(mdbModal);

    $(selector)[0].addEventListener('hidden.mdb.modal', () => {
      closeCb && closeCb();
      $(selector).remove();
    });
  }

  hide(cb) {
    if (this.mdbModals.length) {
      this.mdbModals.forEach((modal) => modal.hide());
      setTimeout(() => cb && cb(), 200);
    } else cb && cb();
  }

  replace(t, vars = {}) {
    $.each(vars, (k, v) => t = t.replaceAll('{{' + k + '}}', v));
    return t;
  }
};