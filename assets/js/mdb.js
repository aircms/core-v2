$(document).ready(() => {
  wait.on('[data-mdb-input-init]', (input) => new mdb.Input(input));
  wait.on('[data-mdb-ripple-init]', (ripple) => new mdb.Ripple(ripple));
  wait.on('[data-mdb-dropdown-init]', (dropdown) => new mdb.Dropdown(dropdown));
  wait.on('[data-mdb-tooltip-init]', (tooltip) => new mdb.Tooltip(tooltip));
  wait.on('[data-mdb-range-init]', (range) => new mdb.Range(range));
});