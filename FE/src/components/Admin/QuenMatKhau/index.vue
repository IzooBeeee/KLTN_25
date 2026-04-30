<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-6">
    <div
      class="flex w-full max-w-6xl bg-white rounded-[40px] shadow-2xl overflow-hidden min-h-[650px]"
    >
      <!-- Left Panel (Hình ảnh) -->
      <div
        class="hidden md:flex md:w-1/2 relative bg-indigo-950 text-white p-12 flex-col justify-between"
      >
        <div class="absolute inset-0 z-0">
          <img
            src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80"
            class="w-full h-full object-cover opacity-30"
          />
          <div
            class="absolute inset-0 bg-gradient-to-br from-indigo-900/80 to-indigo-950"
          ></div>
        </div>

        <div class="relative z-10">
          <div
            class="mb-6 inline-block px-4 py-1 bg-white/10 border border-white/20 rounded-full text-[10px] tracking-widest font-semibold uppercase"
          >
            Nền tảng bất động sản cao cấp
          </div>
          <h2 class="text-5xl font-bold leading-tight mb-6 tracking-tighter">
            Khám phá <span class="text-indigo-300 italic">bất động sản</span
            ><br />
            phù hợp với bạn
          </h2>
          <p
            class="text-indigo-200 text-sm max-w-md leading-relaxed font-light"
          >
            Truy cập danh sách các dự án, nhà ở và cơ hội đầu tư tốt nhất được
            chọn lọc dành riêng cho bạn.
          </p>
        </div>

        <div class="relative z-10">
          <div class="flex -space-x-2 mb-3">
            <div
              v-for="i in 3"
              :key="i"
              class="w-8 h-8 rounded-full border-2 border-indigo-900 bg-gray-400"
            ></div>
          </div>
          <p
            class="text-[11px] text-indigo-300 uppercase tracking-widest font-bold"
          >
            Hơn 5.000 khách hàng đã tin tưởng
          </p>
        </div>
      </div>

      <!-- Right Panel (Form) -->
      <div
        class="w-full md:w-1/2 p-8 md:p-16 flex flex-col justify-center bg-white"
      >
        <div class="w-full max-w-[400px] mx-auto">
          <div class="mb-10 text-left">
            <h5
              class="text-indigo-900 font-black text-[10px] uppercase tracking-[0.2em] mb-4"
            >
              Architectural Curator
            </h5>
            <h3 class="text-4xl font-black text-gray-900 mb-2 tracking-tighter">
              Chào mừng trở lại
            </h3>
          </div>
          <h3 class="text-4xl font-black text-gray-900 mb-2 tracking-tighter">
            Quên mật khẩu
          </h3>
          <p class="text-gray-400 text-sm font-medium">
            Nhập email để nhận mã xác nhận
          </p>

          <form @submit.prevent="guiMa" class="space-y-5">
            <!-- Email -->
            <div class="space-y-2">
              <label
                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1"
              >
                Email
              </label>
              <div class="relative group">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-lg"
                  >📧</span
                >
                <input
                  v-model="email"
                  type="email"
                  placeholder="Nhập email của bạn"
                  class="w-full pl-14 pr-6 py-4 bg-gray-50 !rounded-full focus:ring-2 focus:ring-indigo-600 border-none text-sm font-medium transition-all shadow-sm"
                  required
                />
              </div>
            </div>

            <!-- Button -->
            <div class="pt-2">
              <button
                type="submit"
                :disabled="isLoading"
                class="w-full !bg-indigo-950 hover:!bg-black !text-white font-bold py-4 !rounded-full shadow-xl shadow-indigo-100 transition-all disabled:opacity-70 uppercase tracking-[0.15em] text-xs"
              >
                {{ isLoading ? "Đang gửi..." : "Gửi mã xác nhận" }}
              </button>
            </div>
          </form>

          <!-- Register Link -->
          <p
            class="text-center text-[11px] text-gray-400 mt-3 font-medium uppercase tracking-wider"
          >
            Nhớ mật khẩu rồi?
            <router-link
              to="/admin/dang-nhap"
              class="text-indigo-900 font-black hover:underline ml-1"
            >
              Đăng nhập ngay
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/axios/config";
import { createToaster } from "@meforma/vue-toaster";
import { setAuth, getToken, clearAuth } from "@/js/auth";

const toaster = createToaster({ position: "top-right", duration: 3000 });

export default {
  name: "AdminQuenMatKhau",
  data() {
    return {
      email: "",
      password: "",
      isLoading: false,
    };
  },

  mounted() {
    this.checkLogin();
  },

  methods: {
    async guiMa() {
      if (!this.email) {
        toaster.error("Vui lòng nhập email");
        return;
      }

      this.isLoading = true;

      try {
        const res = await api.post("/quen-mat-khau/gui-ma", {
          email: this.email,
        });

        if (res.data.status) {
          toaster.success("Đã gửi mã về email");

          // 👉 chuyển sang trang nhập mã
          this.$router.push({
            path: "/admin/xac-thuc-ma",
            query: { email: this.email },
          });
        } else {
          toaster.error(res.data.message);
        }
      } catch (err) {
        toaster.error("Gửi mã thất bại");
      } finally {
        this.isLoading = false;
      }
    },
    async handleLogin() {
      if (!this.email || !this.password) {
        toaster.error("Vui lòng nhập đầy đủ thông tin");
        return;
      }

      this.isLoading = true;

      try {
        // ✅ Login TRỰC TIẾP - KHÔNG gọi /sanctum/csrf-cookie
        const res = await api.post("/admin/dang-nhap", {
          email: this.email,
          password: this.password,
        });

        if (res.data.status) {
          const token = res.data.token;
          const role = res.data.data?.role || "admin";
          const userInfo = res.data.data;

          // ✅ Lưu vào key riêng của admin
          setAuth("admin", token, userInfo);

          // ✅ Set Authorization header
          api.defaults.headers.common["Authorization"] = `Bearer ${token}`;

          toaster.success("Đăng nhập thành công");

          // ✅ Redirect
          setTimeout(() => {
            this.$router.replace("/admin/dashboard");
          }, 300);
        } else {
          toaster.error(res.data.message || "Sai tài khoản hoặc mật khẩu");
        }
      } catch (err) {
        console.error("Login error:", err);

        if (err.response?.status === 401) {
          toaster.error("Sai email hoặc mật khẩu");
        } else if (err.response?.status === 419) {
          toaster.error("Phiên làm việc hết hạn. Vui lòng tải lại trang");
        } else {
          toaster.error("Đăng nhập thất bại. Vui lòng thử lại");
        }
      } finally {
        this.isLoading = false;
      }
    },

    async checkLogin() {
      const token = getToken("admin");
      if (!token) return;

      try {
        const res = await api.get("/admin/check-token");

        if (res.data.status === "success" || res.data.status === true) {
          if (this.$route.path.includes("dang-nhap") || this.$route.path.includes("quen-mat-khau")) {
            this.$router.replace("/admin/dashboard");
          }
        }
      } catch (error) {
        if (error.response?.status === 401) {
          clearAuth("admin");
        }
      }
    },

    clearAuth() {
      clearAuth("admin");
      delete api.defaults.headers.common["Authorization"];
    },

    loginGoogle() {
      toaster.info("Google login coming soon");
    },
    loginFacebook() {
      toaster.info("Facebook login coming soon");
    },
  },
};
</script>
<style scoped>
.rounded-full {
  border-radius: 9999px !important;
}

input:focus {
  outline: none;
}
</style>