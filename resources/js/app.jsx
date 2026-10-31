import React from 'react';
import {createRoot} from 'react-dom/client';
import Store from './components/Store';
import Admin from './components/Admin';
import {products} from './lib/catalog.mjs';
import '../css/app.css';

const originalFetch = window.fetch.bind(window);
window.fetch = async (input, init = {}) => {
  const url = typeof input === 'string' ? input : input.url;
  const sameOrigin = new URL(url, location.href).origin === location.origin;
  if (sameOrigin) {
    const headers = new Headers(init.headers);
    headers.set('Accept', 'application/json');
    headers.set('X-Requested-With', 'XMLHttpRequest');
    const cookie = document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN='));
    // Read the current cookie on every request: login/logout in another tab can rotate it.
    if (cookie) headers.set('X-XSRF-TOKEN', decodeURIComponent(cookie.slice('XSRF-TOKEN='.length)));
    else headers.set('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
    init = {...init, headers};
  }
  const response = await originalFetch(input, init);
  if (sameOrigin && /\/api\/auth\/(?:login|logout)$/.test(url) && response.ok) {
    const data = await response.clone().json();
    if (data.csrf_token) document.querySelector('meta[name="csrf-token"]').content = data.csrf_token;
  }
  return response;
};
createRoot(document.getElementById('app')).render(location.pathname === '/admin' ? <Admin/> : <Store initialProducts={products}/>);
