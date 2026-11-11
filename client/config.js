// Клиент и API — разные HTTPS-домены OSPanel; loopback используется только для тестов.
window.ICC_CONFIG = { apiUrl: location.hostname === 'icc.local' ? location.protocol + '//api.icc.local/api' : location.protocol === 'https:' ? 'https://localhost:8443/api' : 'http://127.0.0.1:8000/api' };
