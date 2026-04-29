import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
});

// ✅ Request interceptor
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
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
      console.error("🔴 [Axios] 401 Unauthorized → Clearing auth...");
      
      // ✅ XÓA TOKEN
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_type');
      localStorage.removeItem('user_info');
      
      // ✅ REDIRECT về trang đăng nhập (chỉ redirect nếu chưa ở trang login)
      if (!window.location.pathname.includes('/login') && !window.location.pathname.includes('/dang-nhap')) {
        window.location.href = '/dang-nhap';
      }
    }
    return Promise.reject(error);
  }
);

export default api;