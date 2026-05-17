<template>
  <div class="lich-hen-container">
    <h1 class="lh-title">
      <span class="lh-title__icon"><i class="fa-regular fa-calendar-check"></i></span>
      Lịch hẹn xem nhà của bạn
    </h1>

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
        <div class="lh-empty__icon"><i class="fa-regular fa-calendar-check"></i></div>
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
            <img :src="item.bat_dong_san.anh_dai_dien_url || 'https://placehold.co/120x80?text=BDS'"
              class="lh-card__image" />
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
const isFetching = ref(false); // Prevent concurrent requests

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
  // Prevent concurrent requests
  if (isFetching.value) return;

  try {
    isFetching.value = true;
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
    if (previousAppointments.value.length > 0) {
      newData.forEach(newItem => {
        const oldItem = previousAppointments.value.find(o => o.id === newItem.id);

        if (!oldItem) return;

        if (
          oldItem.trang_thai !== newItem.trang_thai &&
          oldItem.trang_thai === 'cho_xac_nhan' &&
          newItem.trang_thai === 'da_xac_nhan'
        ) {
          toaster.success(
            `✅ Lịch hẹn ${newItem.ngay_hen} lúc ${newItem.gio_hen} đã được xác nhận!`,
            { duration: 5000 }
          );
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
    isFetching.value = false;
  }
}

function startPolling() {
  // Clear old interval if exists to prevent duplicates
  if (pollInterval) {
    clearInterval(pollInterval);
  }

  pollInterval = setInterval(() => fetchAppointments(true), 3000); // 3s for faster real-time updates
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
    await fetchAppointments(false); // Refresh data after cancel
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
.lich-hen-container {
  max-width: 980px;
  margin: 2.5rem auto;
  padding: 0 1rem;
}

.lh-title {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  font-size: 1.9rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 1.75rem;
  text-align: center;
  letter-spacing: 0;
}

.lh-title__icon {
  width: 38px;
  height: 38px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 1.05rem;
  background: linear-gradient(135deg, #2563eb, #06b6d4);
  box-shadow: 0 14px 30px rgba(37, 99, 235, 0.24);
}

.lh-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
  margin-bottom: 1.8rem;
}

.lh-stat {
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  padding: 1.25rem 1rem;
  border-radius: 24px;
  text-align: center;
  cursor: pointer;
  border: 1px solid #eef2f7;
  transition:
    transform 0.42s cubic-bezier(0.16, 1, 0.3, 1),
    box-shadow 0.42s ease,
    border-color 0.42s ease,
    background 0.42s ease;
  box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
}

.lh-stat:hover {
  transform: translateY(-5px);
  border-color: rgba(147, 197, 253, 0.75);
  box-shadow: 0 22px 54px rgba(15, 23, 42, 0.12);
}

.lh-stat.active {
  border-color: #3b82f6;
  background: linear-gradient(135deg, #eff6ff 0%, #ecfeff 100%);
  box-shadow: 0 18px 44px rgba(37, 99, 235, 0.16);
}

.lh-stat__value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #0f172a;
}

.lh-stat__label {
  font-size: 0.875rem;
  color: #64748b;
  margin-top: 0.25rem;
}

.lh-loading {
  text-align: center;
  padding: 3rem;
  color: #64748b;
}

.lh-empty {
  text-align: center;
  padding: 4rem 2rem;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  border-radius: 30px;
  border: 1px solid #eef2f7;
  box-shadow: 0 18px 50px rgba(15, 23, 42, 0.07);
}

.lh-empty__icon {
  width: 76px;
  height: 76px;
  margin: 0 auto 1rem;
  border-radius: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2563eb;
  font-size: 2rem;
  background: #eff6ff;
}

.lh-empty h3 {
  font-size: 1.25rem;
  color: #0f172a;
  margin-bottom: 0.5rem;
}

.lh-empty p {
  color: #64748b;
  margin-bottom: 1.5rem;
}

.lh-list {
  display: flex;
  flex-direction: column;
  gap: 1.35rem;
}

.lh-card {
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
  border-radius: 28px;
  overflow: hidden;
  border: 1px solid #eef2f7;
  box-shadow: 0 16px 44px rgba(15, 23, 42, 0.07);
  transition:
    transform 0.42s cubic-bezier(0.16, 1, 0.3, 1),
    box-shadow 0.42s ease,
    border-color 0.42s ease;
}

.lh-card:hover {
  transform: translateY(-4px);
  border-color: rgba(147, 197, 253, 0.72);
  box-shadow: 0 24px 62px rgba(15, 23, 42, 0.13);
}

.lh-card__header {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.25rem;
  border-bottom: 1px solid #f1f5f9;
}

.lh-card__image {
  width: 128px;
  height: 84px;
  object-fit: cover;
  border-radius: 20px;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.1);
}

.lh-card__info {
  flex: 1;
}

.lh-card__title {
  font-size: 1rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.25rem;
  line-height: 1.3;
}

.lh-card__type {
  font-size: 0.875rem;
  color: #3b82f6;
  margin: 0 0 0.25rem;
}

.lh-card__address {
  font-size: 0.8rem;
  color: #64748b;
  margin: 0;
}

.lh-card__status {
  flex-shrink: 0;
  align-self: center;
  padding: 0.72rem 1rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.01em;
}

.lh-card__status.warning {
  background: #fef3c7;
  color: #92400e;
}

.lh-card__status.info {
  background: #dbeafe;
  color: #1e40af;
}

.lh-card__status.success {
  background: #d1fae5;
  color: #065f46;
}

.lh-card__status.danger {
  background: #fee2e2;
  color: #991b1b;
}

.lh-card__body {
  padding: 1rem 1.25rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.85rem 1rem;
}

.lh-detail {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #475569;
  padding: 0.48rem 0.75rem;
  border-radius: 999px;
  background: #f8fafc;
  border: 1px solid #eef2f7;
}

.lh-detail i {
  color: #64748b;
}

.lh-card__footer {
  padding: 1rem 1.25rem;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
}

.lh-btn {
  padding: 0.8rem 1.35rem;
  border-radius: 999px;
  font-weight: 700;
  cursor: pointer;
  border: none;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  transition:
    transform 0.32s cubic-bezier(0.16, 1, 0.3, 1),
    box-shadow 0.32s ease,
    background 0.32s ease,
    color 0.32s ease,
    border-color 0.32s ease;
}

.lh-btn:hover {
  transform: translateY(-2px);
}

.lh-btn--primary {
  background: linear-gradient(135deg, #2563eb, #06b6d4);
  color: #fff;
  box-shadow: 0 14px 30px rgba(37, 99, 235, 0.24);
}

.lh-btn--danger {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.lh-btn--danger:hover {
  background: #fee2e2;
  box-shadow: 0 12px 26px rgba(220, 38, 38, 0.12);
}

.lh-btn--secondary {
  background: #f1f5f9;
  color: #475569;
}

.lh-last-updated {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #64748b;
  margin-bottom: 1.25rem;
  justify-content: flex-end;
}

.lh-pulse {
  width: 6px;
  height: 6px;
  background: #22c55e;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.5;
  }
}

.lh-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.58);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.lh-modal {
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  padding: 1.5rem;
  border-radius: 28px;
  width: 90%;
  max-width: 430px;
  border: 1px solid rgba(255, 255, 255, 0.7);
  box-shadow: 0 32px 88px rgba(15, 23, 42, 0.28);
}

.lh-modal h3 {
  margin: 0 0 0.5rem;
  font-size: 1.25rem;
}

.lh-modal p {
  color: #64748b;
  margin-bottom: 1rem;
}

.lh-modal textarea {
  width: 100%;
  padding: 0.85rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  margin-bottom: 1rem;
  outline: none;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.lh-modal textarea:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
}

.lh-modal__actions {
  display: flex;
  gap: 0.85rem;
  justify-content: flex-end;
}

@media (max-width: 640px) {
  .lh-stats {
    grid-template-columns: repeat(2, 1fr);
  }

  .lh-card__header {
    flex-direction: column;
  }

  .lh-card__image {
    width: 100%;
    height: 150px;
  }

  .lh-card__status {
    width: 100%;
    text-align: center;
  }

  .lh-card__footer {
    justify-content: stretch;
  }

  .lh-card__footer .lh-btn {
    width: 100%;
    justify-content: center;
  }

  .lh-modal__actions {
    flex-direction: column;
  }
}
</style>
