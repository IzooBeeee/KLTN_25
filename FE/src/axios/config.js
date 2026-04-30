import axios from 'axios';
import { getToken, getRoleFromPath, clearAuth } from '@/js/auth';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
});

// ✅ Request interceptor — đính token đúng role theo URL hiện tại
api.interceptors.request.use(
  config => {
    // Ưu tiên: đọc role từ URL path hiện tại
    const role = getRoleFromPath(window.location.pathname);
    const token = getToken(role);

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    } else {
      // Xóa header nếu không có token (tránh dùng token cũ từ role khác)
      delete config.headers.Authorization;
    }

    return config;
  },
  error => Promise.reject(error)
);

// ✅ Response interceptor: xử lý 401
// Dùng flag để tránh redirect nhiều lần liên tiếp
let _isRedirecting = false;

api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401 && !_isRedirecting) {
      // ✅ Đọc role từ URL của API request (không phải window.location)
      // Ví dụ: /api/khach-hang/... → role "khach-hang"
      //         /api/admin/...      → role "admin"
      //         /api/moi-gioi/...   → role "moi-gioi"
      const requestUrl = error.config?.url || '';
      let roleFromRequest = 'khach-hang';
      if (requestUrl.includes('/admin/')) roleFromRequest = 'admin';
      else if (requestUrl.includes('/moi-gioi/')) roleFromRequest = 'moi-gioi';

      const tokenBefore = getToken(roleFromRequest);

      // Chỉ redirect nếu role đó đang có token (token hết hạn/không hợp lệ)
      if (tokenBefore) {
        console.error('🔴 [Axios] 401 Unauthorized → Clearing auth for role:', roleFromRequest);
        clearAuth(roleFromRequest);

        const currentPath = window.location.pathname;
        const isOnLoginPage = currentPath.includes('/dang-nhap');

        // Không redirect nếu đang ở trang login (tránh vòng lặp)
        if (!isOnLoginPage) {
          _isRedirecting = true;
          let loginPath = '/khach-hang/dang-nhap';
          if (roleFromRequest === 'admin') loginPath = '/admin/dang-nhap';
          else if (roleFromRequest === 'moi-gioi') loginPath = '/moi-gioi/dang-nhap';

          import('@/router').then(({ default: router }) => {
            router.push(loginPath);
            setTimeout(() => { _isRedirecting = false; }, 2000);
          });
        }
      }
    }
    return Promise.reject(error);
  }
);

export default api;