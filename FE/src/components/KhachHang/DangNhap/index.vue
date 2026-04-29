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
            <p class="text-gray-400 text-sm font-medium">
              Vui lòng đăng nhập để tiếp tục quản lý tài khoản
            </p>
          </div>

          <form @submit.prevent="handleLogin" class="space-y-5">
            <!-- Email Input -->
            <div class="space-y-2">
              <label
                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1"
                >Email / Số điện thoại</label
              >
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

            <!-- Password Input -->
            <div class="space-y-2">
              <div class="flex justify-between items-center px-1">
                <label
                  class="text-[10px] font-black text-gray-400 uppercase tracking-widest"
                >
                  Mật khẩu
                </label>

                <!-- 🔥 THÊM DÒNG NÀY -->
                <router-link
                  to="/khach-hang/quen-mat-khau"
                  class="text-[10px] font-black text-gray-400 uppercase tracking-widest"
                >
                  Quên mật khẩu?
                </router-link>
              </div>
              <div class="relative group">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-lg"
                  >🔒</span
                >
                <input
                  v-model="password"
                  type="password"
                  placeholder="Nhập mật khẩu"
                  class="w-full pl-14 pr-6 py-4 bg-gray-50 !rounded-full focus:ring-2 focus:ring-indigo-600 border-none text-sm font-medium transition-all shadow-sm"
                  required
                />
              </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
              <button
                type="submit"
                :disabled="isLoading"
                class="w-full !bg-indigo-950 hover:!bg-black !text-white font-bold py-4 !rounded-full shadow-xl shadow-indigo-100 transition-all disabled:opacity-70 hover:-translate-y-[1px] active:scale-[0.98] uppercase tracking-[0.15em] text-xs"
              >
                {{ isLoading ? "Đang xử lý..." : "Đăng nhập " }}
              </button>
            </div>

            <!-- Divider -->
            <div class="relative my-8 text-center">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-100"></div>
              </div>
              <span
                class="relative px-4 bg-white text-[10px] font-bold text-gray-400 uppercase tracking-widest"
                >Hoặc tiếp tục với</span
              >
            </div>

            <!-- Social Login -->
            <div class="grid grid-cols-2 gap-4">
              <button
                type="button"
                @click="loginGoogle"
                class="flex items-center justify-center gap-3 py-3.5 !rounded-full border border-gray-100 bg-white hover:bg-gray-50 transition-all text-[11px] font-bold text-gray-700 hover:shadow-md active:scale-[0.98]"
              >
                <img
                  src="https://www.svgrepo.com/show/355037/google.svg"
                  class="w-4 h-4"
                />
                GOOGLE
              </button>

              <button
                type="button"
                @click="loginFacebook"
                class="flex items-center justify-center gap-3 py-3.5 !rounded-full border border-gray-100 bg-white hover:bg-gray-50 transition-all text-[11px] font-bold text-gray-700 hover:shadow-md active:scale-[0.98]"
              >
                <img
                  src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/facebook/facebook-original.svg"
                  class="w-4 h-4"
                />
                FACEBOOK
              </button>
            </div>
          </form>

          <!-- Register Link -->
          <p
            class="text-center text-[11px] text-gray-400 mt-10 font-medium uppercase tracking-wider mt-5"
          >
            Chưa có tài khoản?
            <router-link
              to="/khach-hang/dang-ky"
              class="text-indigo-900 font-black hover:underline ml-1"
              >Đăng ký ngay</router-link
            >
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/axios/config";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right", duration: 3000 });

export default {
  name: "KhachHangLogin",
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
    async handleLogin() {
      if (!this.email || !this.password) {
        toaster.error("Vui lòng nhập đầy đủ thông tin");
        return;
      }

      this.isLoading = true;

      try {
        // ✅ Login TRỰC TIẾP - KHÔNG gọi /sanctum/csrf-cookie
        const res = await api.post("/khach-hang/dang-nhap", {
          email: this.email,
          password: this.password,
        });

        if (res.data.status) {
          const token = res.data.token;
          const role = res.data.data?.role || "khach-hang";
          const userInfo = res.data.data;

          // ✅ Lưu vào localStorage
          localStorage.setItem("auth_token", token);
          localStorage.setItem("user_type", role);
          localStorage.setItem("user_info", JSON.stringify(userInfo));


          toaster.success("Đăng nhập thành công");

          // ✅ Redirect
          setTimeout(() => {
            this.$router.replace("/khach-hang/trang-chu");
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
      const token = localStorage.getItem("auth_token");
      if (!token) return;

      try {
        api.defaults.headers.common["Authorization"] = `Bearer ${token}`;
        const res = await api.get("/khach-hang/check-token");

        if (res.data.status) {
          if (this.$route.path.includes("dang-nhap")) {
            this.$router.replace("/khach-hang/trang-chu");
          }
        }
      } catch (error) {
        if (error.response?.status === 401) {
          this.clearAuth();
        }
      }
    },

    clearAuth() {
      localStorage.removeItem("auth_token");
      localStorage.removeItem("user_type");
      localStorage.removeItem("user_info");
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