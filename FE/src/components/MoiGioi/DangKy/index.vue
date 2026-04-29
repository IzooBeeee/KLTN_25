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
            Trở thành <span class="text-emerald-300 italic">đối tác</span><br />
            của chúng tôi
          </h2>
          <p class="text-emerald-200 text-sm max-w-md leading-relaxed font-light">
            Đăng ký ngay để quản lý danh sách bất động sản, theo dõi khách hàng và phát triển kinh doanh.
          </p>
        </div>

        <div class="relative z-10">
          <div class="flex -space-x-2 mb-3">
            <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border-2 border-emerald-900 bg-gray-400"></div>
          </div>
          <p class="text-[11px] text-emerald-300 uppercase tracking-widest font-bold">
            Hơn 2.000 môi giới đang phát triển cùng chúng tôi
          </p>
        </div>
      </div>

      <!-- Right Side - Registration Form -->
      <div class="w-full md:w-1/2 p-8 md:p-10 flex flex-col justify-center bg-white overflow-y-auto">
        <div class="w-full max-w-[420px] mx-auto">

          <div class="mb-8 text-left">
            <h5 class="text-emerald-900 font-black text-[10px] uppercase tracking-[0.2em] mb-4">
              Broker Portal
            </h5>
            <h3 class="text-3xl font-black text-gray-900 mb-2 tracking-tighter">
              Tạo tài khoản mới
            </h3>
            <p class="text-gray-400 text-sm font-medium">
              Điền thông tin để bắt đầu quản lý bất động sản
            </p>
          </div>

          <form @submit.prevent="handleRegister" class="space-y-4">

            <!-- Full Name -->
            <div class="space-y-1.5">
              <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Họ và tên
                *</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-base">👤</span>
                <input v-model="ten" type="text" placeholder="Nhập họ và tên"
                  class="w-full pl-12 pr-4 py-3.5 bg-gray-50 !rounded-full focus:ring-2 focus:ring-emerald-600 border-none text-sm font-medium transition-all shadow-sm"
                  required />
              </div>
            </div>

            <!-- Email -->
            <div class="space-y-1.5">
              <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Email *</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-base">📧</span>
                <input v-model="email" type="email" placeholder="Nhập email chuyên nghiệp"
                  class="w-full pl-12 pr-4 py-3.5 bg-gray-50 !rounded-full focus:ring-2 focus:ring-emerald-600 border-none text-sm font-medium transition-all shadow-sm"
                  required />
              </div>
            </div>

            <!-- Phone -->
            <div class="space-y-1.5">
              <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Số điện thoại
                *</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-base">📱</span>
                <input v-model="so_dien_thoai" type="tel" placeholder="Nhập số điện thoại"
                  class="w-full pl-12 pr-4 py-3.5 bg-gray-50 !rounded-full focus:ring-2 focus:ring-emerald-600 border-none text-sm font-medium transition-all shadow-sm"
                  required />
              </div>
            </div>

            <!-- Zalo Link (Optional) -->
            <div class="space-y-1.5">
              <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Zalo Link (Tuỳ
                chọn)</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-base">💬</span>
                <input v-model="zalo_link" type="url" placeholder="https://zalo.me/..."
                  class="w-full pl-12 pr-4 py-3.5 bg-gray-50 !rounded-full focus:ring-2 focus:ring-emerald-600 border-none text-sm font-medium transition-all shadow-sm" />
              </div>
            </div>

            <!-- Description (Optional) -->
            <div class="space-y-1.5">
              <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Giới thiệu bản
                thân (Tuỳ chọn)</label>
              <textarea v-model="mo_ta" rows="2" placeholder="Kinh nghiệm, khu vực hoạt động, chuyên môn..."
                class="w-full px-4 py-3 bg-gray-50 !rounded-2xl focus:ring-2 focus:ring-emerald-600 border-none text-sm font-medium transition-all shadow-sm resize-none" />
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
              <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Mật khẩu *</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-base">🔒</span>
                <input v-model="password" type="password" placeholder="Tối thiểu 6 ký tự"
                  class="w-full pl-12 pr-4 py-3.5 bg-gray-50 !rounded-full focus:ring-2 focus:ring-emerald-600 border-none text-sm font-medium transition-all shadow-sm"
                  required minlength="6" />
              </div>
            </div>

            <!-- Confirm Password -->
            <div class="space-y-1.5">
              <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Xác nhận mật khẩu
                *</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-base">🔐</span>
                <input v-model="password_confirmation" type="password" placeholder="Nhập lại mật khẩu"
                  class="w-full pl-12 pr-4 py-3.5 bg-gray-50 !rounded-full focus:ring-2 focus:ring-emerald-600 border-none text-sm font-medium transition-all shadow-sm"
                  required />
              </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="flex items-start pt-1">
              <input v-model="agreeTerms" type="checkbox" id="agree"
                class="w-4 h-4 mt-0.5 text-emerald-600 border-gray-300 !rounded focus:ring-emerald-500" />
              <label for="agree" class="ml-2 text-[10px] text-gray-500 leading-relaxed">
                Tôi đồng ý với <a href="#" class="text-emerald-600 font-bold hover:underline">Điều khoản dịch vụ</a>
                và <a href="#" class="text-emerald-600 font-bold hover:underline">Chính sách bảo mật</a>
              </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-3">
              <button type="submit" :disabled="isLoading || !agreeTerms"
                class="w-full !bg-emerald-950 hover:!bg-black !text-white font-bold py-4 !rounded-full shadow-xl shadow-emerald-100 transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:-translate-y-[1px] active:scale-[0.98] uppercase tracking-[0.15em] text-xs">
                {{ isLoading ? 'Đang xử lý...' : 'Đăng ký ngay ' }}
              </button>
            </div>

          </form>

          <!-- Login Link -->
          <p class="text-center text-[11px] text-gray-400 mt-6 font-medium uppercase tracking-wider">
            Đã có tài khoản môi giới?
            <router-link to="/moi-gioi/dang-nhap" class="text-emerald-900 font-black hover:underline ml-1">Đăng nhập
              ngay</router-link>
          </p>

          <!-- Back to Customer -->
          <p class="text-center text-[10px] text-gray-300 mt-3">
            <router-link to="/khach-hang/dang-ky" class="hover:text-gray-500">
              ← Đăng ký tài khoản khách hàng
            </router-link>
          </p>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/axios/config';
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({ position: "top-right" });

export default {
  name: 'MoiGioiRegister',

  data() {
    return {
      ten: '',
      email: '',
      so_dien_thoai: '',
      zalo_link: '',
      mo_ta: '',
      password: '',
      password_confirmation: '',
      agreeTerms: false,
      isLoading: false
    }
  },

  methods: {
    // ===== REGISTER FOR BROKERS =====
    async handleRegister() {
      // Validate password match
      if (this.password !== this.password_confirmation) {
        toaster.error('Mật khẩu xác nhận không khớp');
        return;
      }

      // Validate terms agreement
      if (!this.agreeTerms) {
        toaster.error('Vui lòng đồng ý với điều khoản dịch vụ');
        return;
      }

      this.isLoading = true;

      try {
        const response = await api.post('/moi-gioi/dang-ky', {
            ten: this.ten,
            email: this.email,
            so_dien_thoai: this.so_dien_thoai,
            zalo_link: this.zalo_link || '',
            mo_ta: this.mo_ta || '',
            password: this.password,
            password_confirmation: this.password_confirmation
          }
        );

        console.log('✅ Register response:', response.data);

        if (response.data.status) {
          // Auto login after successful registration
          const token = response.data.data?.token;
          const userData = response.data.data?.moi_gioi;

          if (token && userData) {
            localStorage.setItem("auth_token", token);
            localStorage.setItem("user_type", "moi-gioi");
            localStorage.setItem("user_info", JSON.stringify(userData));
          }

          toaster.success("Đăng ký thành công! Chào mừng bạn 🎉");

          // Redirect to broker dashboard
          this.$router.push("/moi-gioi/trang-chu");
        } else {
          toaster.error(response.data.message || 'Đăng ký thất bại');
        }

      } catch (err) {
        // ✅ Hiển thị lỗi chi tiết từ Laravel Validation
        const errors = err.response?.data?.errors;
        const message = errors
          ? Object.values(errors).flat().join('<br>') // Hiển thị nhiều lỗi
          : err.response?.data?.message || 'Đăng ký thất bại';

        toaster.error(message);
        console.error('Register error:', err.response?.data);

      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>

<style scoped>
/* Đảm bảo bo tròn được thực thi nếu Tailwind bị ghi đè */
.rounded-full {
  border-radius: 9999px !important;
}

input:focus,
textarea:focus {
  outline: none;
}

/* Custom checkbox styling */
input[type="checkbox"] {
  cursor: pointer;
}

/* Scrollbar cho form khi màn hình nhỏ */
@media (max-height: 700px) {
  .overflow-y-auto::-webkit-scrollbar {
    width: 4px;
  }

  .overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
  }

  .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
  }
}
</style>