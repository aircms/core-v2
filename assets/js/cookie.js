const crypt = new class {
  enc(value) {
    return btoa(encodeURIComponent(JSON.stringify(value)));
  }

  dec(value) {
    return JSON.parse(decodeURIComponent(atob(value)));
  }
}

const cookie = new class {
  get(key) {

    const cookies = {};

    document.cookie.split(';').map((item) => {
      const cookie = item.split('=');
      cookies[cookie[0].trim()] = cookie[1].trim();
    });

    if (cookies[key]) {
      return crypt.dec(cookies[key]);
    }
    return null;
  }

  set(key, value, lifetime = null, options = []) {
    const _cookie = [`${key}=${crypt.enc(value)}`, `domain=${location.hostname}`, `path: '/'`, ...options];
    if (lifetime) {
      _cookie.push(`expires=${(new Date(Date.now() + (lifetime * 1000))).toUTCString()}`);
    }
    document.cookie = _cookie.join('; ');
  }

  remove(key) {
    this.set(key, null, -1);
  }
}