<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-6">
    <div class="flex w-full max-w-6xl bg-white rounded-[40px] shadow-2xl overflow-hidden min-h-[650px]">
      <!-- Left Side - Branding -->
      <div class="hidden md:flex md:w-1/2 relative bg-emerald-950 text-white p-12 flex-col justify-between">
        <div class="absolute inset-0 z-0">
          <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1000&q=80"
            class="w-full h-full object-cover opacity-30" />
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-900/80 to-emerald-950"></div>
        </div>

        <div class="relative z-10">
          <div
            class="mb-6 inline-block px-4 py-1 bg-white/10 border border-white/20 rounded-full text-[10px] tracking-widest font-semibold uppercase">
            Dành cho Môi giới Chuyên nghiệp
          </div>
          <h2 class="text-5xl font-bold leading-tight mb-6 tracking-tighter">
            Quản lý <span class="text-emerald-300 italic">giao dịch</span><br />
            bất động sản
          </h2>
          <p class="text-emerald-200 text-sm max-w-md leading-relaxed font-light">
            Truy cập công cụ quản lý danh sách, khách hàng và theo dõi hiệu suất
            kinh doanh của bạn.
          </p>
        </div>

        <div class="relative z-10">
          <div class="flex -space-x-2 mb-3">
            <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border-2 border-emerald-900 bg-gray-400"></div>
          </div>
          <p class="text-[11px] text-emerald-300 uppercase tracking-widest font-bold">
            Hơn 2.000 môi giới đang sử dụng
          </p>
        </div>
      </div>

      <!-- Right Side - Login Form -->
      <div class="w-full md:w-1/2 p-8 md:p-16 flex flex-col justify-center bg-white">
        <div class="w-full max-w-[400px] mx-auto">
          <div class="mb-10 text-left">
            <h5 class="text-emerald-900 font-black text-[10px] uppercase tracking-[0.2em] mb-4">
              Broker Portal
            </h5>
            <h3 class="text-4xl font-black text-gray-900 mb-2 tracking-tighter">
              Chào mừng trở lại
            </h3>
            <p class="text-gray-400 text-sm font-medium">
              Đăng nhập để quản lý danh sách và khách hàng
            </p>
          </div>

          <form @submit.prevent="handleLogin" class="space-y-5">
            <div class="space-y-2">
              <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Email / Số điện
                thoại</label>
              <div class="relative group">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-lg">📧</span>
                <input v-model="email" type="email" placeholder="Nhập email của bạn"
                  class="w-full pl-14 pr-6 py-4 bg-gray-50 !rounded-full focus:ring-2 focus:ring-emerald-600 border-none text-sm font-medium transition-all shadow-sm"
                  required />
              </div>
            </div>

            <div class="space-y-2">
              <div class="flex justify-between items-center px-1">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Mật khẩu</label>
                <a href="/moi-gioi/quen-mat-khau"
                  class="text-[10px] font-bold text-emerald-600 hover:text-emerald-700">Quên mật khẩu?</a>
              </div>
              <div class="relative group">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-lg">🔒</span>
                <input v-model="password" type="password" placeholder="Nhập mật khẩu"
                  class="w-full pl-14 pr-6 py-4 bg-gray-50 !rounded-full focus:ring-2 focus:ring-emerald-600 border-none text-sm font-medium transition-all shadow-sm"
                  required />
              </div>
            </div>

            <div class="flex items-center">
              <input v-model="rememberMe" type="checkbox" id="remember"
                class="w-4 h-4 text-emerald-600 border-gray-300 !rounded focus:ring-emerald-500" />
              <label for="remember" class="ml-2 text-[11px] font-medium text-gray-500">Ghi nhớ đăng nhập</label>
            </div>

            <div class="pt-2">
              <button type="submit" :disabled="isLoading"
                class="w-full !bg-emerald-950 hover:!bg-black !text-white font-bold py-4 !rounded-full shadow-xl shadow-emerald-100 transition-all disabled:opacity-70 hover:-translate-y-[1px] active:scale-[0.98] uppercase tracking-[0.15em] text-xs">
                {{ isLoading ? "Đang xử lý..." : "Đăng nhập " }}
              </button>
            </div>

            <div class="relative my-8 text-center">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-100"></div>
              </div>
              <span class="relative px-4 bg-white text-[10px] font-bold text-gray-400 uppercase tracking-widest">Hoặc
                tiếp tục với</span>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <button type="button" @click="loginGoogle"
                class="flex items-center justify-center gap-3 py-3.5 !rounded-full border border-gray-100 bg-white hover:bg-gray-50 transition-all text-[11px] font-bold text-gray-700 hover:shadow-md active:scale-[0.98]">
                <img src="https://www.svgrepo.com/show/355037/google.svg" class="w-4 h-4" />
                GOOGLE
              </button>

              <button type="button" @click="loginFacebook"
                class="flex items-center justify-center gap-3 py-3.5 !rounded-full border border-gray-100 bg-white hover:bg-gray-50 transition-all text-[11px] font-bold text-gray-700 hover:shadow-md active:scale-[0.98]">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/facebook/facebook-original.svg"
                  class="w-4 h-4" />
                FACEBOOK
              </button>
            </div>
          </form>

          <p class="text-center text-[11px] text-gray-400 mt-10 font-medium uppercase tracking-wider mt-5">
            Chưa có tài khoản môi giới?
            <router-link to="/moi-gioi/dang-ky" class="text-emerald-900 font-black hover:underline ml-1">Đăng ký
              ngay</router-link>
          </p>

          <p class="text-center text-[10px] text-gray-300 mt-4">
            <router-link to="/khach-hang/dang-nhap" class="hover:text-gray-500">
              ← Quay lại đăng nhập khách hàng
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
// ✅ IMPORT API INSTANCE
import api from "@/axios/config";
import { createToaster } from "@meforma/vue-toaster";
import { updateEchoToken, subscribeUser } from '@/js/services/echo';
import { setAuth, getToken, clearAuth } from "@/js/auth";

const toaster = createToaster({ position: "top-right", duration: 3000 });

export default {
  name: "MoiGioiLogin",

  data() {
    return {
      email: "",
      password: "",
      isLoading: false,
      rememberMe: false,
    };
  },

  mounted() {
    console.log("🔍 [Broker Login] mounted");
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
        console.log("🔐 [Login] Attempting login...");

        const res = await api.post("/moi-gioi/dang-nhap", {
          email: this.email,
          password: this.password,
        });

        console.log("🔐 [Login] Response:", res.data);

        if (res.data.status) {
          const token = res.data.token;
          const userInfo = res.data.data;

          console.log("✅ [Login] Success!");

          // ✅ LƯU VÀO KEY RIÊNG cho môi giới (không ảnh hưởng admin/khách hàng)
          setAuth("moi-gioi", token, userInfo);

          // ✅ Thông báo cho Header biết auth đã thay đổi
          window.dispatchEvent(new Event("moi-gioi-auth-changed"));

          // ✅ UPDATE ECHO AUTH
          updateEchoToken(token);
          if (userInfo?.id) {
            subscribeUser(userInfo.id);
          }

          // Remember me
          if (this.rememberMe) {
            localStorage.setItem("moi_gioi_remember_email", this.email);
          } else {
            localStorage.removeItem("moi_gioi_remember_email");
          }

          toaster.success("Đăng nhập thành công");

          // ✅ REDIRECT
          setTimeout(() => {
            this.$router.replace("/moi-gioi/trang-chu");
          }, 300);
        } else {
          toaster.error(res.data.message || "Sai tài khoản hoặc mật khẩu");
        }
      } catch (err) {
        console.error("❌ [Login] Error:", err);

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
      // ✅ Chỉ check token của môi giới
      const token = getToken("moi-gioi");

      console.log("🔍 [Broker CheckLogin]", {
        hasToken: !!token,
        currentPath: this.$route.path,
      });

      // ✅ Chỉ check nếu đang ở trang login
      if (!this.$route.path.includes("dang-nhap")) {
        console.log("⏭️ Not on login page → skip check");
        return;
      }

      if (!token) {
        console.log("❌ No valid broker token");
        return;
      }

      try {
        console.log("🔍 Verifying broker token...");
        const res = await api.get("moi-gioi/check-token");

        console.log("✅ Token valid:", res.data);

        if (res.data.status === "success" || res.data.status === true) {
          this.$router.replace("/moi-gioi/trang-chu");
        }
      } catch (error) {
        console.error("❌ Token invalid:", error);
        if (error.response?.status === 401) {
          clearAuth("moi-gioi");
        }
      }
    },

    loginGoogle() {
      toaster.info("Tính năng đang phát triển");
    },

    loginFacebook() {
      toaster.info("Tính năng đang phát triển");
    },
  },
};
</script>


<style scoped>
/* Đảm bảo bo tròn được thực thi nếu Tailwind bị ghi đè */
.rounded-full {
  border-radius: 9999px !important;
}

input:focus {
  outline: none;
}

/* Custom checkbox styling */
input[type="checkbox"] {
  cursor: pointer;
}
</style>