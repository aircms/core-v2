class Tab {
  static tabSelector = 'data-admin-tab';
  static tabNavigationSelector = 'data-admin-tab-nav';
  static tabContentSelector = 'data-admin-tab-content';

  options = {
    active: 0,
    activeNavItemClass: 'bg-primary-subtle',
    nonActiveItemClass: 'bg-body-secondary',
    change: null
  };

  tab;

  constructor(element, options = {}) {
    this.tab = $(element);

    if (!this.tab.length) {
      throw new Error(`Element not found: ${element}`);
    }

    if (!this.tab.find(`[${Tab.tabNavigationSelector}]`).length) {
      throw new Error(`Tab navigation not found for: ${element}`);
    }

    if (!this.tab.find(`[${Tab.tabContentSelector}]`).length) {
      throw new Error(`Tab content not found for: ${element}`);
    }

    if (this.tab.attr(`${Tab.tabSelector}-initialized`)) {
      return;
    }

    this.tab.attr(`${Tab.tabSelector}-initialized`, 'true');

    this.options = {...this.options, ...options};

    this.tab.find(`[${Tab.tabNavigationSelector}] > *`).click((event) => {
      this.active($(event.currentTarget).index());
    });
    this.active(this.options.active, true);
  }

  active(index, withOutEvent = false) {
    if (index === undefined) {
      return this.options.active;
    }
    const tabContent = this.tab.find(`[${Tab.tabContentSelector}] > *`);
    const tabNavigation = this.tab.find(`[${Tab.tabNavigationSelector}] > *`);

    if (!$(tabContent[index]).length) {
      index = 0;
    }

    tabContent.addClass('d-none');
    $(tabContent[index]).removeClass('d-none');

    tabNavigation.removeClass(this.options.activeNavItemClass).addClass(this.options.nonActiveItemClass);
    $(tabNavigation[index]).removeClass(this.options.nonActiveItemClass).addClass(this.options.activeNavItemClass);

    if (this.options.change && !withOutEvent) {
      this.options.change(index);
    }
  }
}