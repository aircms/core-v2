$(document).ready(() => {

  $(document).on('keydown', '[name="filter[search]"]', function (e) {
    if (e.keyCode === 13) {
      $('[data-admin-table-form]').submit();
    }
  });

  $(document).on('click', '[data-admin-table-form] [data-search-button]', () => $('[data-admin-table-form]').submit());

  $(document).on('click', '[data-admin-table-row-select]', (e) => {
    const row = JSON.parse($(e.currentTarget).find('[data-admin-table-row-object]').html());
    window.parent.postMessage({row: row}, "*");
  });

  $(document).on('keydown', (e) => {
    const searchInput = $('[name="filter[search]"]');
    if (searchInput.length && e.key === 'f' && e.ctrlKey) {
      e.preventDefault();
      searchInput.focus();
      searchInput[0].setSelectionRange(0, $(searchInput).val().length);
    }
  });

  $(document).on('click', '[data-admin-table-row-copy]', function () {
    modal.question($(this).data('admin-table-row-copy-message') || 'Copy record?').then(() => {
      $.post($(this).data('admin-table-row-copy'))
        .done(() => {
          nav.reload();
          notify.success(locale('Yeah! Record has been copied.'));
        })
        .fail(() => notify.danger(locale('Something went wrong. Can not copy record')));
    });
  });

  $(document).on('click', '[data-admin-table-row-activity]', function () {
    modal.question($(this).data('confirm')).then(() => {
      $.post($(this).data('admin-table-row-activity'))
        .done(() => {
          nav.reload();
          notify.success(locale('Yeah! Visibility has been changed'));
        })
        .fail(() => notify.danger(locale('Something went wrong. Can not set visibility')));
    });
  });

  $(document).on('click', '[data-run-and-reload-url]', function () {
    const request = () => {
      $.post($(this).data('run-and-reload-url'))
        .done(() => {
          nav.reload();
          notify.success(locale('Success'));
        })
        .fail(() => notify.danger(locale('Something went wrong.')));
    };

    if ($(this).data('run-and-reload-confirm')) {
      modal.question($(this).data('run-and-reload-confirm')).then(() => request());
    } else {
      request();
    }
    return false;
  });

  $(document).on('click', '[data-admin-table-paginator] [data-page]', function () {
    $('[data-admin-table-form] [name="page"]').val($(this).data('page'));
    $('[data-admin-table-form]').submit();
  });

  $(document).on('submit', '[data-admin-table-form]', function () {
    nav.nav(location.pathname + '?' + $(this).serialize());
    return false;
  });

  $(document).on('click', '[data-admin-table-view-model][data-admin-table-view-id]', function () {
    modal.record(
      $(this).data('admin-table-view-model'),
      $(this).data('admin-table-view-id'),
    );
    return false;
  });

  const updateCheckboxes = () => {
    let allChecked = true;
    let checkCount = 0;
    const ids = [];
    $('[data-main-table-selectable]').each(function () {
      if ($(this).data('main-table-selectable') !== 'main') {
        if (!$(this).prop('checked')) {
          allChecked = false;
        } else {
          checkCount++;
          ids.push($(this).data('main-table-selectable'));
        }
      }
    });
    $('[data-main-table-selectable="main"]').prop('checked', allChecked);
    if (checkCount) {
      $('[data-bulk-manage] span').html('(' + checkCount + ')');
    } else {
      $('[data-bulk-manage] span').html('');
    }
    $('[data-bulk-manage]').attr('data-ids', ids.join(','));
  };

  $(document).on('click', '[data-bulk-manage]', function () {
    const ids = ($(this).attr('data-ids') || '').split(',').filter((c) => c.length);

    const url = ids.length
      ? '/' + $(this).data('bulk-manage') + '/manageMultiple?ids=' + ids.join(',')
      : $(this).data('href');

    modal.iframe(url, locale('Bulk editing'), () => nav.reload());
  });

  $(document).on('click', '[data-main-table-row-select]', function () {
    const id = $(this).data('main-table-row-select');
    const checkbox = $('[data-main-table-selectable="' + id + '"]');
    checkbox.prop('checked', !checkbox.prop('checked'));
    updateCheckboxes();
  });

  $(document).on('click', '[data-main-table-row-selectable]', function () {
    const id = $(this).data('main-table-row-selectable');
    const checkbox = $('[data-main-table-selectable="' + id + '"]');
    checkbox.prop('checked', !checkbox.prop('checked'));
    updateCheckboxes();
    return false;
  });

  $(document).on('click', '[data-main-table-selectable="main"]', function () {
    $('[data-main-table-selectable]').prop('checked', $(this).prop('checked'));
    updateCheckboxes();
  });
});