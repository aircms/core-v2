$(document).ready(() => wait.on('[data-deactivate]', (checkbox) => {
  const dataCheckboxOverlay = '<div data-admin-checkbox-overlay class="bg-blur-1 disable-overlay"></div>';
  const updateCheckboxOverlay = (checkbox) => {

    const applyOverlayToInput = ($input, disable) => {
      if ($input.length) {
        if (disable) {
          $input.find('[data-admin-checkbox-overlay]').remove();
          $input.prepend(dataCheckboxOverlay);
        } else {
          $input.find('[data-admin-checkbox-overlay]').remove();
        }
      }
    };

    try {
      const value = $(checkbox).is(':checked');
      const deactivate = $(checkbox).data('deactivate');
      const deactivateWhen = $(checkbox).data('deactivate-when');

      deactivate.forEach((field) => {
        applyOverlayToInput($('[data-form-element-container="' + field + '"]'), deactivateWhen === value);
      });
    } catch {
    }
  };
  $(checkbox).on('change', () => updateCheckboxOverlay(checkbox));
  updateCheckboxOverlay(checkbox);
}));