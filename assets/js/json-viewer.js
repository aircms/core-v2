$(document).ready(() => {
  wait.on('[data-json-viewer]', (el) => {
    const id = "jsonViever" + Math.floor(Math.random() * (99999999999 - 10000000 + 1) + 10000000);
    const json = JSON.parse($(el).val());
    $(el).replaceWith('<pre class="json-viewer" id="' + id + '"></pre>');
    $('#' + id).jsonViewer(json, {
      collapsed: false, rootCollapsable: true, withQuotes: false, withLinks: true
    });
  });
});