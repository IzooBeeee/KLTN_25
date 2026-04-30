<template>
  <div class="login-wrapper container-fluid p-0 d-flex flex-column vh-100">
    <div class="row g-0 flex-grow-1">
      <div class="col-lg-7 d-none d-lg-flex left-panel position-relative">
        <div class="overlay"></div>
        <div class="content position-relative p-5 d-flex flex-column justify-content-center text-white w-100">
          <div class="badge-custom mb-4 fw-semibold">
            THE INTELLIGENT ESTATE
          </div>
          <h1 class="main-heading fw-bold">
            Editorial <span class="highlight-text">Intelligence</span><br />
            in Real Estate
          </h1>
        </div>
      </div>

      <div
        class="col-lg-5 d-flex flex-column justify-content-center align-items-center right-panel p-4 p-md-5 bg-white">
        <div class="form-container w-100">
          <div class="mb-4">
            <h5 class="brand-text mb-4">The Intelligent Estate</h5>
            <h2 class="fw-bold mb-2 text-dark" style="font-size: 28px">
              Đăng nhập Quản trị viên
            </h2>
            <p class="text-muted small">
              Đây là cổng truy cập bảo mật dành cho Admin
            </p>
          </div>

          <form @submit.prevent="xuLyDangNhap">
            <div class="mb-3 w-100">
              <label class="form-label text-uppercase text-muted fw-bold small-label">Email Admin</label>
              <div class="custom-field">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" v-model="email" placeholder="admin@intelligentestate.com" required />
              </div>
            </div>

            <div class="mb-3 w-100">
              <label class="form-label text-uppercase text-muted fw-bold small-label mb-2">Mật khẩu</label>
              <div class="custom-field position-relative">
                <i class="fa-solid fa-lock"></i>
                <input :type="showPassword ? 'text' : 'password'" v-model="password" placeholder="••••••••••••"
                  required />
                <i class="fa-solid cursor-pointer ms-auto text-muted" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"
                  @click="showPassword = !showPassword" style="cursor: pointer;"></i>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 w-100">
              <div class="form-check mb-0 d-flex align-items-center gap-2">
                <input class="form-check-input mt-0" type="checkbox" v-model="rememberMe" id="rememberMe"
                  style="cursor: pointer;" />
                <label class="form-check-label text-muted small fw-semibold" for="rememberMe" style="cursor: pointer;">
                  Ghi nhớ đăng nhập
                </label>
              </div>
              <a href="#" class="text-primary text-decoration-none small fw-semibold">Quên mật khẩu?</a>
            </div>

            <div class="w-100">
              <button type="submit" class="btn btn-submit w-100 py-3 fw-bold" :disabled="isLoading">
                <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                <span v-else>
                  Đăng nhập Hệ thống
                  <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
                </span>
              </button>
            </div>
          </form>

          <div class="info-box mt-4 p-3 d-flex align-items-start gap-3">
            <i class="fa-solid fa-shield-halved text-primary mt-1"></i>
            <p class="mb-0 text-muted" style="font-size: 12px; line-height: 1.6">
              Đây là hệ thống quản trị nội bộ. Mọi hoạt động truy cập đều được
              ghi lại theo <strong>Chính sách Bảo mật 2.4.0</strong>. Hành vi
              truy cập trái phép sẽ bị xử lý theo quy định.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div
      class="footer d-flex flex-column flex-md-row justify-content-between align-items-center px-4 py-3 bg-light border-top">
      <span class="text-muted fw-semibold mb-2 mb-md-0" style="font-size: 10px; letter-spacing: 1px">
        © 2026 KLTN NHOM 25.
      </span>
      <div class="d-flex gap-4">
        <a href="#" class="text-muted text-decoration-none fw-semibold"
          style="font-size: 10px; letter-spacing: 1px">SECURITY POLICY</a>
        <a href="#" class="text-muted text-decoration-none fw-semibold"
          style="font-size: 10px; letter-spacing: 1px">SYSTEM STATUS</a>
        <a href="#" class="text-muted text-decoration-none fw-semibold"
          style="font-size: 10px; letter-spacing: 1px">ADMIN HELP</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/axios/config";
import { useRouter } from "vue-router";
import { subscribeAdmin, updateEchoToken } from "../../../js/services/echo";
import { setAuth, getToken, clearAuth } from "@/js/auth";
import { getCurrentInstance } from "vue";

const email = ref("");
const password = ref("");
const isLoading = ref(false);
const showPassword = ref(false);
const rememberMe = ref(false);

const router = useRouter();
const internalInstance = getCurrentInstance();
const toaster = internalInstance.appContext.config.globalProperties.$toast;


onMounted(() => {
  // Khôi phục email nếu có
  const savedEmail = localStorage.getItem("admin_saved_email");
  if (savedEmail) {
    email.value = savedEmail;
    rememberMe.value = true;
  }

  // ✅ Check token admin riêng biệt
  checkAdminLogin();
});

const checkAdminLogin = async () => {
  const token = getToken("admin");
  if (!token) return;

  try {
    const res = await api.get("/admin/check-token");
    if (res.data.status === "success" || res.data.status === true) {
      router.replace("/admin/dashboard");
    }
  } catch (error) {
    if (error.response?.status === 401) {
      clearAuth("admin");
    }
  }
};

const xuLyDangNhap = async () => {
  if (!email.value || !password.value) {
    toaster.error("Vui lòng nhập đầy đủ thông tin");
    return;
  }

  isLoading.value = true;

  try {
    const res = await api.post("/admin/dang-nhap", {
      email: email.value,
      password: password.value,
    });

    if (res.data?.token) {
      const user = res.data.user;

      // ✅ Lưu vào key riêng của admin (không ảnh hưởng moi-gioi/khach-hang)
      setAuth("admin", res.data.token, user);

      updateEchoToken(res.data.token);

      if (user?.id) {
        subscribeAdmin(user.id);
      }

      if (rememberMe.value) {
        localStorage.setItem("admin_saved_email", email.value);
      } else {
        localStorage.removeItem("admin_saved_email");
      }

      toaster.success("Đăng nhập thành công 👋");
      router.push("/admin/dashboard");
      return;
    }

    toaster.error("Phản hồi server không hợp lệ");
  } catch (error) {
    console.error("LOGIN ERROR:", error);

    const status = error.response?.status;
    if (status === 401) {
      toaster.error("Sai tài khoản hoặc mật khẩu");
    } else if (status === 500) {
      toaster.error("Lỗi server backend");
    } else if (error.request) {
      toaster.error("Không kết nối được server");
    } else {
      toaster.error("Lỗi không xác định");
    }
  } finally {
    isLoading.value = false;
  }
};
</script>


<style scoped>
/* Reset nền tổng thể */
.login-wrapper {
  background-color: #ffffff;
  font-family: "Roboto", sans-serif;
}

/* --- CỘT TRÁI (HÌNH ẢNH) --- */
.left-panel {
  background-image: url("https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80");
  background-size: cover;
  background-position: center;
}

.overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg,
      rgba(15, 23, 42, 0.7) 0%,
      rgba(15, 23, 42, 0.2) 100%);
}

.badge-custom {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(8px);
  padding: 6px 16px;
  border-radius: 6px;
  font-size: 11px;
  letter-spacing: 2px;
  display: inline-block;
  width: fit-content;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.main-heading {
  font-size: 56px;
  line-height: 1.1;
  letter-spacing: -1px;
}

.highlight-text {
  color: #93c5fd;
  font-style: italic;
}

/* --- CỘT PHẢI (FORM) --- */
.form-container {
  max-width: 420px;
  width: 100%;
  margin: 0 auto;
}

.brand-text {
  color: #1e3a8a;
  font-weight: 800;
  letter-spacing: -0.5px;
}

.small-label {
  font-size: 11px;
  letter-spacing: 1px;
}

.custom-field {
  width: 100%;
  display: flex;
  align-items: center;
  background: #f1f5f9;
  border-radius: 8px;
  padding: 0 16px;
  /* Thay đổi padding để input và icon đều nhau */
  height: 52px;
  box-sizing: border-box;
}

.custom-field i:first-child {
  margin-right: 10px;
  color: #64748b;
}

.custom-field input {
  border: none;
  outline: none;
  background: transparent;
  width: 100%;
  color: #1e293b;
  height: 100%;
  /* Kéo dài input theo chiều cao container */
}

.custom-field input::placeholder {
  color: #94a3b8;
}

/* Hiệu ứng hover icon mắt */
.cursor-pointer:hover {
  color: #0f172a !important;
}

/* --- NÚT ĐĂNG NHẬP MỚI --- */
.btn-submit {
  background-color: #2563eb;
  /* Màu xanh lam */
  color: white;
  border-radius: 50px;
  /* Bo tròn hoàn toàn */
  border: none;
  transition: all 0.3s ease;
  height: 52px;
  width: 100%;
  box-sizing: border-box;
}

.btn-submit:hover {
  background-color: #1d4ed8;
  /* Đậm hơn khi hover */
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

/* Hộp thông tin */
.info-box {
  background-color: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

/* Footer */
.footer {
  background-color: #f8fafc !important;
}
</style>