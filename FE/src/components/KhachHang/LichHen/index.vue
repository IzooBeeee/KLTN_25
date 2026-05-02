<template>
  <div class="lich-hen-container">
    <h1 class="lh-title">📅 Lịch hẹn xem nhà của bạn</h1>
    
    <!-- Stats -->
    <div class="lh-stats">
      <div class="lh-stat" :class="{ active: filter === 'all' }" @click="filter = 'all'">
        <div class="lh-stat__value">{{ stats.total }}</div>
        <div class="lh-stat__label">Tất cả</div>
      </div>
      <div class="lh-stat" :class="{ active: filter === 'cho_xac_nhan' }" @click="filter = 'cho_xac_nhan'">
        <div class="lh-stat__value">{{ stats.pending }}</div>
        <div class="lh-stat__label">Chờ xác nhận</div>
      </div>
      <div class="lh-stat" :class="{ active: filter === 'da_xac_nhan' }" @click="filter = 'da_xac_nhan'">
        <div class="lh-stat__value">{{ stats.confirmed }}</div>
        <div class="lh-stat__label">Đã xác nhận</div>
      </div>
      <div class="lh-stat" :class="{ active: filter === 'hoan_thanh' }" @click="filter = 'hoan_thanh'">
        <div class="lh-stat__value">{{ stats.completed }}</div>
        <div class="lh-stat__label">Đã xem</div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="lh-loading">Đang tải...</div>
    
    <template v-else>
      <!-- Last Updated -->
      <div class="lh-last-updated">
        <span class="lh-pulse"></span>
        Cập nhật lúc {{ lastUpdated }}
      </div>

      <!-- Empty -->
      <div v-if="filteredAppointments.length === 0" class="lh-empty">
      <div class="lh-empty__icon">📅</div>
      <h3>Chưa có lịch hẹn</h3>
      <p>Bạn chưa đặt lịch hẹn xem nhà nào.</p>
      <router-link to="/khach-hang/danh-sach-bat-dong-san" class="lh-btn lh-btn--primary">
        Tìm bất động sản
      </router-link>
    </div>

    <!-- List -->
    <div v-else class="lh-list">
      <div v-for="item in filteredAppointments" :key="item.id" class="lh-card" :class="item.status_color">
        <div class="lh-card__header">
          <img :src="item.bat_dong_san.anh_dai_dien_url || 'https://placehold.co/120x80?text=BDS'" class="lh-card__image" />
          <div class="lh-card__info">
            <h3 class="lh-card__title">{{ item.bat_dong_san.tieu_de }}</h3>
            <p class="lh-card__type">{{ item.bat_dong_san.loai }}</p>
            <p class="lh-card__address"><i class="fa-solid fa-location-dot"></i> {{ item.bat_dong_san.dia_chi }}</p>
          </div>
          <div class="lh-card__status" :class="item.status_color">
            {{ item.status_label }}
          </div>
        </div>
        <div class="lh-card__body">
          <div class="lh-detail">
            <i class="fa-regular fa-calendar"></i>
            <span>{{ item.ngay_hen }}</span>
          </div>
          <div class="lh-detail">
            <i class="fa-regular fa-clock"></i>
            <span>{{ item.gio_hen }}</span>
          </div>
          <div class="lh-detail" v-if="item.moi_gioi">
            <i class="fa-solid fa-user-tie"></i>
            <span>{{ item.moi_gioi.ten }}</span>
          </div>
          <div class="lh-detail" v-if="item.ghi_chu">
            <i class="fa-regular fa-note-sticky"></i>
            <span>{{ item.ghi_chu }}</span>
          </div>
        </div>
        <div class="lh-card__footer" v-if="item.trang_thai !== 'hoan_thanh' && item.trang_thai !== 'huy'">
          <button class="lh-btn lh-btn--danger" @click="cancelAppointment(item)">
            <i class="fa-solid fa-xmark"></i> Hủy lịch
          </button>
        </div>
      </div>
    </div>
    </template>

    <!-- Cancel Modal -->
    <Teleport to="body">
      <div v-if="showCancelModal" class="lh-modal-overlay" @click.self="showCancelModal = false">
        <div class="lh-modal">
          <h3>Hủy lịch hẹn</h3>
          <p>Vui lòng cho chúng tôi biết lý do:</p>
          <textarea v-model="cancelReason" placeholder="Nhập lý do hủy..." rows="3"></textarea>
          <div class="lh-modal__actions">
            <button class="lh-btn lh-btn--secondary" @click="showCancelModal = false">Không hủy</button>
            <button class="lh-btn lh-btn--danger" @click="confirmCancel" :disabled="!cancelReason.trim()">
              Xác nhận hủy
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import api from '@/axios/config';
import { createToaster } from '@meforma/vue-toaster';

const toaster = createToaster({ position: 'top-right', duration: 3000 });

const loading = ref(true);
const appointments = ref([]);
const filter = ref('all');
const showCancelModal = ref(false);
const cancelReason = ref('');
const selectedAppointment = ref(null);
const previousCount = ref(0);
const lastUpdated = ref('');
let pollInterval = null;
const previousAppointments = ref([]); // Track previous state for status change detection

const stats = computed(() => ({
  total: appointments.value.length,
  pending: appointments.value.filter(a => a.trang_thai === 'cho_xac_nhan').length,
  confirmed: appointments.value.filter(a => a.trang_thai === 'da_xac_nhan').length,
  completed: appointments.value.filter(a => a.trang_thai === 'hoan_thanh').length
}));

const filteredAppointments = computed(() => {
  if (filter.value === 'all') return appointments.value;
  return appointments.value.filter(a => a.trang_thai === filter.value);
});

async function fetchAppointments(silent = false) {
  try {
    const res = await api.get('/khach-hang/lich-hen/danh-sach');
    const newData = res.data?.data || [];
    
    // Check for new appointments
    if (!silent && previousCount.value > 0 && newData.length > previousCount.value) {
      const newItems = newData.filter(n => !appointments.value.find(o => o.id === n.id));
      if (newItems.length > 0) {
        toaster.success(`Có ${newItems.length} lịch hẹn mới được cập nhật!`);
      }
    }
    
    // 🔔 Check for status changes (confirmed or rejected)
    if (!silent && previousAppointments.value.length > 0) {
      newData.forEach(newItem => {
        const oldItem = previousAppointments.value.find(o => o.id === newItem.id);
        if (oldItem && oldItem.trang_thai !== newItem.trang_thai) {
          if (oldItem.trang_thai === 'cho_xac_nhan') {
            if (newItem.trang_thai === 'da_xac_nhan') {
              toaster.success(`✅ Lịch hẹn ${newItem.ngay_hen} lúc ${newItem.gio_hen} đã được xác nhận!`, { duration: 5000 });
            } else if (newItem.trang_thai === 'huy') {
              toaster.error(`❌ Lịch hẹn ${newItem.ngay_hen} lúc ${newItem.gio_hen} đã bị từ chối. Lý do: ${newItem.ly_do_huy || 'Không có lý do'}`, { duration: 5000 });
            }
          }
        }
      });
    }
    
    appointments.value = newData;
    previousAppointments.value = JSON.parse(JSON.stringify(newData)); // Deep clone
    previousCount.value = newData.length;
    lastUpdated.value = new Date().toLocaleTimeString('vi-VN');
  } catch (e) {
    if (!silent) toaster.error('Không thể tải danh sách lịch hẹn');
  } finally {
    loading.value = false;
  }
}

function startPolling() {
  console.log('[Polling] KhachHang LichHen started - checking every 10s');
  pollInterval = setInterval(() => fetchAppointments(true), 10000); // 10s for faster real-time
}

function cancelAppointment(item) {
  selectedAppointment.value = item;
  cancelReason.value = '';
  showCancelModal.value = true;
}

async function confirmCancel() {
  try {
    await api.post(`/khach-hang/lich-hen/${selectedAppointment.value.id}/huy`, { ly_do: cancelReason.value });
    toaster.success('Đã hủy lịch hẹn');
    showCancelModal.value = false;
    await fetchAppointments();
  } catch (e) {
    toaster.error('Không thể hủy lịch');
  }
}

onMounted(() => {
  fetchAppointments();
  startPolling();
});

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});
</script>

<style scoped>
.lich-hen-container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
.lh-title { font-size: 1.75rem; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem; text-align: center; }
.lh-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.lh-stat { background: #fff; padding: 1rem; border-radius: 16px; text-align: center; cursor: pointer; border: 2px solid transparent; transition: all 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.lh-stat:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.lh-stat.active { border-color: #3b82f6; background: #eff6ff; }
.lh-stat__value { font-size: 1.75rem; font-weight: 800; color: #0f172a; }
.lh-stat__label { font-size: 0.875rem; color: #64748b; margin-top: 0.25rem; }
.lh-loading { text-align: center; padding: 3rem; color: #64748b; }
.lh-empty { text-align: center; padding: 4rem 2rem; background: #fff; border-radius: 20px; }
.lh-empty__icon { font-size: 4rem; margin-bottom: 1rem; }
.lh-empty h3 { font-size: 1.25rem; color: #0f172a; margin-bottom: 0.5rem; }
.lh-empty p { color: #64748b; margin-bottom: 1.5rem; }
.lh-list { display: flex; flex-direction: column; gap: 1rem; }
.lh-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.lh-card__header { display: flex; gap: 1rem; padding: 1rem; border-bottom: 1px solid #f1f5f9; }
.lh-card__image { width: 120px; height: 80px; object-fit: cover; border-radius: 12px; }
.lh-card__info { flex: 1; }
.lh-card__title { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem; line-height: 1.3; }
.lh-card__type { font-size: 0.875rem; color: #3b82f6; margin: 0 0 0.25rem; }
.lh-card__address { font-size: 0.8rem; color: #64748b; margin: 0; }
.lh-card__status { padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
.lh-card__status.warning { background: #fef3c7; color: #92400e; }
.lh-card__status.info { background: #dbeafe; color: #1e40af; }
.lh-card__status.success { background: #d1fae5; color: #065f46; }
.lh-card__status.danger { background: #fee2e2; color: #991b1b; }
.lh-card__body { padding: 1rem; display: flex; flex-wrap: wrap; gap: 1rem; }
.lh-detail { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: #475569; }
.lh-detail i { color: #64748b; }
.lh-card__footer { padding: 0.75rem 1rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; }
.lh-btn { padding: 0.75rem 1.25rem; border-radius: 12px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
.lh-btn--primary { background: linear-gradient(135deg,#3b82f6,#8b5cf6); color: #fff; }
.lh-btn--danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.lh-btn--danger:hover { background: #fee2e2; }
.lh-btn--secondary { background: #f1f5f9; color: #475569; }
.lh-last-updated { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #64748b; margin-bottom: 1rem; justify-content: flex-end; }
.lh-pulse { width: 6px; height: 6px; background: #22c55e; border-radius: 50%; animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
.lh-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.lh-modal { background: #fff; padding: 1.5rem; border-radius: 16px; width: 90%; max-width: 400px; }
.lh-modal h3 { margin: 0 0 0.5rem; font-size: 1.25rem; }
.lh-modal p { color: #64748b; margin-bottom: 1rem; }
.lh-modal textarea { width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 1rem; }
.lh-modal__actions { display: flex; gap: 0.75rem; justify-content: flex-end; }
@media (max-width: 640px) {
  .lh-stats { grid-template-columns: repeat(2, 1fr); }
  .lh-card__header { flex-direction: column; }
  .lh-card__image { width: 100%; height: 150px; }
}
</style>
