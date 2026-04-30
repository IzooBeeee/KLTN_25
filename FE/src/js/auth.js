/**
 * auth.js — Quản lý xác thực riêng biệt cho từng role
 *
 * Mỗi role dùng localStorage key riêng:
 *   - admin:      admin_auth_token, admin_user_info
 *   - moi-gioi:   moi_gioi_auth_token, moi_gioi_user_info
 *   - khach-hang: khach_hang_auth_token, khach_hang_user_info
 *
 * Điều này cho phép 3 tab cùng lúc (admin, môi giới, khách hàng)
 * trên cùng 1 trình duyệt mà không ảnh hưởng lẫn nhau.
 */

// ===== CONFIG: key mapping theo role =====
const ROLE_KEYS = {
  admin: {
    token: 'admin_auth_token',
    userInfo: 'admin_user_info',
  },
  'moi-gioi': {
    token: 'moi_gioi_auth_token',
    userInfo: 'moi_gioi_user_info',
  },
  'khach-hang': {
    token: 'khach_hang_auth_token',
    userInfo: 'khach_hang_user_info',
  },
};

/**
 * Lấy config keys cho role
 */
function getKeys(role) {
  return ROLE_KEYS[role] || ROLE_KEYS['khach-hang'];
}

/**
 * Đoán role từ URL path hiện tại
 */
export function getRoleFromPath(path = window.location.pathname) {
  if (path.startsWith('/admin')) return 'admin';
  if (path.startsWith('/moi-gioi')) return 'moi-gioi';
  return 'khach-hang';
}

// ===== GET =====

/**
 * Lấy token của một role cụ thể
 */
export function getToken(role) {
  return localStorage.getItem(getKeys(role).token) || null;
}

/**
 * Lấy token của role hiện tại (đoán từ URL)
 */
export function getCurrentToken() {
  return getToken(getRoleFromPath());
}

/**
 * Lấy user info của một role cụ thể
 */
export function getUserInfo(role) {
  try {
    const raw = localStorage.getItem(getKeys(role).userInfo);
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

/**
 * Kiểm tra role có đang đăng nhập không
 */
export function isLoggedIn(role) {
  return !!getToken(role);
}

// ===== SET =====

/**
 * Lưu thông tin đăng nhập cho một role
 */
export function setAuth(role, token, userInfo = null) {
  const keys = getKeys(role);
  localStorage.setItem(keys.token, token);
  if (userInfo) {
    localStorage.setItem(keys.userInfo, JSON.stringify(userInfo));
  }
}

// ===== CLEAR =====

/**
 * Xóa thông tin đăng nhập của một role
 */
export function clearAuth(role) {
  const keys = getKeys(role);
  localStorage.removeItem(keys.token);
  localStorage.removeItem(keys.userInfo);
}

/**
 * Xóa tất cả auth của mọi role
 */
export function clearAllAuth() {
  Object.values(ROLE_KEYS).forEach(({ token, userInfo }) => {
    localStorage.removeItem(token);
    localStorage.removeItem(userInfo);
  });
}

// ===== BACKWARD COMPATIBILITY =====
// Giữ lại để các component cũ không bị break ngay lập tức
// (Những component này vẫn đọc 'auth_token' cũ → sẽ dần migrate)

/**
 * @deprecated Dùng getToken(role) thay thế
 */
export function getLegacyToken() {
  return localStorage.getItem('auth_token') || null;
}
