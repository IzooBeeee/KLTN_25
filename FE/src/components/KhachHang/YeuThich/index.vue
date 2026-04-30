<template>
  <div class="min-h-screen bg-[#f0f4f8] font-['Inter']">

    <!-- Header -->
    <div class="bg-white border-b border-gray-100">
      <div class="container mx-auto max-w-7xl px-6 py-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="font-['Be_Vietnam_Pro'] text-3xl font-bold text-[#0a0e27] mb-1">Bất động sản yêu thích</h1>
            <p class="text-gray-500 text-sm">Danh sách bất động sản bạn đã lưu lại</p>
          </div>
          <span class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 font-bold rounded-full text-sm border border-red-100">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
            {{ list.length }} tin đã lưu
          </span>
        </div>
      </div>
    </div>

    <div class="container mx-auto max-w-7xl px-6 py-8">

      <!-- Loading -->
      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 6" :key="i" class="bg-white rounded-2xl overflow-hidden shadow-sm animate-pulse">
          <div class="h-48 bg-gray-200"></div>
          <div class="p-4 space-y-3">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
            <div class="h-5 bg-gray-200 rounded w-2/5"></div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="list.length === 0" class="text-center py-24">
        <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-red-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-700 mb-2">Chưa có bất động sản yêu thích</h3>
        <p class="text-gray-400 text-sm mb-6">Nhấn vào biểu tượng tim trên các tin đăng để lưu lại.</p>
        <router-link to="/khach-hang/danh-sach-bat-dong-san"
          class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:-translate-y-0.5 transition-all text-sm">
          Khám phá bất động sản
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </router-link>
      </div>

      <!-- Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="item in list" :key="item.id"
          class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all group border border-gray-100 flex flex-col">

          <!-- Image -->
          <div class="relative overflow-hidden" style="height:210px">
            <img
              :src="item.bat_dong_san?.anh_dai_dien_url || 'https://placehold.co/400x210?text=No+Image'"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              :alt="item.bat_dong_san?.tieu_de"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
            <!-- Badges -->
            <div class="absolute top-3 left-3 flex gap-2">
              <span v-if="item.bat_dong_san?.is_noi_bat"
                class="flex items-center gap-1 px-2.5 py-1 bg-amber-400 text-amber-900 text-xs font-bold rounded-full shadow">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Nổi bật
              </span>
              <span class="px-2.5 py-1 bg-white/90 text-gray-700 text-xs font-semibold rounded-full shadow">
                {{ item.bat_dong_san?.loai?.ten_loai || '—' }}
              </span>
            </div>
            <!-- Remove button -->
            <button @click="removeYeuThich(item)" title="Bỏ yêu thích"
              class="absolute top-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow hover:bg-red-50 transition group/btn">
              <svg class="w-4 h-4 text-red-500 group-hover/btn:scale-110 transition" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
            </button>
            <!-- Price overlay -->
            <div class="absolute bottom-3 left-3">
              <span class="px-3 py-1.5 bg-white/95 text-green-700 text-sm font-black rounded-xl shadow">
                {{ formatCurrency(item.bat_dong_san?.gia) }}
              </span>
            </div>
          </div>

          <!-- Body -->
          <div class="p-4 flex flex-col flex-1">
            <h3 class="font-bold text-gray-900 text-sm leading-snug mb-2 line-clamp-2" :title="item.bat_dong_san?.tieu_de">
              {{ item.bat_dong_san?.tieu_de || 'Không có tiêu đề' }}
            </h3>

            <div class="flex items-center gap-3 text-xs text-gray-400 mb-3">
              <span v-if="item.bat_dong_san?.dien_tich" class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                {{ item.bat_dong_san?.dien_tich }} m²
              </span>
              <span v-if="item.bat_dong_san?.so_phong_ngu" class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                {{ item.bat_dong_san?.so_phong_ngu }} PN
              </span>
            </div>

            <p class="flex items-start gap-1 text-xs text-gray-400 mb-4 leading-relaxed">
              <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <span class="line-clamp-1">{{ [item.bat_dong_san?.dia_chi?.dia_chi_chi_tiet, item.bat_dong_san?.dia_chi?.quan?.ten, item.bat_dong_san?.dia_chi?.tinh?.ten].filter(Boolean).join(', ') || '—' }}</span>
            </p>

            <!-- Agent -->
            <div class="flex items-center gap-2 pt-3 border-t border-gray-100 mt-auto">
              <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-xs font-black flex-shrink-0">
                {{ getInitials(item.bat_dong_san?.moi_gioi?.ten) }}
              </div>
              <span class="text-xs text-gray-400 truncate">{{ item.bat_dong_san?.moi_gioi?.ten || 'Môi giới' }}</span>
            </div>
          </div>

          <!-- View Button -->
          <div class="px-4 pb-4">
            <router-link :to="'/khach-hang/chi-tiet-bat-dong-san/' + item.bat_dong_san_id"
              class="flex items-center justify-center gap-2 w-full py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold rounded-xl hover:shadow-lg hover:shadow-blue-200 hover:-translate-y-0.5 transition-all">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              Xem chi tiết
            </router-link>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/axios/config";

const loading = ref(false);
const list = ref([]);

const fetchYeuThich = async () => {
  loading.value = true;
  try {
    const res = await api.get("/khach-hang/bds/yeu-thich/data");
    if (res.data?.data) {
      list.value = res.data.data;
    }
  } catch (e) {
    console.error("Lỗi tải yêu thích:", e);
  } finally {
    loading.value = false;
  }
};

const removeYeuThich = async (item) => {
  try {
    await api.post("/khach-hang/bds/yeu-thich", { bds_id: item.bat_dong_san_id });
    list.value = list.value.filter((i) => i.id !== item.id);
  } catch (e) {
    console.error("Lỗi bỏ yêu thích:", e);
  }
};

const formatCurrency = (val) =>
  new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND", notation: "compact" }).format(val || 0);

const getInitials = (name) => {
  if (!name) return "?";
  return name.split(" ").map((n) => n[0]).join("").toUpperCase().slice(0, 2);
};

onMounted(() => fetchYeuThich());
</script>
