<template>
  <div class="min-h-screen bg-[#f0f4f8] font-['Inter']">

    <!-- Hero Banner -->
    <div class="relative h-40 bg-gradient-to-r from-[#1e3a8a] via-[#1d4ed8] to-[#0ea5e9] overflow-hidden">
      <div class="absolute inset-0 opacity-20" style="background-image:url('https://images.unsplash.com/photo-1549490349-8643362247b5?w=1600&q=60');background-size:cover;background-position:center"></div>
      <div class="absolute bottom-0 left-0 right-0 h-16 bg-[#f0f4f8]" style="clip-path:ellipse(55% 100% at 50% 100%)"></div>
    </div>

    <div class="container mx-auto max-w-6xl px-4 -mt-16 pb-12">

      <!-- Profile Header Card -->
      <div class="bg-white rounded-3xl shadow-xl p-6 mb-6 flex flex-col sm:flex-row items-center sm:items-end gap-5">
        <div class="relative -mt-16 sm:-mt-20 flex-shrink-0">
          <img :src="avatarUrl" class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border-4 border-white shadow-lg object-cover" alt="Avatar" />
          <span class="absolute -bottom-2 -right-2 w-7 h-7 bg-green-500 border-2 border-white rounded-full flex items-center justify-content-center">
            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
          </span>
        </div>
        <div class="flex-1 text-center sm:text-left">
          <div class="flex items-center justify-center sm:justify-start gap-2 mb-1">
            <h1 class="text-2xl font-black text-gray-900">{{ profile.ten || 'Khách hàng' }}</h1>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
              Khách hàng
            </span>
          </div>
          <p class="text-sm text-gray-400">Thành viên từ {{ joinDate }}</p>
        </div>
        <button @click="updateProfile" :disabled="saving"
          class="flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-200 hover:shadow-blue-300 transition-all hover:-translate-y-0.5 disabled:opacity-60">
          <svg v-if="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <span v-if="saving" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
          {{ saving ? 'Đang lưu...' : 'Lưu thay đổi' }}
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT column -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Personal Info -->
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
                <input v-model="profile.ten" type="text" placeholder="Nhập họ và tên"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Email</label>
                <input v-model="profile.email" type="email" placeholder="Email của bạn"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Số điện thoại</label>
                <input v-model="profile.so_dien_thoai" type="tel" placeholder="Số điện thoại"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Quốc gia</label>
                <input value="Việt Nam" type="text" readonly
                  class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-sm text-gray-500 cursor-not-allowed" />
              </div>
            </div>
          </div>

          <!-- Change Password -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              </div>
              <h2 class="text-base font-bold text-gray-800">Đổi mật khẩu</h2>
            </div>
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Mật khẩu hiện tại</label>
                <input v-model="password.old_password" type="password" placeholder="••••••••"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition" />
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Mật khẩu mới</label>
                  <input v-model="password.password" type="password" placeholder="••••••••"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Xác nhận mật khẩu</label>
                  <input v-model="password.password_confirmation" type="password" placeholder="••••••••"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition" />
                </div>
              </div>
              <button @click="changePassword" :disabled="changingPwd"
                class="flex items-center gap-2 px-5 py-2.5 border-2 border-amber-400 text-amber-600 text-sm font-bold rounded-xl hover:bg-amber-50 transition disabled:opacity-60">
                <span v-if="changingPwd" class="w-4 h-4 border-2 border-amber-400/30 border-t-amber-500 rounded-full animate-spin"></span>
                {{ changingPwd ? 'Đang cập nhật...' : 'Cập nhật mật khẩu' }}
              </button>
            </div>
          </div>

        </div>

        <!-- RIGHT column -->
        <div class="space-y-6">

          <!-- Quick Actions -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-800 mb-4">Truy cập nhanh</h2>
            <div class="space-y-2">
              <router-link to="/khach-hang/yeu-thich"
                class="flex items-center justify-between p-3 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 transition group">
                <span class="flex items-center gap-2 text-sm font-semibold">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                  BĐS đã lưu
                </span>
                <span class="text-xs font-black bg-red-500 text-white rounded-full px-2 py-0.5">{{ favCount }}</span>
              </router-link>
              <router-link to="/khach-hang/danh-sach-bat-dong-san"
                class="flex items-center gap-2 p-3 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Tìm kiếm BĐS
              </router-link>
              <router-link to="/khach-hang/ban-do"
                class="flex items-center gap-2 p-3 rounded-xl bg-green-50 hover:bg-green-100 text-green-600 text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                Xem bản đồ
              </router-link>
            </div>
          </div>

          <!-- Upgrade Banner -->
          <div class="relative rounded-2xl overflow-hidden p-6 text-white" style="background:linear-gradient(135deg,#1e3a8a,#1d4ed8)">
            <div class="absolute top-0 right-0 w-32 h-32 opacity-10" style="background:radial-gradient(circle,white,transparent)"></div>
            <div class="text-3xl mb-3">🚀</div>
            <h3 class="font-black text-lg mb-1">Trở thành Môi Giới</h3>
            <p class="text-sm text-blue-200 mb-4 leading-relaxed">Đăng BĐS, tiếp cận hàng nghìn khách hàng tiềm năng.</p>
            <router-link to="/khach-hang/nang-cap-moi-gioi"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-blue-700 text-sm font-black rounded-xl hover:bg-blue-50 transition shadow-lg">
              Nâng cấp ngay
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </router-link>
          </div>

          <!-- Recent Favorites -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-base font-bold text-gray-800">Yêu thích gần đây</h2>
              <router-link to="/khach-hang/yeu-thich" class="text-xs font-bold text-blue-600 hover:underline">Xem tất cả</router-link>
            </div>
            <div v-if="favorites.length === 0" class="text-center py-4">
              <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
              <p class="text-sm text-gray-400">Chưa có BĐS yêu thích</p>
            </div>
            <div v-for="f in favorites.slice(0, 3)" :key="f.id" class="flex gap-3 mb-3 last:mb-0">
              <img :src="f.bat_dong_san?.anh_dai_dien_url || 'https://placehold.co/54x54?text=No'"
                class="w-14 h-14 rounded-xl object-cover flex-shrink-0" />
              <div class="overflow-hidden">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ f.bat_dong_san?.tieu_de }}</p>
                <p class="text-xs text-gray-400">{{ f.bat_dong_san?.dia_chi?.quan?.ten_quan }}</p>
                <p class="text-sm font-bold text-green-600">{{ formatCurrency(f.bat_dong_san?.gia) }}</p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from "vue";
import api from "@/axios/config";
import Swal from "sweetalert2";

const profile = reactive({ ten: "", email: "", so_dien_thoai: "", created_at: "" });
const password = reactive({ old_password: "", password: "", password_confirmation: "" });
const saving = ref(false);
const changingPwd = ref(false);
const favorites = ref([]);
const favCount = ref(0);

const avatarUrl = computed(() =>
  `https://ui-avatars.com/api/?name=${encodeURIComponent(profile.ten || "KH")}&background=1e3a8a&color=fff&size=80`
);

const joinDate = computed(() => {
  if (!profile.created_at) return "gần đây";
  return new Date(profile.created_at).toLocaleDateString("vi-VN", { month: "long", year: "numeric" });
});

const loadProfile = async () => {
  try {
    const res = await api.get("/khach-hang/profile");
    if (res.data?.status) Object.assign(profile, res.data.data);
  } catch {
    Swal.fire("Lỗi", "Không tải được hồ sơ", "error");
  }
};

const loadFavorites = async () => {
  try {
    const res = await api.get("/khach-hang/bds/yeu-thich/data");
    if (res.data?.data) {
      favorites.value = res.data.data;
      favCount.value = res.data.data.length;
    }
  } catch {}
};

const updateProfile = async () => {
  saving.value = true;
  try {
    const res = await api.post("/khach-hang/update-profile", {
      ten: profile.ten,
      email: profile.email,
      so_dien_thoai: profile.so_dien_thoai,
    });
    Swal.fire("Thành công", res.data.message || "Đã cập nhật hồ sơ", "success");
  } catch (e) {
    Swal.fire("Lỗi", e.response?.data?.message || "Cập nhật thất bại", "error");
  } finally {
    saving.value = false;
  }
};

const changePassword = async () => {
  if (!password.old_password || !password.password) {
    Swal.fire("Lưu ý", "Vui lòng điền đầy đủ thông tin mật khẩu", "warning");
    return;
  }
  changingPwd.value = true;
  try {
    const res = await api.post("/khach-hang/update-password", password);
    Swal.fire("Thành công", res.data.message || "Đã đổi mật khẩu", "success");
    password.old_password = "";
    password.password = "";
    password.password_confirmation = "";
  } catch (e) {
    Swal.fire("Lỗi", e.response?.data?.message || "Đổi mật khẩu thất bại", "error");
  } finally {
    changingPwd.value = false;
  }
};

const formatCurrency = (val) =>
  new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND", notation: "compact" }).format(val || 0);

onMounted(() => {
  loadProfile();
  loadFavorites();
});
</script>