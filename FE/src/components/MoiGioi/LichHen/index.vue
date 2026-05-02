<template>
  <div class="mg-lich-hen">
    <div class="mg-lh-header">
      <div>
        <h1 class="mg-lh-title">📅 Quản lý lịch hẹn xem nhà</h1>
        <p class="mg-lh-subtitle">Quản lý và xác nhận lịch hẹn từ khách hàng</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="mg-lh-stats">
      <div class="mg-lh-stat">
        <div class="mg-lh-stat__icon">⏰</div>
        <div>
          <div class="mg-lh-stat__value">{{ stats.pending }}</div>
          <div class="mg-lh-stat__label">Chờ xác nhận</div>
        </div>
      </div>
      <div class="mg-lh-stat">
        <div class="mg-lh-stat__icon">📅</div>
        <div>
          <div class="mg-lh-stat__value">{{ stats.today }}</div>
          <div class="mg-lh-stat__label">Hôm nay</div>
        </div>
      </div>
      <div class="mg-lh-stat">
        <div class="mg-lh-stat__icon">📊</div>
        <div>
          <div class="mg-lh-stat__value">{{ stats.this_week }}</div>
          <div class="mg-lh-stat__label">Tuần này</div>
        </div>
      </div>
      <div class="mg-lh-stat">
        <div class="mg-lh-stat__icon">✅</div>
        <div>
          <div class="mg-lh-stat__value">{{ stats.completed_this_month }}</div>
          <div class="mg-lh-stat__label">Hoàn thành tháng này</div>
        </div>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="mg-lh-tabs">
      <button v-for="tab in tabs" :key="tab.value" 
        class="mg-lh-tab" :class="{ active: filter === tab.value }"
        @click="filter = tab.value">
        {{ tab.label }} ({{ getCount(tab.value) }})
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mg-lh-loading">Đang tải...</div>
    
    <template v-else>
      <!-- Last Updated -->
      <div class="mg-lh-last-updated">
        <span class="mg-lh-pulse"></span>
        Cập nhật lúc {{ lastUpdated }}
      </div>

      <!-- Empty -->
      <div v-if="filteredAppointments.length === 0" class="mg-lh-empty">
        <div class="mg-lh-empty__icon">📅</div>
        <h3>Chưa có lịch hẹn</h3>
        <p>Chưa có khách hàng nào đặt lịch xem nhà.</p>
      </div>

    <!-- List -->
    <div v-else class="mg-lh-list">
      <div v-for="item in filteredAppointments" :key="item.id" class="mg-lh-card" :class="item.status_color">
        <div class="mg-lh-card__main">
          <div class="mg-lh-card__bds">
            <img :src="item.bat_dong_san.anh_dai_diem_url || 'https://placehold.co/150x100?text=BDS'" />
            <div>
              <h4>{{ item.bat_dong_san.tieu_de }}</h4>
              <p>{{ item.bat_dong_san.dia_chi }}</p>
            </div>
          </div>
          <div class="mg-lh-card__customer">
            <div class="mg-lh-avatar">{{ getInitials(item.khach_hang.ten) }}</div>
            <div>
              <div class="mg-lh-name">{{ item.khach_hang.ten }}</div>
              <div class="mg-lh-phone">{{ item.khach_hang.so_dien_thoai }}</div>
            </div>
          </div>
          <div class="mg-lh-card__time">
            <div class="mg-lh-date">{{ item.ngay_hen }}</div>
            <div class="mg-lh-hour">{{ item.gio_hen }}</div>
          </div>
          <div class="mg-lh-card__status" :class="item.status_color">{{ item.status_label }}</div>
        </div>
        <div v-if="item.ghi_chu" class="mg-lh-card__note">
          <i class="fa-regular fa-note-sticky"></i> {{ item.ghi_chu }}
        </div>
        <div v-if="item.trang_thai === 'cho_xac_nhan'" class="mg-lh-card__actions">
          <button class="mg-lh-btn mg-lh-btn--reject" @click="reject(item)">
            <i class="fa-solid fa-xmark"></i> Từ chối
          </button>
          <button class="mg-lh-btn mg-lh-btn--accept" @click="accept(item)">
            <i class="fa-solid fa-check"></i> Xác nhận
          </button>
        </div>
        <div v-if="item.trang_thai === 'da_xac_nhan'" class="mg-lh-card__actions">
          <button class="mg-lh-btn mg-lh-btn--complete" @click="complete(item)">
            <i class="fa-solid fa-check-double"></i> Đánh dấu hoàn thành
          </button>
        </div>
      </div>
    </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import api from '@/axios/config';
import { createToaster } from '@meforma/vue-toaster';
import Swal from 'sweetalert2';

const toaster = createToaster({ position: 'top-right', duration: 3000 });

const loading = ref(true);
const appointments = ref([]);
const filter = ref('all');
const stats = ref({ pending: 0, today: 0, this_week: 0, completed_this_month: 0 });
const previousPending = ref(0);
const lastUpdated = ref('');
let pollInterval = null;

const tabs = [
  { value: 'all', label: 'Tất cả' },
  { value: 'cho_xac_nhan', label: 'Chờ xác nhận' },
  { value: 'da_xac_nhan', label: 'Đã xác nhận' },
  { value: 'hoan_thanh', label: 'Hoàn thành' },
];

const filteredAppointments = computed(() => {
  if (filter.value === 'all') return appointments.value;
  return appointments.value.filter(a => a.trang_thai === filter.value);
});

function getCount(status) {
  if (status === 'all') return appointments.value.length;
  return appointments.value.filter(a => a.trang_thai === status).length;
}

function getInitials(name) {
  return name?.split(' ').map(n => n[0]).slice(-2).join('').toUpperCase() || 'KH';
}

async function fetchData(silent = false) {
  try {
    const [apptRes, statsRes] = await Promise.all([
      api.get('/moi-gioi/lich-hen/danh-sach'),
      api.get('/moi-gioi/lich-hen/thong-ke')
    ]);
    const newAppointments = apptRes.data?.data?.appointments || [];
    
    // Check for new pending appointments
    const newPendingCount = newAppointments.filter(a => a.trang_thai === 'cho_xac_nhan').length;
    if (!silent && previousPending.value > 0 && newPendingCount > previousPending.value) {
      const newPendingItems = newAppointments.filter(a => 
        a.trang_thai === 'cho_xac_nhan' && !appointments.value.find(o => o.id === a.id)
      );
      if (newPendingItems.length > 0) {
        toaster.success(`📅 Có ${newPendingItems.length} lịch hẹn mới chờ xác nhận!`);
      }
    }
    
    appointments.value = newAppointments;
    previousPending.value = newPendingCount;
    stats.value = statsRes.data?.data || { pending: 0, today: 0, this_week: 0, completed_this_month: 0 };
    lastUpdated.value = new Date().toLocaleTimeString('vi-VN');
  } catch (e) {
    if (!silent) toaster.error('Không thể tải dữ liệu');
  } finally {
    loading.value = false;
  }
}

function startPolling() {
  pollInterval = setInterval(() => fetchData(true), 30000); // 30s
}

async function accept(item) {
  const result = await Swal.fire({
    title: 'Xác nhận lịch hẹn?',
    text: `Xác nhận lịch hẹn với ${item.khach_hang.ten} vào ${item.ngay_hen} lúc ${item.gio_hen}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy'
  });
  if (!result.isConfirmed) return;

  try {
    await api.post(`/moi-gioi/lich-hen/${item.id}/xac-nhan`);
    toaster.success('Đã xác nhận lịch hẹn');
    await fetchData();
  } catch (e) {
    toaster.error('Không thể xác nhận');
  }
}

async function reject(item) {
  const { value: reason } = await Swal.fire({
    title: 'Từ chối lịch hẹn',
    input: 'textarea',
    inputLabel: 'Lý do từ chối',
    inputPlaceholder: 'Nhập lý do từ chối...',
    showCancelButton: true,
    confirmButtonText: 'Từ chối',
    cancelButtonText: 'Hủy',
    inputValidator: (value) => value ? null : 'Vui lòng nhập lý do'
  });
  if (!reason) return;

  try {
    await api.post(`/moi-gioi/lich-hen/${item.id}/huy`, { ly_do: reason });
    toaster.success('Đã từ chối lịch hẹn');
    await fetchData();
  } catch (e) {
    toaster.error('Không thể từ chối');
  }
}

async function complete(item) {
  const result = await Swal.fire({
    title: 'Hoàn thành lịch hẹn?',
    text: 'Đánh dấu lịch hẹn này đã hoàn thành?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Hoàn thành',
    cancelButtonText: 'Hủy'
  });
  if (!result.isConfirmed) return;

  try {
    await api.post(`/moi-gioi/lich-hen/${item.id}/hoan-thanh`);
    toaster.success('Đã cập nhật trạng thái');
    await fetchData();
  } catch (e) {
    toaster.error('Không thể cập nhật');
  }
}

onMounted(() => {
  fetchData();
  startPolling();
});

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});
</script>

<style scoped>
.mg-lich-hen { padding: 1.5rem; }
.mg-lh-header { margin-bottom: 1.5rem; }
.mg-lh-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem; }
.mg-lh-subtitle { color: #64748b; margin: 0; }
.mg-lh-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.mg-lh-stat { background: #fff; border-radius: 16px; padding: 1rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.mg-lh-stat__icon { width: 48px; height: 48px; background: #f0f9ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.mg-lh-stat__value { font-size: 1.75rem; font-weight: 800; color: #0f172a; }
.mg-lh-stat__label { font-size: 0.875rem; color: #64748b; }
.mg-lh-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.mg-lh-tab { padding: 0.75rem 1.25rem; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; color: #64748b; font-weight: 500; cursor: pointer; transition: all 0.2s; }
.mg-lh-tab:hover { border-color: #3b82f6; color: #3b82f6; }
.mg-lh-tab.active { background: linear-gradient(135deg,#3b82f6,#8b5cf6); color: #fff; border-color: transparent; }
.mg-lh-loading { text-align: center; padding: 3rem; color: #64748b; }
.mg-lh-empty { text-align: center; padding: 4rem 2rem; background: #fff; border-radius: 20px; }
.mg-lh-empty__icon { font-size: 4rem; margin-bottom: 1rem; }
.mg-lh-list { display: flex; flex-direction: column; gap: 1rem; }
.mg-lh-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-left: 4px solid #cbd5e1; }
.mg-lh-card.warning { border-left-color: #f59e0b; }
.mg-lh-card.info { border-left-color: #3b82f6; }
.mg-lh-card.success { border-left-color: #22c55e; }
.mg-lh-card__main { display: grid; grid-template-columns: 2fr 1.5fr 1fr auto; gap: 1rem; align-items: center; padding: 1rem; }
.mg-lh-card__bds { display: flex; gap: 0.75rem; align-items: center; }
.mg-lh-card__bds img { width: 80px; height: 60px; object-fit: cover; border-radius: 8px; }
.mg-lh-card__bds h4 { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem; }
.mg-lh-card__bds p { font-size: 0.8rem; color: #64748b; margin: 0; }
.mg-lh-card__customer { display: flex; align-items: center; gap: 0.75rem; }
.mg-lh-avatar { width: 40px; height: 40px; background: linear-gradient(135deg,#3b82f6,#8b5cf6); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 0.85rem; }
.mg-lh-name { font-weight: 600; color: #0f172a; font-size: 0.9rem; }
.mg-lh-phone { font-size: 0.8rem; color: #64748b; }
.mg-lh-card__time { text-align: center; }
.mg-lh-date { font-size: 0.9rem; font-weight: 600; color: #0f172a; }
.mg-lh-hour { font-size: 0.8rem; color: #3b82f6; }
.mg-lh-card__status { padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.mg-lh-card__status.warning { background: #fef3c7; color: #92400e; }
.mg-lh-card__status.info { background: #dbeafe; color: #1e40af; }
.mg-lh-card__status.success { background: #d1fae5; color: #065f46; }
.mg-lh-card__note { padding: 0.75rem 1rem; background: #f8fafc; border-top: 1px dashed #e2e8f0; font-size: 0.875rem; color: #64748b; }
.mg-lh-card__actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 0.75rem 1rem; border-top: 1px solid #f1f5f9; }
.mg-lh-btn { padding: 0.6rem 1rem; border-radius: 10px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.5rem; }
.mg-lh-btn--accept { background: #dcfce7; color: #166534; }
.mg-lh-btn--accept:hover { background: #bbf7d0; }
.mg-lh-btn--reject { background: #fef2f2; color: #991b1b; }
.mg-lh-btn--reject:hover { background: #fee2e2; }
.mg-lh-btn--complete { background: #eff6ff; color: #1e40af; }
.mg-lh-last-updated { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #64748b; margin-bottom: 1rem; justify-content: flex-end; }
.mg-lh-pulse { width: 6px; height: 6px; background: #22c55e; border-radius: 50%; animation: mgPulse 2s infinite; }
@keyframes mgPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
@media (max-width: 1024px) {
  .mg-lh-stats { grid-template-columns: repeat(2, 1fr); }
  .mg-lh-card__main { grid-template-columns: 1fr; gap: 0.75rem; }
}
@media (max-width: 640px) {
  .mg-lh-stats { grid-template-columns: 1fr; }
}
</style>
