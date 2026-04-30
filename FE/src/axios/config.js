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
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      console.error('🔴 [Axios] 401 Unauthorized → Clearing auth for current role...');

      const role = getRoleFromPath(window.location.pathname);
      clearAuth(role);

      // Redirect về trang đăng nhập đúng role (chỉ redirect nếu chưa ở trang login)
      const path = window.location.pathname;
      const isOnLoginPage = path.includes('/dang-nhap');

      if (!isOnLoginPage) {
        let loginPath = '/khach-hang/dang-nhap';
        if (path.startsWith('/admin')) loginPath = '/admin/dang-nhap';
        else if (path.startsWith('/moi-gioi')) loginPath = '/moi-gioi/dang-nhap';
        window.location.href = loginPath;
      }
    }
    return Promise.reject(error);
  }
);

export default api;