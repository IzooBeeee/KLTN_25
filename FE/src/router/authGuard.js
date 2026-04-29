// src/router/authGuard.js
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ 
  position: "top-right",
  duration: 3000,
  queue: true 
});

export default function authGuard(to, from, next) {
  const token = localStorage.getItem("auth_token");
  const userType = localStorage.getItem("user_type"); // 'admin' | 'moi-gioi' | 'khach-hang'

  // ===== 1. GUEST ROUTES (Login/Register pages) =====
  if (to.meta.guest) {
    if (token && userType) {
      // ✅ Đã login → redirect về home đúng role
      return next({ path: getHomePath(userType) });
    }
    return next(); // ✅ Chưa login → cho vào trang login
  }

  // ===== 2. PUBLIC ROUTES (Không cần auth) =====
  const publicPaths = ["/", "/khach-hang/trang-chu", "/gioi-thieu", "/lien-he"];
  if (publicPaths.includes(to.path) || to.meta.public) {
    return next();
  }

  // ===== 3. CHƯA LOGIN → Redirect to login =====
  if (!token || !userType) {
    const loginPath = getLoginPath(to.path);
    // ✅ Vue Router 4: dùng object { path } thay vì string
    return next({ path: loginPath, query: { redirect: to.fullPath } });
  }

  // ===== 4. CHECK ROLE PERMISSION =====
 // ===== 4. CHECK ROLE PERMISSION =====
if (to.meta.roles) {
  const allowedRoles = Array.isArray(to.meta.roles) 
    ? to.meta.roles 
    : [to.meta.roles];
  
  if (!allowedRoles.includes(userType)) {
    // ✅ Check toaster tồn tại trước khi gọi
    if (typeof toaster !== 'undefined' && toaster.error) {
      toaster.error("❌ Bạn không có quyền truy cập trang này");
    }
    return next({ path: getHomePath(userType) });
  }
}
  // ===== 5. OPTIONAL: Check token expiry (nếu cần) =====
  // if (isTokenExpired(token)) {
  //   clearAuth();
  //   return next({ path: getLoginPath(to.path) });
  // }

  // ✅ All checks passed
  next();
}

// ===== HELPER FUNCTIONS =====

function getLoginPath(path) {
  if (!path) return "/khach-hang/dang-nhap";
  if (path.startsWith("/admin")) return "/admin/dang-nhap";
  if (path.startsWith("/moi-gioi")) return "/moi-gioi/dang-nhap";
  return "/khach-hang/dang-nhap";
}

function getHomePath(role) {
  const map = {
    admin: "/admin/dashboard",
    "moi-gioi": "/moi-gioi/trang-chu",
    "khach-hang": "/khach-hang/trang-chu",
  };
  return map[role] || "/";
}

// ✅ Utility: Clear auth data (dùng khi logout hoặc token expired)
export function clearAuth() {
  localStorage.removeItem("auth_token");
  localStorage.removeItem("user_type");
  localStorage.removeItem("user_info");
}

// ✅ Utility: Check if token is expired (optional)
export function isTokenExpired(token) {
  if (!token) return true;
  try {
    // Nếu token là JWT, decode payload để check exp
    const payload = JSON.parse(atob(token.split(".")[1]));
    return payload.exp * 1000 < Date.now();
  } catch {
    return false; // Không phải JWT → skip check
  }
}