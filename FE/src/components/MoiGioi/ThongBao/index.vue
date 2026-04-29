<template>
  <div class="min-h-screen bg-[#f0f4f8] font-['Inter'] p-6">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-black text-gray-900 mb-1">Thông báo</h1>
        <p class="text-sm text-gray-400">Theo dõi hoạt động khách hàng quan tâm đến bất động sản của bạn.</p>
      </div>
      <div class="flex items-center gap-2">
        <button v-if="unreadCount > 0" @click="markAllRead"
          class="flex items-center gap-1.5 px-4 py-2 bg-blue-50 text-blue-600 text-sm font-semibold rounded-xl hover:bg-blue-100 transition border border-blue-100">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Đọc tất cả
        </button>
        <button @click="fetchData"
          class="w-9 h-9 flex items-center justify-center bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition text-gray-500">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-2xl p-4 shadow-sm text-center border border-gray-100">
        <div class="text-2xl font-black text-blue-600">{{ list.length }}</div>
        <div class="text-xs text-gray-400 mt-1">Tổng thông báo</div>
      </div>
      <div class="bg-white rounded-2xl p-4 shadow-sm text-center border border-gray-100">
        <div class="text-2xl font-black text-red-500">{{ unreadCount }}</div>
        <div class="text-xs text-gray-400 mt-1">Chưa đọc</div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="bg-white rounded-2xl p-4 shadow-sm animate-pulse flex gap-3">
        <div class="w-10 h-10 bg-gray-200 rounded-xl flex-shrink-0"></div>
        <div class="flex-1 space-y-2">
          <div class="h-3.5 bg-gray-200 rounded w-2/3"></div>
          <div class="h-3 bg-gray-200 rounded w-full"></div>
          <div class="h-3 bg-gray-200 rounded w-4/5"></div>
        </div>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="list.length === 0" class="text-center py-20 bg-white rounded-2xl shadow-sm">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
      </div>
      <h3 class="font-bold text-gray-600 mb-1">Chưa có thông báo nào</h3>
      <p class="text-sm text-gray-400">Thông báo sẽ xuất hiện khi khách hàng yêu thích bất động sản của bạn.</p>
    </div>

    <!-- Notification List -->
    <div v-else class="space-y-3">
      <div v-for="item in list" :key="item.id"
        @click="markRead(item)"
        class="group flex items-start gap-3 p-4 rounded-2xl cursor-pointer transition-all border"
        :class="item.is_read ? 'bg-white border-gray-100 hover:border-blue-200 hover:shadow-sm' : 'bg-blue-50 border-blue-200 shadow-sm'">

        <!-- Icon -->
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-base"
          :class="{
            'bg-red-100 text-red-500': item.loai === 'yeu_thich',
            'bg-blue-100 text-blue-500': item.loai === 'dang_tin',
            'bg-green-100 text-green-500': item.loai === 'duyet',
            'bg-amber-100 text-amber-500': item.loai === 'tu_choi',
            'bg-gray-100 text-gray-500': !['yeu_thich','dang_tin','duyet','tu_choi'].includes(item.loai),
          }">
          <svg v-if="item.loai === 'yeu_thich'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
          <svg v-else-if="item.loai === 'duyet'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <svg v-else-if="item.loai === 'tu_choi'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-2 mb-1">
            <span class="text-sm font-semibold text-gray-800 truncate">{{ item.tieu_de || 'Thông báo mới' }}</span>
            <span class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0">{{ formatTime(item.created_at) }}</span>
          </div>
          <p class="text-xs text-gray-500 leading-relaxed mb-1">{{ item.noi_dung }}</p>
          <router-link v-if="item.bat_dong_san"
            :to="'/khach-hang/chi-tiet-bat-dong-san/' + item.bat_dong_san?.id"
            class="inline-flex items-center gap-1 text-xs text-blue-600 font-medium hover:underline" @click.stop>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            {{ item.bat_dong_san?.tieu_de }}
          </router-link>
        </div>

        <!-- Unread indicator -->
        <div v-if="!item.is_read" class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></div>

        <!-- Delete button -->
        <button @click.stop="deleteNotif(item)"
          class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition opacity-0 group-hover:opacity-100 flex-shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "@/axios/config";

const loading = ref(false);
const list = ref([]);

const unreadCount = computed(() => list.value.filter((n) => !n.is_read).length);

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await api.get("/moi-gioi/thong-bao");
    if (res.data?.data) {
      list.value = res.data.data;
    }
  } catch (e) {
    console.error("Lỗi tải thông báo:", e);
  } finally {
    loading.value = false;
  }
};

const markRead = async (item) => {
  if (item.is_read) return;
  try {
    await api.post(`/moi-gioi/thong-bao/${item.id}/da-doc`);
    item.is_read = true;
  } catch {}
};

const markAllRead = async () => {
  try {
    await api.post("/moi-gioi/thong-bao/doc-tat-ca");
    list.value.forEach((n) => (n.is_read = true));
  } catch {}
};

const deleteNotif = async (item) => {
  try {
    await api.delete(`/moi-gioi/thong-bao/${item.id}`);
    list.value = list.value.filter((n) => n.id !== item.id);
  } catch {}
};

const getIcon = (loai) => {
  const map = { yeu_thich: "bi-heart-fill", dang_tin: "bi-house-fill", duyet: "bi-check-circle-fill", tu_choi: "bi-x-circle-fill" };
  return map[loai] || "bi-bell-fill";
};

const getIconClass = (loai) => {
  const map = { yeu_thich: "icon-danger", dang_tin: "icon-primary", duyet: "icon-success", tu_choi: "icon-warning" };
  return map[loai] || "icon-primary";
};

const formatTime = (val) => {
  if (!val) return "";
  const d = new Date(val);
  const now = new Date();
  const diff = (now - d) / 1000;
  if (diff < 60) return "Vừa xong";
  if (diff < 3600) return Math.floor(diff / 60) + " phút trước";
  if (diff < 86400) return Math.floor(diff / 3600) + " giờ trước";
  return d.toLocaleDateString("vi-VN", { day: "2-digit", month: "2-digit", year: "numeric" });
};

onMounted(() => fetchData());
</script>
