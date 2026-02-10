$(document).ready(() => {

  const getPage = (name) => {

    const page = JSON.parse($('[name="' + name + '"]').val() || '{}');

    if (!Object.keys(page).length) {
      const container = $('[data-admin-form-doc-page-element-container="' + name + '"]');
      return {
        width: parseInt(container.find('[data-admin-form-doc-toolbar-size="width"]').val()),
        height: parseInt(container.find('[data-admin-form-doc-toolbar-size="height"]').val()),
        gutter: parseInt(container.find('[data-admin-form-doc-toolbar-gutter]').val()),
        transparent: 100,
        backgroundColor: null,
        backgroundImage: null,
        items: []
      };
    }

    return page;
  };

  const setPage = (name, page) => {
    $('[name="' + name + '"]').val(JSON.stringify(page, null, 2));
  };

  const updatePage = (name, data) => {
    if (!data.width || !data.height) {

    }

    let page = getPage(name);
    page = {...page, ...data};
    setPage(name, page);
  };

  const setItemDimensions = (e) => {
    context(e, ({name, itemIndex}) => {
      updateItem(name, itemIndex, {
        width: Math.floor(e.width()),
        height: Math.floor(e.height()),
        x: Math.floor(e.position().left),
        y: Math.floor(e.position().top),
      });
    });
  };

  const moveElementTop = (e) => {
    context(e, ({name, page, itemIndex}) => {
      const items = getPage(name).items || [];

      let biggest = 0;

      page.find('[data-admin-form-doc-page-item]').each((i, element) => {
        const zIndex = parseInt($(element).css('z-index')) || i;
        if (zIndex > biggest) {
          biggest = zIndex;
        }
        $(element).css('z-index', zIndex);
        items[i].deep = zIndex;
      });
      e.css('z-index', biggest + 1);

      items[itemIndex].deep = biggest + 1;
      updatePage(name, {items});
      initItems();
    });
  };

  const updateHelper = (element, action, ui) => {
    let helper = $(element).find('.helper');
    if (!helper.length) {
      $(element).append('<div class="helper"></div>');
      helper = $(element).find('.helper');
    }

    let val1;
    let val2;

    if (action === 'resize') {
      helper.removeClass('draggable').addClass('resizable');

      val1 = ui.size.width;
      val2 = ui.size.height;

    } else {
      helper.addClass('draggable').removeClass('resizable');

      val1 = ui.position.left;
      val2 = ui.position.top;
    }

    helper.html(val1 + 'x' + val2);
    helper.show();
  };

  const hideHelper = (e) => {
    $(e).find('.helper').hide();
  };

  const context = (e, cb) => {
    const container = $(e).closest('[data-admin-form-doc-page-element-container]');
    const page = container.find('[data-admin-form-doc-page]');
    const name = container.data('admin-form-doc-page-element-container');
    const sorting = container.find('[data-admin-form-doc-page-sorting-items]');

    const url = container.data('admin-form-doc-page-element-storage-url');
    const key = container.data('admin-form-doc-page-element-storage-key');

    let itemIndex = -1;
    let item = null;

    if ($(e).data('admin-form-doc-page-item')) {
      item = $(e);

    } else if ($(e).closest('[data-admin-form-doc-page-item]').length) {
      item = $(e).closest('[data-admin-form-doc-page-item]');
    }

    if (item) {
      page.find('[data-admin-form-doc-page-item]').each((i, element) => {
        if ($(element).is(e)) {
          itemIndex = i;
        }
      });
    }

    cb && cb({page, name, url, key, container, item, itemIndex, sorting});
  };

  const initItems = () => {
    $('[data-admin-form-doc-page-item][data-resizable]').each((i, e) => {
      context(e, ({container}) => {
        const grid = parseInt(container.find('[data-admin-form-doc-toolbar-gutter]').val());

        try {
          $(e).resizable('destroy');
        } catch {
        }

        $(e).resizable({
          grid: grid,
          containment: $(e).parent(),
          handles: "n, e, s, w, ne, se, sw, nw, all",
          resize: (event, ui) => {
            updateHelper(e, 'resize', ui);
          },
          stop: () => {
            setItemDimensions($(e));
            hideHelper(e);
          }
        });
      });
    });

    $('[data-admin-form-doc-page-item][data-draggable]').each((i, e) => {
      context(e, ({container}) => {
        const grid = parseInt(container.find('[data-admin-form-doc-toolbar-gutter]').val());

        try {
          $(e).draggable('destroy');
        } catch {
        }

        $(e).draggable({
          grid: [grid, grid],
          containment: $(e).parent(),
          drag: (event, ui) => {
            updateHelper(e, 'drag', ui);
          },
          stop: () => {
            setItemDimensions($(e));
            hideHelper(e);
          }
        });
      });
    });


    $('[data-admin-form-doc-page]').each((i, e) => {
      context($(e), ({name, url, sorting, page}) => {

        let sortedItems = [...getPage(name).items || []];

        for (let i = 0; i < sortedItems.length; i++) {
          sortedItems[i].id = $(page.find('[data-admin-form-doc-page-item]')[i]).data('admin-form-doc-page-item');
        }

        sortedItems.sort((a, b) => a.deep < b.deep ? 1 : -1);
        const items = [];

        sortedItems.forEach((sortedItem) => {
          let template = null;

          if (sortedItem.type === 'file') {
            template = $('[data-admin-form-doc-sorting-layout-file-template]').html()
              .replaceAll('{{src}}', url + sortedItem.value.thumbnail);

          } else if (sortedItem.type === 'html') {
            template = $('[data-admin-form-doc-sorting-layout-html-template]').html()
              .replaceAll('{{color}}', sortedItem.value.color);

          } else if (sortedItem.type === 'embed') {
            template = $('[data-admin-form-doc-sorting-layout-embed-template]').html();
          }

          if (template) {
            items.push(template
              .replaceAll('{{id}}', sortedItem.id)
              .replaceAll('{{size}}', sortedItem.width + 'x' + sortedItem.height));
          }
        });

        sorting.html(items.join(''));

        if (items.length) {
          sorting.sortable({
            stop: (e, ui) => {
              ui.item.parent().find('[data-admin-form-doc-sorting-layout-item]').each((i, e) => {
                const id = $(e).data('admin-form-doc-sorting-layout-item');
                context('[data-admin-form-doc-page-item="' + id + '"]', ({name, itemIndex, item}) => {
                  const items = getPage(name).items;
                  items[itemIndex].deep = (sortedItems.length + 1) - i;

                  updatePage(name, {items});
                  item.css('z-index', items[itemIndex].deep);
                });
              });
            }
          });
        }
      });
    });
  };

  $(document).on('click', '[data-admin-form-doc-page-item]', (e) => moveElementTop($(e.currentTarget)));

  nav.listen('after', () => initItems());
  initItems();

  const addItem = (name, page, template, data) => {
    template = template.replaceAll('{{id}}', Math.random());
    page.append(template);

    const item = page.find('[data-admin-form-doc-page-item]').last();

    item.css('left', ((page.width() / 2) - (item.width() / 2)) + "px");
    item.css('top', ((page.height() / 2) - (item.height() / 2)) + "px");

    const items = getPage(name).items || [];
    items.push(data);

    updatePage(name, {items});
    setItemDimensions(item);

    moveElementTop(item);
    initItems();

    return item;
  }

  const updateItem = (name, index, data) => {
    const items = getPage(name).items || [];
    items[index] = {...items[index], ...data};
    updatePage(name, {items});
  };

  $(document).on('click', '[data-admin-form-doc-toolbar-set-background-image]', (e) => {
    const button = $(e.currentTarget);
    context(button, ({page, url, key, container, index, name}) => {
      modal.file(url, key, false, '/', (file) => {
        modal.hide();

        if (file.mime.search('video') !== -1) {
          page.css('background-image', "url('" + url + file.thumbnail + "')");
        } else {
          page.css('background-image', "url('" + url + file.src + "')");
        }

        page.removeClass('transparent');
        button.css('display', 'none');

        container.find('[data-admin-form-doc-toolbar-clear-background-image]').css('display', '');

        updatePage(name, {backgroundImage: file});
      });
    });
  });

  $(document).on('click', '[data-admin-form-doc-toolbar-clear-background-image]', (e) => {
    const button = $(e.currentTarget);
    context(button, ({page, container, name}) => {
      modal.question(locale('Remove background image?')).then(() => {
        button.hide();

        container.find('[data-admin-form-doc-toolbar-set-background-image]').show();
        page.css('background-image', "");

        if (!getPage(name).backgroundColor) {
          page.addClass('transparent');
        }

        updatePage(name, {backgroundImage: null});
      });
    });
  });

  $(document).on('click', '[data-admin-form-doc-toolbar-set-background-color]', (e) => {
    const button = $(e.currentTarget);
    context(button, ({page, container, name}) => {
      const colorInput = $('[data-admin-form-doc-background-color-form-template]').html();
      modal.html(locale('Select color'), colorInput).then(() => {
        $(modal.selector).find('[data-admin-form-doc-background-color-form-apply]').click(() => {

          const color = $(modal.selector).find('[data-admin-form-doc-background-color-form-input]').val();

          button.css('display', 'none');

          container.find('[data-admin-form-doc-toolbar-clear-background-color]').css('display', '');
          page.css('background-color', color);
          page.removeClass('transparent');

          updatePage(name, {backgroundColor: color});
          modal.hide();
        });
      });
    });
  });

  $(document).on('click', '[data-admin-form-doc-toolbar-clear-background-color]', (e) => {
    const button = $(e.currentTarget);
    context(button, ({page, container, name}) => {
      modal.question(locale('Remove background color?')).then(() => {
        button.css('display', 'none');
        container.find('[data-admin-form-doc-toolbar-set-background-color]').css('display', '');
        page.css('background-color', '');

        if (!getPage(name).backgroundImage) {
          page.addClass('transparent');
        }

        updatePage(name, {backgroundColor: null});
      });
    });
  });

  $(document).on('click', '[data-admin-form-doc-page-item-remove]', (e) => {
    const id = $(e.currentTarget).data('admin-form-doc-page-item-remove');
    const el = $('[data-admin-form-doc-page-item="' + id + '"]');

    context(el, ({name, item, itemIndex}) => {
      modal.question(locale('Remove item?')).then(() => {
        item.remove();
        const items = getPage(name).items || [];
        items.splice(itemIndex, 1);
        updatePage(name, {items});
        initItems();
      });
    });
  });

  const htmlForm = (name, data, cb) => {
    modal.html('Type text', $('[data-admin-form-doc-html-form-template]').html(), {size: 'large'})
      .then(() => {
        const form = $('[data-admin-form-doc-html-form]');

        if (data) {
          form.find('#background-color').val(data.color);
          form.find('textarea').val(data.html);
        }

        Tiny.ready(() => {
          new Tiny('[data-admin-form-doc-html-form-tiny]', {
            height: 600,
            resize: false,
          }, () => {
            if (data) {
              $(form.find('iframe')[0].contentDocument.getElementsByTagName('head'))
                .append('<style>body {background-color: ' + data.color + ';}</style>');
            }

            form.find('#background-color').on('input', (e) => {
              $(form.find('iframe')[0].contentDocument.getElementsByTagName('head'))
                .append('<style>body {background-color: ' + $(e.currentTarget).val() + ';}</style>');
            });

            form.find('[data-admin-form-doc-html-form-apply]').on('click', () => {
              const color = form.find('#background-color').val();
              const html = form.find('textarea').val();

              cb && cb({color, html});
            });
          });
        });
      });
  };

  const updateSizeLabel = (container) => {
    const width = container.find('[data-admin-form-doc-toolbar-size="width"]').val();
    const height = container.find('[data-admin-form-doc-toolbar-size="height"]').val();

    container.find('[data-admin-form-doc-toolbar-size-label]').html(`Size: ${width} x ${height}`);
  };

  $(document).on('click', '[data-admin-form-doc-toolbar-set-size]', (e) => {
    const size = $(e.currentTarget).data('admin-form-doc-toolbar-set-size');
    const width = parseInt(size.split('x')[0]);
    const height = parseInt(size.split('x')[1]);

    context(e.currentTarget, ({name, container, page}) => {
      container.find('[data-admin-form-doc-toolbar-size="width"]').val(width);
      container.find('[data-admin-form-doc-toolbar-size="height"]').val(height);
      page.css('width', width + 'px');
      page.css('height', height + 'px');
      updatePage(name, {width, height});
      updateSizeLabel(container);
    });
  });

  $(document).on('input', '[data-admin-form-doc-toolbar-size]', (e) => {
    const side = $(e.currentTarget).data('admin-form-doc-toolbar-size');
    const value = parseInt($(e.currentTarget).val());

    context(e.currentTarget, ({name, page, container}) => {
      page.css(side, value + 'px');
      const data = {};
      data[side] = value;
      updatePage(name, data);
      updateSizeLabel(container);
    });
  });

  $(document).on('click', '[data-admin-form-doc-toolbar-set-gutter]', (e) => {
    const gutter = parseInt($(e.currentTarget).data('admin-form-doc-toolbar-set-gutter'));
    context(e.currentTarget, ({name, container}) => {
      container.find('[data-admin-form-doc-toolbar-gutter]').val(gutter);
      container.find('[data-admin-form-doc-toolbar-gutter-label]').html(gutter);

      updatePage(name, {gutter});
      initItems();
    });
  });

  $(document).on('input', '[data-admin-form-doc-toolbar-gutter]', (e) => {
    const gutter = parseInt($(e.currentTarget).val());
    context(e.currentTarget, ({name, container}) => {
      container.find('[data-admin-form-doc-toolbar-gutter-label]').html(gutter);

      updatePage(name, {gutter});
      initItems();
    });
  });


  $(document).on('input', '[data-admin-form-doc-page-item-transparent]', (e) => {
    const val = $(e.currentTarget).val();
    const id = $(e.currentTarget).data('admin-form-doc-page-item-transparent');
    const selector = '[data-admin-form-doc-page-item="' + id + '"]';
    const label = '[data-admin-form-doc-page-item-transparent-label="' + id + '"]';

    context(selector, ({item, name, itemIndex}) => {
      item.css('opacity', val / 100);
      $(label).html(val);
      updateItem(name, itemIndex, {transparent: parseInt(val)});
    });
  });

  wait.on('[data-admin-form-doc-page-item-transparent]', (e) => {
    const id = $(e).data('admin-form-doc-page-item-transparent');
    const page = '[data-admin-form-doc-page-item="' + id + '"]';
    const label = '[data-admin-form-doc-page-item-transparent-label="' + id + '"]';
    const progressInput = e;

    context(page, ({name, itemIndex}) => {
      const transparent = getPage(name).items[itemIndex].transparent || 100;
      $(progressInput).val(transparent);
      $(label).html(transparent);
    });
  });

  $(document).on('click', '[data-admin-form-doc-toolbar-add-file]', (e) => {
    context(e.currentTarget, ({name, page, url, key}) => {
      modal.file(url, key, false, '/', (file) => {
        modal.hide();

        const template = $('[data-admin-form-doc-page-item-image-template]').html()
          .replaceAll('{{mime}}', url + file.mime)
          .replaceAll('{{thumbnail}}', url + file.thumbnail)
          .replaceAll('{{src}}', url + file.src);

        const item = addItem(name, page, template, {
          type: 'file',
          value: file
        });

        if (file.mime.includes('video')) {
          item.append('<i class="fas fa-play text-primary fa-4x shadow-1 opacity-80"></i>');
        }
      });
    });
  });

  $(document).on('click', '[data-admin-form-doc-toolbar-add-embed]', (e) => {
    context(e.currentTarget, ({page, name}) => {
      modal.prompt(locale('Type embed URL'), 'URL').then((src) => {
        const template = $('[data-admin-form-doc-page-item-embed-template]').html()
          .replaceAll('{{src}}', src);

        addItem(name, page, template, {
          type: 'embed',
          value: src
        });
      });
    });
  });

  $(document).on('click', '[data-admin-form-doc-toolbar-add-html]', (e) => {
    context(e.currentTarget, ({page, name}) => {
      htmlForm(name, null, ({color, html}) => {

        const template = $('[data-admin-form-doc-page-item-html-template]').html()
          .replaceAll('{{color}}', color)
          .replaceAll('{{html}}', html);

        addItem(name, page, template, {
          type: 'html',
          value: {html, color}
        });

        modal.hide();
      });
    });
  });

  $(document).on('click', '[data-admin-form-doc-page-item-edit]', (event) => {
    editHtml('[data-admin-form-doc-page-item="' + $(event.currentTarget).data('admin-form-doc-page-item-edit') + '"]');
  });

  $(document).on('dblclick', '[data-admin-form-doc-page-item-edit-dblclick]', (event) => {
    editHtml('[data-admin-form-doc-page-item="' + $(event.currentTarget).data('admin-form-doc-page-item-edit-dblclick') + '"]');
  });

  const editHtml = (e) => {
    context(e, ({name, itemIndex}) => {

      const content = $(e).find('[data-admin-form-doc-page-item-html-content]');

      htmlForm(name, getPage(name).items[itemIndex].value, ({html, color}) => {
        content.html(html);
        content.css('background-color', color);

        updateItem(name, itemIndex, {
          value: {html, color}
        });

        modal.hide();
      });
    });
  };
});