<template>
  <div class="profile-page font-['Inter']">

    <!-- Hero Banner -->
    <div class="profile-banner">
      <div class="banner-bg"></div>
      <div class="banner-curve"></div>
    </div>

    <div class="container mx-auto max-w-6xl px-4 -mt-12 pb-12 relative z-10">

      <!-- Profile Header -->
      <div class="bg-white rounded-3xl shadow-xl p-6 mb-6 flex flex-col sm:flex-row items-center sm:items-end gap-5">
        <div class="relative -mt-12 sm:-mt-16 flex-shrink-0">
          <img :src="profile.avatar || defaultAvatar" alt="Avatar"
            class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border-4 border-white shadow-lg object-cover" />
          <span class="absolute -bottom-2 -right-2 w-7 h-7 bg-green-500 border-2 border-white rounded-full flex items-center justify-center">
            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
          </span>
        </div>
        <div class="flex-1 text-center sm:text-left">
          <div class="flex items-center justify-center sm:justify-start gap-2 mb-0.5">
            <h1 class="text-2xl font-black text-gray-900">{{ profile.ten || 'Chưa cập nhật' }}</h1>
            <span class="px-2.5 py-0.5 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">Môi Giới</span>
          </div>
          <p class="text-sm text-gray-400 mb-1">{{ profile.mo_ta || 'Chuyên viên tư vấn cao cấp' }}</p>
          <div class="flex items-center justify-center sm:justify-start gap-4 text-xs text-gray-400">
            <span>MG-{{ profile.id }}</span>
            <span>•</span>
            <span>Tham gia {{ formatDate(profile.created_at) }}</span>
            <span>•</span>
            <span class="text-green-500 font-semibold">Đang hoạt động</span>
          </div>
        </div>
        <button @click="saveProfile" :disabled="loading"
          class="flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-200 hover:-translate-y-0.5 transition-all disabled:opacity-60">
          <svg v-if="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
          {{ loading ? 'Đang lưu...' : 'Lưu thay đổi' }}
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT: Stats sidebar -->
        <div class="space-y-5">

          <!-- Stats Card -->
          <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <h3 class="text-sm font-bold text-gray-700">Tổng tiền đã mua gói</h3>
            </div>
            <div class="mb-4">
              <span class="text-3xl font-black text-gray-900">{{ formatCurrency(stats.tongTien) }}</span>
              <span class="text-sm text-gray-400 ml-1">VND</span>
            </div>

            <!-- Usage progress -->
            <div class="mb-4">
              <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                <span>Tin đã sử dụng</span>
                <span class="font-semibold">{{ stats.tinDaDang || 0 }} / {{ (stats.tinDaDang || 0) + (stats.tinConLai || 0) }}</span>
              </div>
              <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500"
                  :style="{ width: usagePercent + '%', background: usagePercent > 80 ? '#f59e0b' : '#10b981' }"></div>
              </div>
            </div>

            <!-- Mini stats grid -->
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-blue-50 rounded-xl p-3 text-center">
                <div class="text-xl font-black text-blue-600">{{ stats.tinDaDang || 0 }}</div>
                <div class="text-xs text-gray-400 mt-0.5">Đã đăng</div>
              </div>
              <div class="bg-green-50 rounded-xl p-3 text-center">
                <div class="text-xl font-black text-green-600">{{ stats.tinConLai || 0 }}</div>
                <div class="text-xs text-gray-400 mt-0.5">Còn lại</div>
              </div>
            </div>
          </div>

          <!-- Danger zone -->
          <div class="bg-white rounded-2xl shadow-sm p-5 border border-red-100">
            <h3 class="text-sm font-bold text-red-600 mb-3">Vùng nguy hiểm</h3>
            <button @click="confirmLogoutAll"
              class="w-full flex items-center justify-center gap-2 py-2.5 border-2 border-red-400 text-red-500 text-sm font-bold rounded-xl hover:bg-red-50 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              Đăng xuất tất cả thiết bị
            </button>
          </div>

        </div>

        <!-- RIGHT: Forms -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Personal info -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <h2 class="text-base font-bold text-gray-800">Thông tin cá nhân</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Họ và tên</label>
                <input type="text" v-model="profile.ten" placeholder="Nhập họ và tên"
                  class="w-full px-4 py-2.5 bg-gray-50 border rounded-xl text-sm outline-none transition focus:ring-2 focus:border-transparent"
                  :class="errors.ten ? 'border-red-400 focus:ring-red-300' : 'border-gray-200 focus:ring-blue-500'" />
                <p v-if="errors.ten" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.ten) ? errors.ten[0] : errors.ten }}</p>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Email</label>
                <input type="email" v-model="profile.email" placeholder="email@example.com"
                  class="w-full px-4 py-2.5 bg-gray-50 border rounded-xl text-sm outline-none transition focus:ring-2 focus:border-transparent"
                  :class="errors.email ? 'border-red-400 focus:ring-red-300' : 'border-gray-200 focus:ring-blue-500'" />
                <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}</p>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Số điện thoại</label>
                <input type="tel" v-model="profile.so_dien_thoai" placeholder="+84 xxx xxx xxx"
                  class="w-full px-4 py-2.5 bg-gray-50 border rounded-xl text-sm outline-none transition focus:ring-2 focus:border-transparent"
                  :class="errors.so_dien_thoai ? 'border-red-400 focus:ring-red-300' : 'border-gray-200 focus:ring-blue-500'" />
                <p v-if="errors.so_dien_thoai" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.so_dien_thoai) ? errors.so_dien_thoai[0] : errors.so_dien_thoai }}</p>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Link Zalo</label>
                <input type="text" v-model="profile.zalo_link" placeholder="https://zalo.me/..."
                  class="w-full px-4 py-2.5 bg-gray-50 border rounded-xl text-sm outline-none transition focus:ring-2 focus:border-transparent"
                  :class="errors.zalo_link ? 'border-red-400 focus:ring-red-300' : 'border-gray-200 focus:ring-blue-500'" />
                <p v-if="errors.zalo_link" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.zalo_link) ? errors.zalo_link[0] : errors.zalo_link }}</p>
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Mô tả</label>
                <textarea v-model="profile.mo_ta" placeholder="Mô tả ngắn gọn về bạn..." rows="3"
                  class="w-full px-4 py-2.5 bg-gray-50 border rounded-xl text-sm outline-none transition focus:ring-2 focus:border-transparent resize-none"
                  :class="errors.mo_ta ? 'border-red-400 focus:ring-red-300' : 'border-gray-200 focus:ring-blue-500'"></textarea>
                <p v-if="errors.mo_ta" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.mo_ta) ? errors.mo_ta[0] : errors.mo_ta }}</p>
              </div>
            </div>
          </div>

          <!-- Change Password -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              </div>
              <h2 class="text-base font-bold text-gray-800">Thay đổi mật khẩu</h2>
            </div>
            <div class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Mật khẩu hiện tại</label>
                  <div class="relative">
                    <input :type="showCurrentPassword ? 'text' : 'password'" v-model="passwordForm.old_password" placeholder="••••••••"
                      class="w-full px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition" />
                    <button type="button" @click="showCurrentPassword = !showCurrentPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                      <svg v-if="!showCurrentPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Mật khẩu mới</label>
                  <input type="password" v-model="passwordForm.password" placeholder="Mật khẩu mới"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition" />
                </div>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Xác nhận mật khẩu</label>
                <input type="password" v-model="passwordForm.re_password" placeholder="Nhập lại mật khẩu"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition" />
              </div>
              <p class="text-xs text-gray-400 bg-amber-50 p-3 rounded-xl">Mật khẩu nên bao gồm ít nhất 8 ký tự, kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt.</p>
              <button @click="changePassword"
                class="flex items-center gap-2 px-5 py-2.5 border-2 border-amber-400 text-amber-600 text-sm font-bold rounded-xl hover:bg-amber-50 transition">
                Cập nhật mật khẩu
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

  <!-- Security Alert Modal -->
  <Teleport to="body">
    <div v-if="showLogoutAllModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 text-white">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg font-black">Xác nhận đăng xuất tất cả</h3>
          </div>
        </div>
        <div class="p-6">
          <p class="font-semibold text-gray-700 mb-3">Hành động này sẽ:</p>
          <ul class="space-y-2 mb-4">
            <li class="flex items-center gap-2 text-sm text-gray-600">
              <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
              Đăng xuất khỏi <strong>TẤT CẢ</strong> thiết bị đang đăng nhập
            </li>
            <li class="flex items-center gap-2 text-sm text-gray-600">
              <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
              Bạn sẽ cần đăng nhập lại trên mọi thiết bị
            </li>
          </ul>
          <p class="text-sm text-red-600 font-bold mb-5">Bạn có chắc chắn muốn tiếp tục?</p>
          <div class="flex gap-3">
            <button @click="closeModal" class="flex-1 py-3 border-2 border-gray-200 text-gray-500 font-bold rounded-xl hover:bg-gray-50 transition text-sm">Hủy</button>
            <button @click="logoutAll" class="flex-1 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white font-bold rounded-xl shadow-lg shadow-red-200 hover:-translate-y-0.5 transition-all text-sm flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              Đăng xuất tất cả
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
  </div>
</template>
<script>
import api from "@/axios/config";
import Swal from "sweetalert2";
import { useRouter } from "vue-router";
import { clearAuth, getToken } from "@/js/auth";

export default {
  data() {
    return {
      loading: false,
      showLogoutAllModal: false,
      showCurrentPassword: false,
      errors: {},
      profile: {
        id: "",
        ten: "",
        email: "",
        so_dien_thoai: "",
        zalo_link: "",
        dia_chi: "",
        khu_vuc_hoat_dong: "",
        mo_ta: "",
        created_at: "",
      },
      passwordForm: {
        old_password: "",
        password: "",
        re_password: "",
      },

      stats: {
        tinDaDang: 0, // Số tin đã đăng
        tinConLai: 0, // Số tin còn lại
        tongTien: 0, // Tổng tiền đã mua gói
        soGoiDaMua: 0, // Số gói đã mua
      },

      defaultAvatar:
        "https://ui-avatars.com/api/?name=Moi+Gioi&background=0a0a4d&color=fff&rounded=true&size=200",
      // ✅ Lấy token từ key riêng của môi giới
      token: getToken("moi-gioi"),
    };
  },

  // ✅ 2. Khởi tạo router
  setup() {
    const router = useRouter();
    return { router };
  },

  computed: {
    // ✅ Tính % tin đã sử dụng cho progress bar
    usagePercent() {
      const total = (this.stats.tinDaDang || 0) + (this.stats.tinConLai || 0);
      if (total === 0) return 0;
      return Math.round((this.stats.tinDaDang / total) * 100);
    },
  },

  mounted() {
    this.fetchProfile();
    this.fetchStats(); // ✅ Load stats khi component mount
  },

  methods: {
    formatDate(dateString) {
      if (!dateString) return "-";
      const date = new Date(dateString);
      const day = String(date.getDate()).padStart(2, "0");
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    },

    // ✅ Format tiền VND
    formatCurrency(value) {
      if (!value && value !== 0) return "0";
      return new Intl.NumberFormat("vi-VN").format(value);
    },

    // ✅ Lấy thống kê từ API - THÊM DEBUG
    async fetchStats() {
      try {
        const token = this.token;
        const headers = { Authorization: `Bearer ${token}` };

        console.log("📡 Calling stats APIs...");

        const [tinDaDang, tinConLai, tongTien] = await Promise.all([
          api.get("/moi-gioi/thong-ke/tong-tin-da-dang"),
          api.get("/moi-gioi/thong-ke/tin-con-lai"),
          api.get("/moi-gioi/thong-ke/tong-tien"),
        ]);

        // Gán dữ liệu từ backend
        this.stats = {
          tinDaDang:
            typeof tinDaDang.data?.data === "number" ? tinDaDang.data.data : 0,
          tinConLai:
            typeof tinConLai.data?.data === "number" ? tinConLai.data.data : 0,
          tongTien: Number(tongTien.data?.data) || 0,  // ✅ Fix ở đây
          soGoiDaMua: 0,
        };

      } catch (error) {
        console.error("Lỗi load stats:", error);
      }
    },

    // ✅ Đóng modal (sửa cách gọi Bootstrap modal)
    closeModal() {
      this.showLogoutAllModal = false;
      // Nếu dùng Bootstrap JS để ẩn modal vật lý (nếu có)
      const modalEl = document.querySelector(".modal");
      if (modalEl) {
        modalEl.classList.remove("show");
        modalEl.style.display = "none";
        document.body.classList.remove("modal-open");
        document.body.style.overflow = "";
        const backdrop = document.querySelector(".modal-backdrop");
        if (backdrop) backdrop.remove();
      }
    },

    // ✅ Xác nhận hiện modal
    confirmLogoutAll() {
      this.showLogoutAllModal = true;
      // Hiển thị modal bằng Bootstrap JS nếu cần
      this.$nextTick(() => {
        const modalEl = document.querySelector(".modal");
        if (modalEl && window.bootstrap?.Modal) {
          const modal = new window.bootstrap.Modal(modalEl);
          modal.show();
        }
      });
    },

    // ✅ Đăng xuất tất cả thiết bị (FIXED)
    async logoutAll() {
      try {
        // ✅ Tạo api instance tạm thời hoặc dùng axios trực tiếp
        const response = await api.post("/moi-gioi/dang-xuat-tat-ca");

        if (response.data.status === "success") {
          // ✅ XÓA TOKEN MÔI GIỚI (không ảnh hưởng admin/khách hàng)
          clearAuth("moi-gioi");

          Swal.fire({
            icon: "success",
            title: "Thành công",
            text: "Đã đăng xuất từ tất cả thiết bị!",
            timer: 2000,
            showConfirmButton: false,
          });

          setTimeout(() => {
            this.router.push("/moi-gioi/dang-nhap"); // ✅ Dùng this.router
          }, 2000);
        }
      } catch (error) {
        console.error("Logout error:", error);

        // ✅ VẪN XÓA TOKEN
        clearAuth("moi-gioi");

        Swal.fire({
          icon: "error",
          title: "Lỗi",
          text: "Có lỗi xảy ra khi đăng xuất",
        });

        setTimeout(() => {
          this.router.push("/moi-gioi/dang-nhap"); // ✅ Dùng this.router
        }, 2000);
      } finally {
        this.closeModal(); // ✅ Thêm 'this.'
      }
    },

    // ... giữ nguyên các method khác (fetchProfile, saveProfile, changePassword) ...
    async fetchProfile() {
      try {
        const res = await api.get("/moi-gioi/profile");
        if (res.data.status) {
          Object.assign(this.profile, res.data.data);
        }
      } catch (error) {
        Swal.fire("Lỗi", "Không tải được hồ sơ", "error");
      }
    },

    async saveProfile() {
      this.loading = true;
      this.errors = {};

      try {
        const res = await api.post("/moi-gioi/update-profile", this.profile);

        if (res.data.status === 1) {
          Swal.fire(
            "Thành công",
            res.data.message || "Cập nhật hồ sơ thành công",
            "success"
          );
          await this.fetchProfile();
        } else {
          Swal.fire(
            "Lỗi",
            res.data.message || "Không thể cập nhật hồ sơ",
            "error"
          );
        }
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors;
        } else {
          Swal.fire("Lỗi", "Đã xảy ra lỗi hệ thống", "error");
        }
        console.error("Lỗi cập nhật profile:", error);
      }
      this.loading = false;
    },

    async changePassword() {
      if (!this.passwordForm.old_password || !this.passwordForm.password) {
        Swal.fire(
          "Cảnh báo",
          "Vui lòng nhập đầy đủ thông tin mật khẩu!",
          "warning"
        );
        return;
      }
      if (this.passwordForm.password !== this.passwordForm.re_password) {
        Swal.fire("Lỗi", "Xác nhận mật khẩu không khớp", "error");
        return;
      }
      try {
        const res = await api.post("/moi-gioi/update-password", this.passwordForm);

        if (res.data.status === 1 || res.data.status === true) {
          Swal.fire("Thành công", "Đổi mật khẩu thành công", "success");
          this.passwordForm = {
            old_password: "",
            password: "",
            re_password: "",
          };
        } else {
          Swal.fire(
            "Lỗi",
            res.data.message || "Đổi mật khẩu thất bại",
            "error"
          );
        }
      } catch (error) {
        Swal.fire(
          "Lỗi",
          error.response?.data?.message || "Đổi mật khẩu thất bại",
          "error"
        );
      }
    },
  },
};
</script>

<style scoped>
.profile-page {
  background: #f0f4f8;
  min-height: 100%;
}

.profile-banner {
  position: relative;
  height: 160px;
  background: linear-gradient(to right, #0a0e27, #1e3a8a, #1d4ed8);
  overflow: hidden;
  margin: 0 -16px 0;
}

.banner-bg {
  position: absolute;
  inset: 0;
  opacity: 0.2;
  background-image: url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1600&q=60');
  background-size: cover;
  background-position: center;
}

.banner-curve {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 56px;
  background: #f0f4f8;
  clip-path: ellipse(55% 100% at 50% 100%);
}
</style>
