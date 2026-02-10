$(document).ready(() => wait.on('[data-deactivate]', (checkbox) => {
  const dataCheckboxOverlay = '<div data-admin-checkbox-overlay class="bg-blur-1 disable-overlay"></div>';
  const updateCheckboxOverlay = (checkbox) => {

    const applyOverlayToInput = ($input, disable) => {
      if ($input.length) {
        const container = $input.closest('.form-outline');
        if (disable) {
          container.find('[data-admin-checkbox-overlay]').remove();
          container.prepend(dataCheckboxOverlay);
        } else {
          container.find('[data-admin-checkbox-overlay]').remove();
        }
      }
    };

    try {
      const value = $(checkbox).is(':checked');
      const deactivate = $(checkbox).data('deactivate');
      const deactivateWhen = $(checkbox).data('deactivate-when');

      deactivate.forEach((field) => {
        if ($('[name="' + field + '"]').length) {
          applyOverlayToInput($('[name="' + field + '"]'), deactivateWhen === value);

        } else if (field.includes('[]')) {
          const start = field.split('[]')[0] + "[";
          const end = "]" + field.split('[]')[1];
          const multipleContainer = $(checkbox).closest('[data-admin-form-element-group-multiple-group-item-container]');
          multipleContainer.find('[name]').each((i, input) => {
            if ($(input).attr('name').startsWith(start) && $(input).attr('name').endsWith(end)) {
              applyOverlayToInput($(input), deactivateWhen === value);
            }
          });
        }
      });
    } catch {
    }
  };
  $(checkbox).on('change', () => updateCheckboxOverlay(checkbox));
  updateCheckboxOverlay(checkbox);
}));