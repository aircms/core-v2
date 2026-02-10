$(document).ready(() => {
  $(document).on('input', '[data-admin-faicon-search-input]', (e) => {

    const container = $(e.currentTarget).closest('[data-admin-faicon-search-container]');
    const query = $(e.currentTarget).val();

    if (!query.length) {
      container.find('[data-admin-faicon-search]').removeClass('d-none');

    } else {
      setTimeout(() => {
        container.find('[data-admin-faicon-search]').each((index, icon) => {
          if ($(icon).data('admin-faicon-search').toString().indexOf(query) != -1) {
            $(icon).removeClass('d-none');
          } else {
            $(icon).addClass('d-none');
          }
        });
      });
    }
  });

  $(document).on('blur', '[data-admin-icon-search-input]', (e) => {
    setTimeout(() => {
      $(e.currentTarget).val('');
      $(e.currentTarget).closest('[data-admin-icon-search-container]')
        .find('[data-admin-icon-search]').removeClass('d-none');
    }, 100);
  });

  $(document).on('click', '[data-admin-icon-search]', (e) => {
    const container = $(e.currentTarget).closest('[data-admin-icon]');
    const iconTitle = $(e.currentTarget).data('admin-icon-title').toString();
    const iconId = $(e.currentTarget).data('admin-icon-id').toString();

    container.find('[data-admin-icon-input]').val(iconId);
    container.find('[data-admin-icon-value]').html(
      `<i class="fs-6 material-symbols-outlined me-1">${iconId}</i>` +
      `<span>${iconTitle}</span>`
    );
  });
});