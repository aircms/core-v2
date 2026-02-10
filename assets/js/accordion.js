class Accordion {
  static accordionSelector = 'data-admin-accordion';
  static accordionNavigationSelector = 'data-admin-accordion-nav';
  static accordionNavigationIconSelector = 'data-admin-accordion-nav-icon';
  static accordionContentSelector = 'data-admin-accordion-content';

  options = {
    active: -1,
    activeNavItemClass: 'bg-primary-subtle',
    nonActiveItemClass: 'bg-body-secondary',
    closeOthers: false,
  };

  accordion;

  heights = [];

  constructor(element, options = {}) {
    this.accordion = $(element);

    if (!this.accordion.length) {
      throw new Error(`Element not found: ${element}`);
    }

    if (!this.accordion.find(`[${Accordion.accordionNavigationSelector}]`).length) {
      throw new Error(`Accordion navigation not found for: ${element}`);
    }

    if (!this.accordion.find(`[${Accordion.accordionContentSelector}]`).length) {
      throw new Error(`Accordion content not found for: ${element}`);
    }

    if (this.accordion.attr(`${Accordion.accordionSelector}-initialized`)) {
      return;
    }

    this.accordion.attr(`${Accordion.accordionSelector}-initialized`, 'true');

    this.options = {...this.options, ...options};

    this.accordion.find(`[${Accordion.accordionContentSelector}]`).each((i, content) => {
      this.heights.push(content.offsetHeight);
    });

    this.accordion.find(`[${Accordion.accordionNavigationSelector}]`).click((event) => {
      this.active($(event.currentTarget).parent().index());
    });

    this.active(this.options.active);
  }

  active(index) {
    if (index === undefined) {
      return this.options.active;
    }

    const accordionContent = this.accordion.find(`[${Accordion.accordionContentSelector}]`);
    const accordionNavigation = this.accordion.find(`[${Accordion.accordionNavigationSelector}]`);

    accordionNavigation.each((i, nav) => {
      const accordionNavIcon = $(nav).find(`[${Accordion.accordionNavigationIconSelector}]`);

      if (i === index) {
        if ($(nav).hasClass(this.options.activeNavItemClass)) {
          $(nav).removeClass(this.options.activeNavItemClass).addClass(this.options.nonActiveItemClass);
          accordionNavIcon.removeClass('rotate-90');
        } else {
          $(nav).addClass(this.options.activeNavItemClass).removeClass(this.options.nonActiveItemClass);
          accordionNavIcon.addClass('rotate-90');
        }
      } else if (this.options.closeOthers || index === -1) {
        $(nav).removeClass(this.options.activeNavItemClass).addClass(this.options.nonActiveItemClass);
        accordionNavIcon.removeClass('rotate-90');
      }
    });

    accordionContent.each((i, content) => {
      if (i === index) {
        if ($(content).css('height') !== '0px' && !$(accordionNavigation[i]).hasClass(this.options.activeNavItemClass)) {
          $(content).css('height', `0px`);
        } else {
          $(content).css('height', `${this.heights[i]}px`);
        }
      } else if (this.options.closeOthers || index === -1) {
        $(content).css('height', `0px`);
      }
    });
  }
}

$(document).ready(() => {
  wait.on('[data-admin-accordion]', (accordion) => new Accordion(accordion));
});