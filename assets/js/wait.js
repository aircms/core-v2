const wait = new class {
  listeners = [];

  start() {
    const observer = new MutationObserver((events) => {
      events.forEach((event) => {
        event.addedNodes.forEach((addedNode) => {
          this.listeners.forEach((listener) => {
            if ($(addedNode).is(listener.selector)) {
              listener.cb(addedNode);
            }
            $(addedNode).find(listener.selector).each((i, e) => listener.cb(e));
          });
        });
      });
    });
    observer.observe(document.body, {
      subtree: true,
      childList: true,
    });
  }

  on(selector, cb) {
    $(selector).each((i, e) => cb(e));
    this.listeners.push({selector, cb});
  }
}

$(document).ready(() => wait.start());