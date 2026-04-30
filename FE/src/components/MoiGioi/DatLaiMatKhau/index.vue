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
            Thiết lập bảo mật
          </div>
          <h2 class="text-5xl font-bold leading-tight mb-6 tracking-tighter">
            Tạo <span class="text-indigo-300 italic">mật khẩu mới</span><br />
            cho tài khoản
          </h2>
          <p
            class="text-indigo-200 text-sm max-w-md leading-relaxed font-light"
          >
            Mật khẩu mới cần có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường và
            số.
          </p>
        </div>

        <div class="relative z-10">
          <div class="flex items-center gap-2 text-[11px] text-indigo-300">
            <span class="w-2 h-2 rounded-full bg-green-400"></span>
            <span>Mật khẩu được mã hóa bcrypt</span>
          </div>
        </div>
      </div>

      <!-- Right Panel (Form) -->
      <div
        class="w-full md:w-1/2 p-8 md:p-16 flex flex-col justify-center bg-white"
      >
        <div class="w-full max-w-[400px] mx-auto">
          <div class="mb-8 text-left">
            <h5
              class="text-indigo-900 font-black text-[10px] uppercase tracking-[0.2em] mb-4"
            >
              Architectural Curator
            </h5>
            <h3 class="text-4xl font-black text-gray-900 mb-2 tracking-tighter">
              Đặt lại mật khẩu
            </h3>
            <p class="text-gray-500 text-sm">
              Cho tài khoản:
              <span class="font-semibold text-gray-700">{{ email }}</span>
            </p>
          </div>

          <form @submit.prevent="datLaiMatKhau" class="space-y-5">
            <!-- New Password -->
            <div class="space-y-2">
              <label
                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1"
              >
                Mật khẩu mới
              </label>
              <div class="relative group">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-lg"
                  >🔐</span
                >
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Nhập mật khẩu mới"
                  class="w-full pl-14 pr-12 py-4 bg-gray-50 !rounded-full focus:ring-2 focus:ring-indigo-600 border-none text-sm font-medium transition-all shadow-sm"
                  required
                  minlength="8"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm"
                >
                  {{ showPassword ? "🙈" : "👁️" }}
                </button>
              </div>
              <!-- Password strength hint -->
              <div class="flex gap-1 ml-1">
                <span
                  v-for="i in 3"
                  :key="i"
                  class="h-1 flex-1 rounded-full transition-colors"
                  :class="getStrengthClass(i)"
                ></span>
              </div>
            </div>

            <!-- Confirm Password -->
            <div class="space-y-2">
              <label
                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1"
              >
                Xác nhận mật khẩu
              </label>
              <div class="relative group">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-lg"
                  >✅</span
                >
                <input
                  v-model="confirmPassword"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  placeholder="Nhập lại mật khẩu"
                  class="w-full pl-14 pr-12 py-4 bg-gray-50 !rounded-full focus:ring-2 focus:ring-indigo-600 border-none text-sm font-medium transition-all shadow-sm"
                  required
                />
                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm"
                >
                  {{ showConfirmPassword ? "🙈" : "👁️" }}
                </button>
              </div>
              <p
                v-if="
                  password && confirmPassword && password !== confirmPassword
                "
                class="text-red-500 text-xs ml-1"
              >
                Mật khẩu không khớp
              </p>
            </div>

            <!-- Button -->
            <div class="pt-4">
              <button
                type="submit"
                :disabled="isLoading || !isFormValid"
                class="w-full !bg-indigo-950 hover:!bg-black !text-white font-bold py-4 !rounded-full shadow-xl shadow-indigo-100 transition-all disabled:opacity-70 disabled:cursor-not-allowed uppercase tracking-[0.15em] text-xs"
              >
                {{ isLoading ? "Đang cập nhật..." : "Cập nhật mật khẩu" }}
              </button>
            </div>
          </form>

          <!-- Back Link -->
          <p
            class="text-center text-[11px] text-gray-400 mt-6 font-medium uppercase tracking-wider"
          >
            <router-link
              to="/moi-gioi/quen-mat-khau"
              class="hover:text-indigo-600 transition-colors"
            >
              ← Hủy và quay lại
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

const toaster = createToaster({ position: "top-right", duration: 3000 });

export default {
  name: "DatLaiMatKhau",
  data() {
    return {
      email: "",
      code: "",
      password: "",
      confirmPassword: "",
      showPassword: false,
      showConfirmPassword: false,
      isLoading: false,
    };
  },

  computed: {
    isFormValid() {
      return (
        this.password.length >= 8 &&
        this.password === this.confirmPassword &&
        /[A-Z]/.test(this.password) &&
        /[a-z]/.test(this.password) &&
        /[0-9]/.test(this.password)
      );
    },
  },

  created() {
    this.email = this.$route.query.email;
    this.code = this.$route.query.code;

    if (!this.email || !this.code) {
      toaster.error("Liên kết không hợp lệ hoặc đã hết hạn");
      this.$router.replace("/moi-gioi/quen-mat-khau");
    }
  },

  methods: {
    getStrengthClass(step) {
      const length = this.password.length;
      const hasUpper = /[A-Z]/.test(this.password);
      const hasLower = /[a-z]/.test(this.password);
      const hasNumber = /[0-9]/.test(this.password);

      let score = 0;
      if (length >= 8) score++;
      if (hasUpper && hasLower) score++;
      if (hasNumber) score++;

      if (step === 1) return score >= 1 ? "bg-green-400" : "bg-gray-200";
      if (step === 2) return score >= 2 ? "bg-green-400" : "bg-gray-200";
      return score >= 3 ? "bg-green-400" : "bg-gray-200";
    },

    // Trong DatLaiMatKhau.vue
    async datLaiMatKhau() {
      try {
        const res = await api.post("/moi-gioi/quen-mat-khau/dat-lai", {
          email: this.email,
          code: this.code,
          password: this.password,
          password_confirmation: this.confirmPassword, // ← BẮT BUỘC có field này
        });

        if (res.data.status) {
          toaster.success("Đặt lại mật khẩu thành công!");
          setTimeout(() => {
            this.$router.replace("/moi-gioi/dang-nhap");
          }, 1500);
        }
      } catch (error) {
        if (error.response?.status === 422) {
          const errors = error.response.data.errors;
          // Hiển thị lỗi đầu tiên
          const firstError = Object.values(errors)[0]?.[0];
          toaster.error(firstError || "Dữ liệu không hợp lệ");
        }
      }
    },
  },
};
</script>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="password"] {
  -moz-appearance: textfield;
}
</style>