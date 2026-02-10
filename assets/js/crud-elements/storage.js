$(document).ready(() => {
  File.ready(() => {
    wait.on('[data-admin-form-storage]', (input) => {
      const updateValue = () => {
        const name = $(input).data('admin-form-storage');
        const files = [];

        $(input).find('[data-admin-file-json]').each((i, e) => {
          files.push(JSON.parse($(e).html()));
        });

        let filesInput = $(input).find(`[name="${name}"]`);

        if (!filesInput.length) {
          filesInput = $(input).find(`[name="${name}[value]"]`);
        }

        if ($(input).data('admin-form-storage-multiple')) {
          filesInput.val(JSON.stringify(files, null, 2));
        } else {
          filesInput.val(JSON.stringify(files[0], null, 2));
        }
      };

      $(input).find('[data-admin-form-storage-add]').on('click', (e) => {
        const isMultiple = !!$(e.currentTarget).data('admin-form-storage-multiple');
        const storageUrl = $(e.currentTarget).data('admin-form-storage-add-url');
        const storageKey = $(e.currentTarget).data('admin-form-storage-add-key');
        let path = '/';

        try {
          const images = $(e.currentTarget).next().find('[data-admin-file-json]');
          if (images.length) {
            const firstImage = JSON.parse($(images[0]).html());
            if (firstImage && firstImage.src) {
              path = '/' + firstImage.src.split('/').slice(2, -1).join('/');
            }
          }
        } catch {
        }

        const storageList = $(e.currentTarget)
          .closest('[data-admin-form-storage]')
          .find('[data-admin-form-storage-list]');

        modal.file(storageUrl, storageKey, isMultiple, path, (file) => {
          if (!file.alt) {
            file.alt = locale('Untitled');
          }

          if (!file.title) {
            file.title = '';
          }

          const html = `<div data-admin-file data-admin-file-storage-url="${storageUrl}">${JSON.stringify(file)}</div>`;

          if (isMultiple) {
            storageList.append(html);
            notify.success(locale('File added'));

          } else {
            storageList.html(html);
            modal.hide();
          }

          storageList.find('[data-admin-file]').each((i, e) => {
            new File(e, {
              change: () => setTimeout(() => updateValue(), 100), remove: () => setTimeout(() => updateValue(), 100),
            });
          });

          setTimeout(() => updateValue(), 200);
        });
      });

      if ($(input).find('[data-admin-form-storage-sortable]').length) {
        new Sortable($(input).find('[data-admin-form-storage-sortable]')[0], {
          animation: 150, onUpdate: () => updateValue()
        });
      }

      $(input).find('[data-admin-file]').each((i, e) => {
        new File(e, {
          change: () => setTimeout(() => updateValue(), 100), remove: () => setTimeout(() => updateValue(), 100)
        });
      });
    });
  });
});