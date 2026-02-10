$(document).ready(() => {
  $(document).on('click', '[data-admin-faicon] [data-admin-faicon-iframe]', function () {
    const container = $(this).closest('[data-admin-faicon]');
    modal.model($(this).data('admin-faicon-iframe'), function (icon) {
      container.find('[data-admin-faicon-input]').val(JSON.stringify({
        icon: icon.icon,
        style: icon.style
      }));
      container.find('[data-admin-faicon-value]').html(
        '<i class="fa-' + icon.icon + ' ' + icon.style + ' me-3 fs-5"></i> ' + icon.title
      );
      modal.hide();
    });
  });
  $(document).on('dblclick', '[data-admin-faicon-id]', function () {
    window.parent.postMessage({
      row: {
        icon: $(this).data('admin-faicon-id'),
        title: $(this).data('admin-faicon-title'),
        style: $('[data-admin-select-input]').val()
      }
    }, "*");
  });
});