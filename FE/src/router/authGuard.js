// src/router/authGuard.js
// ⚠️ File này hiện ít được dùng (router/index.js đã có guard riêng)
// Giữ lại để backward compatibility

import { createToaster } from "@meforma/vue-toaster";
import { getToken, getRoleFromPath, clearAuth } from "@/js/auth";

const toaster = createToaster({ 
  position: "top-right",
  duration: 3000,
  queue: true 
});

export default function authGuard(to, from, next) {
  // ✅ Đọc token theo role của path đang truy cập
  const role = getRoleFromPath(to.path);
  const token = getToken(role);

  // ===== 1. GUEST ROUTES (Login/Register pages) =====
  if (to.meta.guest) {
    if (token) {
      return next({ path: getHomePath(role) });
    }
    return next();
  }

  // ===== 2. PUBLIC ROUTES (Không cần auth) =====
  const publicPaths = ["/", "/khach-hang/trang-chu", "/gioi-thieu", "/lien-he"];
  if (publicPaths.includes(to.path) || to.meta.public) {
    return next();
  }

  // ===== 3. CHƯA LOGIN → Redirect to login =====
  if (!token) {
    const loginPath = getLoginPath(to.path);
    return next({ path: loginPath, query: { redirect: to.fullPath } });
  }

  // ===== 4. CHECK ROLE PERMISSION =====
  if (to.meta.roles) {
    const allowedRoles = Array.isArray(to.meta.roles) 
      ? to.meta.roles 
      : [to.meta.roles];
    
    if (!allowedRoles.includes(role)) {
      if (typeof toaster !== 'undefined' && toaster.error) {
        toaster.error("❌ Bạn không có quyền truy cập trang này");
      }
      return next({ path: getHomePath(role) });
    }
  }

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

// ✅ Utility: Clear auth data — dùng role-specific key
export { clearAuth };

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