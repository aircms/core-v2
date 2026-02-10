const nav = new class {
  layoutSelector = '[data-admin-layout-main]';

  url = null;

  interval = null;

  history = [];

  events = {
    'before': [],
    'redirect': [],
    'after': []
  };

  constructor() {
    this.listen('after', (page) => {
      $(this.layoutSelector).html(page.responseData);
      $(window).scrollTop(0);
      if (!page.url.startsWith('/_storage')) {
        loader.hide();
      }
    });

    this.listen('before', () => {
      modal.hide();
      loader.show();
      side.hide();
    });

    const _nav = (href, force, isBlank) => {
      if (isBlank) {
        window.open(href);
        return true;
      }

      if (force) {
        location.href = href;
        return true;
      }

      if (href.startsWith('#') || href.startsWith('tel:') || href.startsWith('mailto:') || href.startsWith('javascript')) {
        return true;
      }

      if (href.length) {
        this.nav(href);
        return false;
      }
    }

    $(document).on('click', '[href]', (event) => {
      $('[data-admin-contextmenu-target]').remove();

      const href = $(event.currentTarget).attr('href');
      const confirm = $(event.currentTarget).data('confirm');
      const force = $(event.currentTarget).data('force') || $(event.currentTarget).attr('target') === '_blank';
      const isBlank = $(event.currentTarget).attr('target') === '_blank';

      $(event.currentTarget).blur();

      if (confirm) {
        modal.question(confirm).then(() => _nav(href, force, isBlank));
        return false;
      }
      return _nav(href, force, isBlank);
    });
    this.start();
  }

  start() {
    this.url = this.getCurrentUrl();
    this.interval = setInterval(() => this.handler(), 10);
  }

  listen(event, handler) {
    this.events[event].push(handler);
    return this.events[event].length - 1;
  }

  _call(event, request) {
    this.events[event].forEach((callback) => {
      callback(request);
    });
  }

  stop() {
    clearInterval(this.interval);
  }

  handler() {
    let currentUrl = this.getCurrentUrl();

    if (currentUrl !== this.url) {

      this.url = currentUrl;
      this.history.push(this.url);

      this.load(this.url);
    }
  }

  load(url, callback) {
    this.request(url, callback);
  }

  reload(callback) {
    this.request(this.url, callback);
  }

  nav(url, callback) {
    this._setUrl(url);
    this.load(this.url, callback);
  }

  back() {
    if (this.history.length > 1) {
      this.history.splice(this.history.length - 1);
      let url = this.history[this.history.length - 1];
      this.history.splice(this.history.length - 1);

      this.nav(url);
      return;
    }

    this.nav('/' + location.pathname.split('/')[1]);
  }


  get(url, callback) {
    this.request(url, callback);
  }

  post(url, data, callback) {
    this._setUrl(url);
    this.request(url, callback, data, 'post');
  }

  request(url, callback, requestData, method) {
    let xmlHttpRequest = this._getXmlHttpRequest();
    xmlHttpRequest.open(method || "GET", url, true);
    xmlHttpRequest.setRequestHeader(
      'X-Requested-With',
      'XMLHttpRequest'
    );

    if (method === 'post') {
      xmlHttpRequest.setRequestHeader(
        'Content-Type',
        'application/x-www-form-urlencoded'
      );
      xmlHttpRequest.setRequestHeader(
        'X-Redirect',
        this.getReferrer()
      );
    }

    let callbackData = {
      'url': url,
      'requestData': requestData,
      'method': method,
      'xmlHttpRequest': xmlHttpRequest
    };

    this._call('before', callbackData);

    xmlHttpRequest.onprogress = () => {
      if (xmlHttpRequest.responseURL && !this._isEqualUrls(url, xmlHttpRequest.responseURL)) {
        this._setUrl(xmlHttpRequest.responseURL);
        this._call('redirect', callbackData);
      }
    };

    xmlHttpRequest.onload = () => {
      callbackData.responseData = xmlHttpRequest.responseText;
      this._call('after', callbackData);

      if (callback) {
        callback(xmlHttpRequest.responseText);
      }
    };
    xmlHttpRequest.send(requestData);
  }

  _setUrl(url) {
    this.stop();
    url = url.replace(location.protocol + '//' + location.hostname, '');
    this.url = url;
    history.pushState({}, null, url);
    if (this.history[this.history.length - 1] !== url) {
      this.history.push(url);
    }
    this.start();
  }

  _getXmlHttpRequest() {
    if (typeof XMLHttpRequest !== 'undefined') {
      return new XMLHttpRequest();
    }
    try {
      return new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {

      try {
        return new ActiveXObject("Microsoft.XMLHTTP");
      } catch (ee) {
        return false;
      }
    }
  }

  _isEqualUrls(requested, response) {
    let index = 0;

    if (response.indexOf('?_=') !== -1) {
      index = response.indexOf('?_=');
    } else if (response.indexOf('&_=') !== -1) {
      index = response.indexOf('&_=');
    }
    response = response.replace(location.protocol + '//' + location.host, '');
    if (index) {
      response = response.slice(0, -18);
    }
    return requested === response;
  }

  getCurrentUrl() {
    let url = location.pathname;
    if (location.search.length) {
      url = url + location.search;
    }
    return url;
  }

  getReferrer() {
    let referrer = '/' + this.url.split('/')[1];

    if (this.history[this.history.length - 2]) {
      referrer = this.history[this.history.length - 2];
    }
    if (referrer.indexOf('/manage/')) {
      referrer = '/' + referrer.split('/')[1];
    }
    return referrer;
  }

  getQueryParams() {
    let params = {};
    if (location.search.length) {
      location.search.substr(1).split('&').forEach((param) => {
        params[param.split('=')[0]] = param.split('=')[1];
      });
    }
    return params;
  }

  getQueryParamsFromString(query) {
    let params = {};
    if (query.length) {
      query.split('?')[1].split('&').forEach((param) => {
        params[param.split('=')[0]] = param.split('=')[1];
      });
    }
    return params;
  }

  getQueryStringFrom(obj) {
    let query = "";
    Object.keys(obj).forEach((key, index) => {
      if (index === 0) {
        query += key + '=' + obj[key];
      } else {
        query += '&' + key + '=' + obj[key];
      }
    });
    if (query.length) {
      query = "?" + query;
    }
    return query;
  }
};