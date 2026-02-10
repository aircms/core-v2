$(document).ready(() => {

  const disableUserData = (e) => {
    $(e).closest('[data-admin-meta]').find('[data-admin-meta-user-data-overlay]').addClass('d-none');
  };

  const enableUserData = (e) => {
    $(e).closest('[data-admin-meta]').find('[data-admin-meta-user-data-overlay]').removeClass('d-none');
  };

  const setUserDataStatus = (e) => {
    if (!$(e).is(':checked')) {
      disableUserData(e);
    } else {
      enableUserData(e);
    }
  };

  wait.on('[data-admin-meta-use-model-data]', (e) => {
    setUserDataStatus(e);
    $(e).on('change', (event) => {
      setUserDataStatus(event.currentTarget);
    });
  });
});