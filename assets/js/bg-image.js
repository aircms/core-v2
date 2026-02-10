$(document).ready(() => {
  wait.on('[data-admin-async-image]', (asyncBgImage) => {
    $(asyncBgImage).css('background-image', `url('${$(asyncBgImage).data('admin-async-image')}')`);
  });
});