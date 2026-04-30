import axios from 'axios';
import { getToken, clearAuth } from '@/js/auth';

/**
 * Xác định role từ URL của API request (không phải URL của trình duyệt).
 * Đây là cách duy nhất chính xác 100% trong môi trường đa tab.
 * window.location.pathname chỉ trả về URL của TAB ĐANG FOCUS, gây sai role.
 */
function getRoleFromApiUrl(url = '') {
  if (url.includes('/admin/') || url.startsWith('admin/') || url === '/admin/check-token' || url.startsWith('/admin')) {
    return 'admin';
  }
  if (url.includes('/moi-gioi/') || url.startsWith('moi-gioi/') || url.startsWith('/moi-gioi')) {
    return 'moi-gioi';
  }
  return 'khach-hang';
}

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
});

// ✅ Request interceptor — đọc role từ API URL (chính xác dù có nhiều tab)
api.interceptors.request.use(
  config => {
    // Xác định role từ URL của API request (VD: /admin/check-token → admin)
    const role = getRoleFromApiUrl(config.url || '');
    const token = getToken(role);

    // Lưu role vào config để response interceptor dùng lại (không cần đoán lần 2)
    config._role = role;

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    } else {
      delete config.headers.Authorization;
    }

    return config;
  },
  error => Promise.reject(error)
);

// ✅ Response interceptor: xử lý 401 — dùng đúng role của request đó
// Flag ngăn redirect liên tiếp (debounce)
let _redirectTimer = null;

api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Role đã được xác định chính xác từ request interceptor
      const role = error.config?._role || getRoleFromApiUrl(error.config?.url || '');
      const tokenBefore = getToken(role);

      // Chỉ xử lý nếu role đó thực sự có token (tránh xử lý thừa)
      if (tokenBefore) {
        console.warn(`⚠️ [Axios] 401 for role "${role}" → clearing token`);
        clearAuth(role);

        // Không redirect nếu đang ở trang login
        const currentPath = window.location.pathname;
        const isLoginPage = currentPath.includes('/dang-nhap');

        if (!isLoginPage && !_redirectTimer) {
          const loginPaths = {
            'admin': '/admin/dang-nhap',
            'moi-gioi': '/moi-gioi/dang-nhap',
            'khach-hang': '/khach-hang/dang-nhap',
          };
          const loginPath = loginPaths[role] || '/khach-hang/dang-nhap';

          // Debounce 1s để tránh redirect liên tiếp khi nhiều request thất bại cùng lúc
          _redirectTimer = setTimeout(() => {
            _redirectTimer = null;
            import('@/router').then(({ default: router }) => {
              // Chỉ redirect nếu tab này đang ở đúng role bị lỗi
              const tabRole = window.location.pathname.startsWith('/admin')
                ? 'admin'
                : window.location.pathname.startsWith('/moi-gioi')
                  ? 'moi-gioi'
                  : 'khach-hang';

              if (tabRole === role) {
                router.push(loginPath);
              }
            });
          }, 1000);
        }
      }
    }
    return Promise.reject(error);
  }
);

export default api;
