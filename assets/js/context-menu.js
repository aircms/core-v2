class ContextMenu {
  static initialized = false;

  static cMAttr = 'data-admin-contextmenu';
  static cMTargetAttr = 'data-admin-contextmenu-target';

  cMEl;
  cMTargetEl;

  static listeners = {
    show: [],
    hide: []
  };

  static on(event, cb) {
    this.listeners[event].push(cb);
  }

  constructor(element) {
    this.cMEl = $(element);

    if (!this.cMEl.length) {
      throw new Error(`Element not found: ${element}`);
    }

    if (this.cMEl.attr(`${ContextMenu.cMAttr}-initialized`)) {
      return;
    }

    const elForSel = `[${ContextMenu.cMTargetAttr}="${this.cMEl.attr(ContextMenu.cMAttr)}"]`;
    this.cMTargetEl = $(elForSel);

    if (!this.cMTargetEl.length) {
      throw new Error(`El 'for' not found with selector: '${sel}' -> '${elForSel}'`);
    }

    this.cMEl.attr(`${ContextMenu.cMAttr}-initialized`, 'true');

    !this.cMTargetEl.hasClass('position-absolute')
    && this.cMTargetEl.addClass('position-absolute');

    !this.cMTargetEl.hasClass('top-0')
    && this.cMTargetEl.addClass('top-0');

    !this.cMTargetEl.hasClass('m-0')
    && this.cMTargetEl.addClass('m-0');

    this.cMEl.on('contextmenu', (event) => {
      ContextMenu.hideAll();
      $('body').append(this.cMTargetEl[0].outerHTML);

      const contextMenu = $(`body > [${ContextMenu.cMTargetAttr}]`);

      contextMenu
        .css('transform', 'translate(' + event.clientX + 'px, ' + event.clientY + 'px)')
        .addClass('show');

      ContextMenu.listeners.show.forEach((cb) => cb(this.cMEl, contextMenu));
      return false;
    });

    if (!ContextMenu.initialized) {
      ContextMenu.initialized = true;
      

      $(document).click(() => ContextMenu.hideAll());
      $('*').scroll(() => ContextMenu.hideAll());
    }
  }

  static hideAll() {
    const contextMenu = $(`body > [${ContextMenu.cMTargetAttr}]`);
    if (contextMenu.length) {
      ContextMenu.listeners.hide.forEach((cb) => cb(this.cMEl, contextMenu));
      contextMenu.remove();
    }
  }
}

$(document).ready(() => {
  wait.on('[data-admin-contextmenu]', (contextmenu) => new ContextMenu(contextmenu));
});