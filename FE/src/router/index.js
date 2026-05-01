import { createRouter, createWebHistory } from "vue-router";

const routes = [
  // ===== ADMIN =====
  {
    path: "/admin/dang-nhap",
    component: () => import("../components/Admin/DangNhap/index.vue"),
    name: "AdminDangNhap",
    meta: { layout: "blank", guest: true }
  },
  {
    path: "/admin/quen-mat-khau",
    component: () => import("../components/Admin/QuenMatKhau/index.vue"),
    name: "AdminQuenMatKhau",
    meta: { layout: "blank", guest: true }
  },
  {
    path: "/admin/xac-thuc-ma",
    component: () => import("../components/Admin/XacThucMaOTP/index.vue"),
    name: "AdminXacThucMa",
    meta: { layout: "blank", guest: true }
  },
  {
    path: "/admin/dat-lai-mat-khau",
    component: () => import("../components/Admin/DatLaiMatKhau/index.vue"),
    name: "AdminDatLaiMatKhau",
    meta: { layout: "blank", guest: true }
  },
  {
    path: "/admin",
    redirect: "/admin/dashboard"
  },
  {
    path: "/admin/dashboard",
    component: () => import("../components/Admin/Dashboard/index.vue"),
    meta: { layout: "admin", roles: ["admin"] }
  },
  {
    path: "/admin/moi-gioi",
    component: () => import("../components/Admin/QuanLyNguoiDung/MoiGioi/index.vue"),
    meta: { layout: "admin", roles: ["admin"] }
  },
  {
    path: "/admin/khach-hang",
    component: () => import("../components/Admin/QuanLyNguoiDung/KhachHang/index.vue"),
    meta: { layout: "admin", roles: ["admin"] }
  },
  {
    path: "/admin/bat-dong-san",
    component: () => import("../components/Admin/QuanLyBDS/index.vue"),
    meta: { layout: "admin", roles: ["admin"] }
  },
  {
    path: "/admin/goi-tin",
    component: () => import("../components/Admin/GoiTin/DanhSachGoi/index.vue"),
    meta: { layout: "admin", roles: ["admin"] }
  },
  {
    path: "/admin/lich-su-mua-goi",
    component: () => import("../components/Admin/GoiTin/LichSuMuaGoi/index.vue"),
    meta: { layout: "admin", roles: ["admin"] }
  },
  {
    path: "/admin/loai-bat-dong-san",
    component: () => import("../components/Admin/LoaiBatDongSan/index.vue"),
    meta: { layout: "admin", roles: ["admin"] }
  },
  {
    path: "/admin/phan-quyen",
    component: () => import("../components/Admin/PhanQuyen/index.vue"),
    meta: { layout: "admin", roles: ["admin"] }
  },
  {
    path: "/admin/ho-so-ca-nhan",
    component: () => import("../components/Admin/Profile/index.vue"),
    meta: { layout: "admin", roles: ["admin"] }
  },

  // ===== KHÁCH HÀNG =====
  {
    path: "/",
    component: () => import("../components/KhachHang/TrangChu/index.vue"),
    name: "KhachHangTrangChu",
    meta: { layout: "khach-hang" }
  },
  {
    path: "/khach-hang/dang-nhap",
    component: () => import("../components/KhachHang/DangNhap/index.vue"),
    name: "KhachHangDangNhap",
    meta: { layout: "blank", guest: true }
  },
  {
    path: "/khach-hang/dang-ky",
    component: () => import("../components/KhachHang/DangKy/index.vue"),
    name: "KhachHangDangKy",
    meta: { layout: "blank", guest: true }
  },
  {
    path: '/khach-hang/quen-mat-khau',
    name: 'QuenMatKhau',
    component: () => import('../components/KhachHang/QuenMatKhau/index.vue'),
    meta: { layout: "blank", guest: true }
  },
  {
    path: '/khach-hang/xac-thuc-ma',
    name: 'XacThucMa',
    component: () => import('../components/KhachHang/XacThucMaOTP/index.vue'),
    meta: { layout: "blank" }
  },
  {
    path: '/khach-hang/dat-lai-mat-khau',
    name: 'DatLaiMatKhau',
    component: () => import('../components/KhachHang/DatLaiMatKhau/index.vue'),
    meta: { layout: "blank" }
  },
  {
    path: "/khach-hang/trang-chu",
    component: () => import("../components/KhachHang/TrangChu/index.vue"),
    name: "KhachHangTrangChuFull",
    meta: { layout: "khach-hang", roles: ["khach-hang", "moi-gioi"] }
  },
  {
    path: "/khach-hang/danh-sach-bat-dong-san",
    component: () => import("../components/KhachHang/DanhSachBatDongSan/index.vue"),
    name: "KhachHangDanhSachBatDongSan",
    meta: { layout: "khach-hang" }
  },
  {
    path: "/khach-hang/chi-tiet-bat-dong-san/:id",
    component: () => import("../components/KhachHang/ChiTietBatDongSan/index.vue"),
    name: "KhachHangChiTietBatDongSan",
    meta: { layout: "khach-hang" }
  },
  {
    path: "/khach-hang/profile",
    component: () => import("../components/KhachHang/Profile/index.vue"),
    name: "KhachHangProfile",
    meta: { layout: "khach-hang" }
  },
  {
    path: "/khach-hang/lien-he",
    component: () => import("../components/KhachHang/LienHe/index.vue"),
    name: "KhachHangLienHe",
    meta: { layout: "khach-hang" }
  },
  {
    path: "/khach-hang/yeu-thich",
    component: () => import("../components/KhachHang/YeuThich/index.vue"),
    name: "KhachHangYeuThich",
    meta: { layout: "khach-hang", roles: ["khach-hang"] }
  },
  {
    path: "/khach-hang/ban-do",
    component: () => import("../components/KhachHang/BanDo/index.vue"),
    name: "KhachHangBanDo",
    meta: { layout: "khach-hang" }
  },
  {
    path: "/khach-hang/nang-cap-moi-gioi",
    component: () => import("../components/KhachHang/NangCapMoiGioi/index.vue"),
    name: "KhachHangNangCapMoiGioi",
    meta: { layout: "khach-hang", roles: ["khach-hang"] }
  },
  {
    path: "/khach-hang/dinh-gia-ai",
    component: () => import("../components/KhachHang/DinhGia/index.vue"),
    name: "KhachHangDinhGiaAI",
    meta: { layout: "khach-hang" }
  },
  // {
  //   path: "/khach-hang/ve-chung-toi",
  //   component: () => import("../components/KhachHang/VeChungToi/index.vue"),
  //   name: "KhachHangVeChungToi",
  //   meta: { layout: "khach-hang" }
  // },

  // ===== MÔI GIỚI =====
  {
    path: "/moi-gioi/dang-nhap",
    component: () => import("../components/MoiGioi/DangNhap/index.vue"),
    name: "MoiGioiDangNhap",
    meta: { layout: "blank", guest: true }
  },
  {
    path: "/moi-gioi/dang-ky",
    component: () => import("../components/MoiGioi/DangKy/index.vue"),
    name: "MoiGioiDangKy",
    meta: { layout: "blank", guest: true }
  },
  {
    path: "/moi-gioi/quen-mat-khau",
    component: () => import("../components/MoiGioi/QuenMatKhau/index.vue"),
    name: "MoiGioiQuenMatKhau",
    meta: { layout: "blank", guest: true }
  },
  {
    path: "/moi-gioi/xac-thuc-ma",
    component: () => import("../components/MoiGioi/XacThucMaOTP/index.vue"),
    name: "MoiGioiXacThucMa",
    meta: { layout: "blank", guest: true }
  },
  {
    path: "/moi-gioi/dat-lai-mat-khau",
    component: () => import("../components/MoiGioi/DatLaiMatKhau/index.vue"),
    name: "MoiGioiDatLaiMatKhau",
    meta: { layout: "blank", guest: true }
  },
  {
    path: "/moi-gioi/trang-chu",
    component: () => import("../components/MoiGioi/TrangChu/index.vue"),
    name: "MoiGioiTrangChu",
    meta: { layout: "moi-gioi", roles: ["moi-gioi"] }  // ✅ Thêm roles
  },
  {
    path: "/moi-gioi/dang-tin",
    component: () => import("../components/MoiGioi/DangTin/index.vue"),
    name: "MoiGioiDangTin",
    meta: { layout: "moi-gioi", roles: ["moi-gioi"] }  // ✅ Thêm roles
  },
  {
    path: "/moi-gioi/quan-ly-khach-hang",
    component: () => import("../components/MoiGioi/KhachHang/index.vue"),
    name: "MoiGioiQuanLyKhachHang",
    meta: { layout: "moi-gioi", roles: ["moi-gioi"] }  // ✅ Thêm roles
  },
  {
    path: "/moi-gioi/quan-ly-bat-dong-san",
    component: () => import("../components/MoiGioi/QuanLyBDS/index.vue"),
    name: "MoiGioiQuanLyBDS",
    meta: { layout: "moi-gioi", roles: ["moi-gioi"] }  // ✅ Thêm roles
  },
  {
    path: "/moi-gioi/goi-tin",
    component: () => import("../components/MoiGioi/GoiTin/index.vue"),
    name: "MoiGioiGoiTin",
    meta: { layout: "moi-gioi", roles: ["moi-gioi"] }  // ✅ Thêm roles
  },
  {
    path: "/moi-gioi/ho-so-ca-nhan",
    component: () => import("../components/MoiGioi/Profile/index.vue"),
    name: "MoiGioiProfile",
    meta: { layout: "moi-gioi", roles: ["moi-gioi"] }
  },
  {
    path: "/moi-gioi/thong-bao",
    component: () => import("../components/MoiGioi/ThongBao/index.vue"),
    name: "MoiGioiThongBao",
    meta: { layout: "moi-gioi", roles: ["moi-gioi"] }
  },
  {
    path: "/moi-gioi/lich-su-mua-tin",
    component: () => import("../components/MoiGioi/LichSuMuaTin/index.vue"),
    name: "MoiGioiLichSuMuaTin",
    meta: { layout: "moi-gioi", roles: ["moi-gioi"] }
  },

  // ===== ADMIN THÊM =====
  {
    path: "/admin/giao-dich",
    component: () => import("../components/Admin/GiaoDich/index.vue"),
    name: "AdminGiaoDich",
    meta: { layout: "admin", roles: ["admin"] }
  },

  // ===== 404 =====
  {
    path: "/:pathMatch(.*)*",
    redirect: "/"
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// ================= HELPER FUNCTIONS =================
function getLoginPath(path) {
  if (!path) return "/khach-hang/dang-nhap";
  if (path.startsWith("/admin")) return "/admin/dang-nhap";
  if (path.startsWith("/moi-gioi")) return "/moi-gioi/dang-nhap";
  if (path.startsWith("/khach-hang")) return "/khach-hang/dang-nhap";
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

// ================= ROLE-SPECIFIC TOKEN KEYS =================
// Mỗi role dùng localStorage key riêng để có thể đăng nhập đồng thời 3 tab
const ROLE_TOKEN_KEYS = {
  admin: "admin_auth_token",
  "moi-gioi": "moi_gioi_auth_token",
  "khach-hang": "khach_hang_auth_token",
};

/**
 * Lấy token theo role cụ thể
 */
function getTokenForRole(role) {
  const key = ROLE_TOKEN_KEYS[role];
  return key ? localStorage.getItem(key) : null;
}

/**
 * Đoán role từ path đang truy cập
 */
function getRoleFromPath(path) {
  if (path.startsWith("/admin")) return "admin";
  if (path.startsWith("/moi-gioi")) return "moi-gioi";
  return "khach-hang";
}

/**
 * Lấy token của role tương ứng với path đang truy cập
 */
function getTokenForPath(path) {
  const role = getRoleFromPath(path);
  return getTokenForRole(role);
}

// ================= AUTH GUARD =================
router.beforeEach((to, from, next) => {
  // ✅ Lấy token theo path đang vào (KHÔNG dùng chung 1 token nữa)
  const role = getRoleFromPath(to.path);
  const token = getTokenForRole(role);

  console.log("🔍 [Router Guard]", {
    path: to.path,
    role,
    hasToken: !!token,
    meta: to.meta,
    from: from.path
  });

  // ✅ 1. TRANG LOGIN/REGISTER (GUEST) — đã đăng nhập rồi thì redirect về home
  if (to.meta.guest) {
    if (token) {
      console.log("✅ Already logged in as", role, "→ redirect to:", getHomePath(role));
      return next({ path: getHomePath(role) });
    }
    console.log("⏭️ Guest route, no token → proceed");
    return next();
  }

  // ✅ 2. ROUTE CẦN AUTH (CÓ ROLES)
  if (to.meta.roles) {
    if (!token) {
      console.log("❌ No token for role", role, "→ redirect to:", getLoginPath(to.path));
      return next({ path: getLoginPath(to.path) });
    }

    const allowedRoles = Array.isArray(to.meta.roles) ? to.meta.roles : [to.meta.roles];

    if (!allowedRoles.includes(role)) {
      console.log("❌ Wrong role:", role, "allowed:", allowedRoles);
      return next({ path: getHomePath(role) });
    }

    console.log("✅ Auth OK → proceed");
    return next();
  }

  // ✅ 3. PUBLIC ROUTES
  console.log("✅ Public route → proceed");
  next();
});

export default router;